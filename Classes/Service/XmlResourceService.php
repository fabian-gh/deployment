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
use \TYPO3\Deployment\Domain\Model\File;
use \TYPO3\Deployment\Domain\Repository\AbstractRepository;
use \TYPO3\CMS\Core\Resource\ResourceFactory;
use \TYPO3\Deployment\Service\FileService;

/**
 * XmlResourceService
 * Class for creating and reading resource xml files
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class XmlResourceService extends AbstractDataService {

    /**
     * @var \TYPO3\Deployment\Domain\Model\File
     */
    protected $fileList;

    /**
     * @var \XmlWriter
     */
    protected $xmlwriter;

    /**
     * @var \SimpleXml
     */
    protected $xmlreader;

    
    /**
     * Writes a xml-file with all files inside the fileadmin
     */
    public function writeXmlResourceList() {

        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();

        // new XMLWriter-Object
        $this->xmlwriter = new \XMLWriter();

        // document properties
        $this->xmlwriter->openMemory();
        $this->xmlwriter->setIndent(TRUE);
        $this->xmlwriter->startDocument('1.0');
        // Document Type Definition (DTD)
        $this->xmlwriter->startDtd('resourcelist');
        $this->xmlwriter->writeDtdElement('resourcelist', '(file+)');
        $this->xmlwriter->writeDtdElement('file', '(tablename,tstamp,crdate,type,storage,identifier,extension,mime_type,name,title,sha1,size,creation_date,modification_date,width,height,uuid)');
        $this->xmlwriter->writeDtdElement('tablename', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('tstamp', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('crdate', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('type', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('storage', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('identifier', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('extension', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('mime_type', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('name', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('title', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('sha1', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('size', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('creation_date', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('modification_date', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('width', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('height', '(#PCDATA)');
        $this->xmlwriter->writeDtdElement('uuid', '(#PCDATA)');
        $this->xmlwriter->endDtd();

        // write data
        $this->xmlwriter->startElement('resourcelist');

        foreach ($this->fileList as $file) {
            /** @var File $file */
            $FileObj = $resFact->getFileObject($file->getUid());
            $this->xmlwriter->startElement('file');
            $this->xmlwriter->writeElement('tablename', 'sys_file');
            $this->xmlwriter->writeElement('tstamp', $FileObj->getProperty('tstamp'));
            $this->xmlwriter->writeElement('crdate', $FileObj->getProperty('crdate'));
            $this->xmlwriter->writeElement('type', $FileObj->getProperty('type'));
            $this->xmlwriter->writeElement('storage', $FileObj->getProperty('storage'));
            $this->xmlwriter->writeElement('identifier', $FileObj->getProperty('identifier'));
            $this->xmlwriter->writeElement('extension', $FileObj->getProperty('extension'));
            $this->xmlwriter->writeElement('mime_type', $FileObj->getProperty('mime_type'));
            $this->xmlwriter->writeElement('name', $FileObj->getProperty('name'));
            $this->xmlwriter->writeElement('title', $FileObj->getProperty('title'));
            $this->xmlwriter->writeElement('sha1', $FileObj->getProperty('sha1'));
            $this->xmlwriter->writeElement('size', $FileObj->getProperty('size'));
            $this->xmlwriter->writeElement('creation_date', $FileObj->getProperty('creation_date'));
            $this->xmlwriter->writeElement('modification_date', $FileObj->getProperty('modification_date'));
            $this->xmlwriter->writeElement('width', $FileObj->getProperty('width'));
            $this->xmlwriter->writeElement('height', $FileObj->getProperty('height'));
            $this->xmlwriter->writeElement('uuid', $FileObj->getProperty('uuid'));
            $this->xmlwriter->endElement();
        }

        $this->xmlwriter->endElement();
        $this->xmlwriter->endDocument();
        $writeString = $this->xmlwriter->outputMemory();

        $file = GeneralUtility::tempnam('resource_');
        GeneralUtility::writeFile($file, $writeString);

        $folder = $fileService->getDeploymentMediaPathWithTrailingSlash() . date('Y_m_d', time());
        GeneralUtility::mkdir($folder);

        GeneralUtility::upload_copy_move($file, $folder . '/' . date('H-i-s', time()) . '_resource.xml');
    }

    
    /**
     * Read all not deployed data from the resource xml file 
     * and converts it to an array
     * 
     * @return array
     */
    public function readXmlResourceList() {
        $arrcount = 0;
        $fileArr = array();
        $dateFolder = array();
        $contentArr = array();
        $exFaf = array();
        $splittedDateTime = array();
        $validationResult = array();
        /** @var \TYPO3\Deployment\Service\RegistryService $registry */
        $registry = new RegistryService();
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();

        $timestamp = $registry->getLastDeploy();
        $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $fileService->getDeploymentMediaPathWithTrailingSlash());

        if ($filesAndFolders) {
            // split data path
            foreach ($filesAndFolders as $faf) {
                $exFaf[] = str_replace('media/', '', strstr($faf, 'media'));
            }

            // split date and time
            foreach ($exFaf as $dateTime) {
                $splittedDateTime[] = explode('/', $dateTime);
            }

            // for each date an own directory with all filename inside
            foreach ($splittedDateTime as $dateTime) {
                $dateFolder[$dateTime[0]][] = $dateTime[1];
            }
        }

        // read files
        foreach ($dateFolder as $folder => $filename) {
            // extract the date from the directory
            $expDate = explode('_', $folder);

            foreach ($filename as $file) {
                // extract the time for each file
                $temp = explode('_', $file);
                $expTime = explode('-', $temp[0]);
                // create timestamp
                $dateAsTstamp = mktime($expTime[0], $expTime[1], $expTime[2], $expDate[1], $expDate[2], $expDate[0]);

                // if file-timestamp newer than last deplyoment.
                // than read the file and convert it
                if ($dateAsTstamp >= $timestamp) {
                    $validationResult['validation']['database/' . $folder . '/' . $file] = $fileService->xmlValidation($fileService->getDeploymentMediaPathWithTrailingSlash() . $folder . '/' . $file);
                    $xmlString = file_get_contents($fileService->getDeploymentMediaPathWithTrailingSlash() . $folder . '/' . $file);

                    $this->xmlreader = new \SimpleXMLElement($xmlString);
                    foreach ($this->xmlreader->file as $fileset) {
                        foreach ($fileset as $key => $value) {
                            $contentArr[$arrcount][$key] = (string) $value;
                        }
                        $arrcount++;
                    }
                }
            }
        }
        return array_merge($contentArr, $validationResult);
    }

    // ============================ Getter & Setter ================================

    /**
     * @return array
     */
    public function getFileList() {
        return $this->fileList;
    }

    /**
     * @param array $fileList
     */
    public function setFileList($fileList) {
        $this->fileList = $fileList;
    }

    /**
     * @return \XMLWriter
     */
    public function getXmlwriter() {
        return $this->xmlwriter;
    }

    /**
     * @param \XmlWriter $xmlwriter
     */
    public function setXmlwriter(\XmlWriter $xmlwriter) {
        $this->xmlwriter = $xmlwriter;
    }

    /**
     * @return \SimpleXml
     */
    public function getXmlreader() {
        return $this->xmlreader;
    }

    /**
     * @param \SimpleXml $xmlreader
     */
    public function setXmlreader(\SimpleXml $xmlreader) {
        $this->xmlreader = $xmlreader;
    }
}