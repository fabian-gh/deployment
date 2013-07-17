<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * FileService
 * Class for file creating service
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class FileService extends AbstractDataService {

    /**
     * Write a file list of the fileadmin without deployment data
     *
     * @return array
     */
    public function readFilesInFileadmin() {
        $fileArr = array();
        $newArr = array();

        // read the directory directly, because maybe not all files are indexed
        $path = $this->getFileadminPathWithTrailingSlash();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $path);

        $pathCount = strlen($path);
        // exclude deplyoment directory
        foreach ($fileList as $filekey => $filevalue) {
            if (strstr($filevalue, '/fileadmin/deployment') == FALSE) {
                $newArr[$filekey] = substr($filevalue, $pathCount);
            }
        }

        return $newArr;
    }

    
    /**
     * Filter all not indexed files and add them to the sys_file table
     *
     * @return array
     */
    public function getNotIndexedFiles() {
        $fileArr = array();
        $newFileArr = array();
        $notIndexedFiles = array();

        $filesInFileadmin = $this->readFilesInFileadmin();

        // exclude processed data
        foreach ($filesInFileadmin as $filevalue) {
            if (strstr($filevalue, '_processed_/') == FALSE) {
                $fileArr[] = $filevalue;
            }
        }

        // exclude temp data
        foreach ($fileArr as $filevalue) {
            if (strstr($filevalue, '_temp_/') == FALSE) {
                $newFileArr[] = $filevalue;
            }
        }

        foreach ($newFileArr as $file) {
            /** @var \TYPO3\Deployment\Domain\Repository\FileRepository $fileRef */
            $fileRef = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
            $result = $fileRef->findByIdentifier($file);

            if ($result->getFirst() == NULL) {
                $notIndexedFiles[] = $file;
            }
        }

        return $notIndexedFiles;
    }

    
    /**
     * Index not indexed files
     *
     * @param array $fileArr
     */
    public function processNotIndexedFiles($fileArr) {
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();

        foreach ($fileArr as $file) {
            $res = $resFact->getFileObjectFromCombinedIdentifier('/fileadmin/' . $file);
            // automatic indexing as soon as $res is used
            $res->isIndexed();
        }

        if ($this->getDatabase()->isConnected()) {
            $fileRep = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            $res = $fileRep->findByIdentifierWithoutHeadingSlash('/fileadmin/');

            foreach ($res as $file) {
                // create file-object over uid of the result
                $File = $resFact->getFileObject($file->getUid());
                // qzery identifier of the object
                $identifier = $File->getIdentifier();

                if (strstr($identifier, '/fileadmin') != FALSE) {
                    $croppedIdentifier = substr($identifier, 10);
                    $this->getDatabase()->exec_UPDATEquery('sys_file', 'uid=' . $file->getUid(), array('identifier' => $croppedIdentifier));
                } else {
                    $this->getDatabase()->exec_UPDATEquery('sys_file', 'uid=' . $file->getUid(), array('identifier' => $identifier));
                }
            }
        }
    }

    
    /**
     * Generate a uuid
     *
     * @return string
     */
    public function generateUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
    
    
    /**
     * Cleans the directory with the XML-files
     */
    public function deleteXmlFileDirectory(){
        $fileArr = array();
        
        $path = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $this->getDeploymentDatabasePathWithTrailingSlash(), '', TRUE);
        unset($path[0]);
        
        foreach($path as $file){
            GeneralUtility::rmdir($file, true);
        }
    }
    
    
    /**
     * Cleans the directory with the database dumps
     */
    public function deleteDbDumpDirectory(){
        $fileArr = array();
        
        $path = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $this->getDeploymentBBDeploymentPathWithTrailingSlash(), '', TRUE);
        unset($path[0]);
        
        foreach($path as $file){
            GeneralUtility::rmdir($file, true);
        }
    }

    
    /**
     * Delete all xml files which are older than a half year
     */
    public function deleteOlderFiles() {
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configuration */
        $configuration = new ConfigurationService();

        $deleteState = $configuration->getDeleteState();

        // if data should be deleted
        if ($deleteState == 1) {
            $fileArr = array();
            $split = array();
            $dateFolder = array();

            $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $this->getDeploymentPathWithTrailingSlash(), '', TRUE);

            if ($filesAndFolders) {
                // split file path
                foreach ($filesAndFolders as $faf) {
                    $exFaf[] = explode('/', $faf);
                }

                //  for each date an own directory with all filename inside
                foreach ($exFaf as $item) {
                    if ($item[7] != '' && $item[8] != '') {
                        $dateFolder[$item[7]][] = $item[8];
                    }
                }

                // split date and delete
                foreach ($dateFolder as $datekey => $files) {
                    $split = explode('_', $datekey);
                    $splitdate = mktime(0, 0, 0, $split[1], $split[2], $split[0]);

                    // if directory older than a half year
                    if ($splitdate + (6 * 30 * 24 * 60 * 60) < time()) {
                        // dann Dateien in Ordner löschen
                        foreach ($files as $filevalue) {
                            $splitFile = explode('_', $filevalue);
                            $folder = ($splitFile[1] == 'changes.xml') ? 'database' : 'media';

                            GeneralUtility::rmdir($this->getDeploymentPathWithTrailingSlash().$folder.'/'.$datekey, true);
                        }
                    }
                }
            }
        }
    }

    
    /**
     * Creates the directory structure if it not already exists
     */
    public function createDirectories() {
        $exFold = array();
        /** @var \TYPO3\CMS\Core\Resource\Driver\LocalDriver $folder */
        $folder = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Driver\\LocalDriver');

        $exFold[] = $folder->folderExists($this->getDeploymentPathWithTrailingSlash());
        $exFold[] = $folder->folderExists($this->getDeploymentDatabasePathWithTrailingSlash());
        $exFold[] = $folder->folderExists($this->getDeploymentMediaPathWithTrailingSlash());
        // TODO: Löschen des Pfades
        $exFold[] = $folder->folderExists($this->getDeploymentResourcePathWithTrailingSlash());

        foreach ($exFold as $ergkey => $ergvalue) {
            if (!$ergvalue) {
                switch ($ergkey) {
                    case 0:
                        GeneralUtility::mkdir($ergvalue);
                        break;

                    case 1:
                        GeneralUtility::mkdir($ergvalue);
                        break;

                    case 2:
                        GeneralUtility::mkdir($ergvalue);
                        break;
					
                    // TODO: Pfad löschen
                    case 3:
                        GeneralUtility::mkdir($ergvalue);
                        break;
                }
            }
        }
    }

    
    /**
     * Validate the assigned xml file
     *
     * @param XML-File $file
     *
     * @return boolean
     */
    public function xmlValidation($file) {
        $dom = new \DOMDocument;
        $dom->load($file);
        
        if ($dom->validate()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    
    /**
     * Split the validation from the data
     *
     * @param array $content
     * @param bool  $validation
     *
     * @return array
     */
    public function splitContent($content, $validation = FALSE) {
        $newArr = array();

        if ($validation) {
            return $content['validation'];
        }

        foreach ($content as $key => $value) {
            if ($key !== 'validation') {
                $newArr[] = $value;
            }
        }
        return $newArr;
    }
    
    
    /**
     * Copy all resources which didn't exists on your server
     * 
     * @param string $resourceServerPath
     */
    public function fileChecker($resourceServerPath){
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $result */
        $result = $this->getDatabase()->exec_SELECTgetRows('*', 'sys_file', '1=1');
        
        foreach($result as $res){
            $obj = $resFact->getFileObject($res['uid']);
            
            if(!$obj->exists() && $resourceServerPath !== ''){
                copy($resourceServerPath.$obj->getIdentifier(), $this->getFileadminPathWithoutTrailingSlash().$obj->getIdentifier());
            }
        }
    }

    // ======================================= Getter ===============================================

    /**
     * @return string
     */
    public function getFileadminPathWithoutTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin';
    }

    /**
     * @return string
     */
    public function getFileadminPathWithTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/';
    }

    /**
     * @return string
     */
    public function getDeploymentPathWithoutTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment';
    }

    /**
     * @return string
     */
    public function getDeploymentPathWithTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/';
    }

    /**
     * @return string
     */
    public function getDeploymentDatabasePathWithoutTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/database';
    }

    /**
     * @return string
     */
    public function getDeploymentDatabasePathWithTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/database/';
    }

    /**
     * @return string
     */
    public function getDeploymentMediaPathWithoutTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media';
    }

    /**
     * @return string
     */
    public function getDeploymentMediaPathWithTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media/';
    }

    /**
     * @return string
     */
    public function getDeploymentBBDeploymentPathWithoutTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/bbdeployment';
    }

    /**
     * @return string
     */
    public function getDeploymentBBDeploymentPathWithTrailingSlash() {
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/bbdeployment/';
    }
}