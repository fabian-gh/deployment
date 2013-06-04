<?php

/**
 * FileService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * FileService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class FileService extends AbstractDataService {
    
    /**
     * Schreibt eine Dateiliste des Fileadmins, ohne Deploymentdateien
     * 
     * @return array
     */
    public function readFilesInFileadmin() {
        $fileArr = $newArr = array();

        // direktes auslesen des Ordners, da evtl. nicht alle Dateien in Tabellen indexiert sind
        $path = $this->getFileadminPathWithTrailingSlash();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $path);

        $pathCount = strlen($path);
        // deployment-Ordner exkludieren
        foreach ($fileList as $filekey => $filevalue) {
            if (strstr($filevalue, '/fileadmin/deployment') == FALSE) {
                $newArr[$filekey] = substr($filevalue, $pathCount);
            }
        }

        return $newArr;
    }
    
    
    
    /**
     * Filtert alle nicht indizierten Dateien und fügt diese in die sys-file Tabelle ein
     * 
     * @return array 
     */
    public function getNotIndexedFiles() {
        $fileArr = array();
        $newFileArr = array();
        $notIndexedFiles = array();

        $filesInFileadmin = $this->readFilesInFileadmin();

        // processed Data raus
        foreach ($filesInFileadmin as $filevalue) {
            if (strstr($filevalue, '_processed_/') == FALSE) {
                $fileArr[] = $filevalue;
            }
        }

        // temp Data raus
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
     * Prüft ob die Dateien im resource-Ordner innerhalb des fileadmins vorhanden
     * sind. Falls nein werden diese kopiert.
     */
    public function checkIfFileExists() {
        $resourceFiles = $newArr = $newFileList = array();
        $path = $this->getFileadminPathWithTrailingSlash();
        $resPath = $this->getDeploymentResourcePathWithTrailingSlash();

        // Dateilisten 
        $fileadminFiles = $this->readFilesInFileadmin();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($resourceFiles, $resPath);

        // Pfade kürzen und in Array abspeichern
        foreach ($fileList as $paths) {
            $newFileList[] = str_replace('resource/', '', strstr($paths, 'resource'));
        }

        // Unterschiede ermitteln
        $diffFiles = array_diff($newFileList, $fileadminFiles);

        // Dateien aus resource in fileadmin kopieren
        foreach ($diffFiles as $file) {
            if (!file_exists($path . '/' . $file)) {
                copy($resPath . $file, $path . '/' . $file);
            }
        }
    }
    
    
    /**
     * Nicht indizierte Daten in Tabelle eintragen
     * 
     * @param array $fileArr
     */
    public function processNotIndexedFiles($fileArr){
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        
        foreach($fileArr as $file){
            $res = $resFact->getFileObjectFromCombinedIdentifier('/fileadmin/'.$file);
            // selbstständige Indizierung wenn etwas mit $res gemacht wird
            $res->isIndexed();
        }

        if($con->isConnected()){
            $fileRep = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            $res = $fileRep->findByIdentifierWithoutHeadingSlash('/fileadmin/');
            
            foreach($res as $file){
                // File-Objekt über UID des Ergebnisses erzeugen
                $File = $resFact->getFileObject($file->getUid());
                // Identifier des Objekts abfragen
                $identifier = $File->getIdentifier();
                
                if(strstr($identifier, '/fileadmin') != false){
                    $croppedIdentifier = substr($identifier, 10);
                    $con->exec_UPDATEquery('sys_file', 'uid='.$file->getUid(), array('identifier' => $croppedIdentifier));
                } else {
                    $con->exec_UPDATEquery('sys_file', 'uid='.$file->getUid(), array('identifier' => $identifier));
                }
            }
        }
    }
    
    
    /**
     * Generiert eine UUID
     *
     * @return string
     */
    public function generateUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, 
            mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
    
    
    /**
     * Löscht alle XML-Dateien und Ordner, die älter als ein halbes Jahr sind
     */
    public function deleteOlderFiles(){
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configuration */
        $configuration = new ConfigurationService();
        $deleteState = $configuration->getDeleteState();
        
        // falls Daten gelöscht werden sollen
        if($deleteState == 1){
            $fileArr  = array();
            $split  = array();
            $dateFolder = array();
            
            $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/', '', true);
        
            if ($filesAndFolders) {
                // Dateipfad ausplitten
                foreach ($filesAndFolders as $faf) {
                    $exFaf[] = explode('/', $faf);
                }

                // pro Ordner/Datum ein Array mit allen Dateinamen darin
                foreach ($exFaf as $item) {
                    if ($item[7] != '' && $item[8] != '') {
                        $dateFolder[$item[7]][] = $item[8];
                    }
                }

                // Datum splitten und löschen
                foreach($dateFolder as $datekey => $files){
                    $split = explode('_', $datekey);
                    $splitdate = mktime(0, 0, 0, $split[1], $split[2], $split[0]);

                    // falls Ordner älter als halbes Jahr
                    if($splitdate+(6*30*24*60*60) < time()){
                        // dann Dateien in Ordner löschen
                        foreach($files as $filevalue){
                            $splitFile = explode('_', $filevalue);
                            $folder = ($splitFile[1] == 'changes.xml') ? 'database' : 'media';

                            unlink(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/'.$folder.'/'.$datekey.'/'.$filevalue);
                        }
                        // Ordner selbst löschen
                        rmdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/'.$folder.'/'.$datekey);
                    }
                }
            }
        }
    }
    
    
    // ======================================= Getter ===============================================
    
    /**
     * @return string
     */
    public function getFileadminPathWithoutTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin';
    }
    
    /**
     * @return string
     */
    public function getFileadminPathWithTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/';
    }
    
    /**
     * @return string
     */
    public function getDeploymentPathWithoutTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment';
    }
    
    /**
     * @return string
     */
    public function getDeploymentPathWithTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/';
    }
    
    /**
     * @return string
     */
    public function getDeploymentDatabasePathWithoutTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/database';
    }
    
    /**
     * @return string
     */
    public function getDeploymentDatabasePathWithTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/database/';
    }
    
    /**
     * @return string
     */
    public function getDeploymentMediaPathWithoutTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media';
    }
    
    /**
     * @return string
     */
    public function getDeploymentMediaPathWithTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media/';
    }
    
    /**
     * @return string
     */
    public function getDeploymentResourcePathWithoutTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/resource';
    }
    
    /**
     * @return string
     */
    public function getDeploymentResourcePathWithTrailingSlash(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/resource/';
    }
}