<?php

/**
 * MediaDataService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Domain\Repository\AbstractRepository;
use \TYPO3\CMS\Core\Resource\File;

/**
 * MediaDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class MediaDataService extends AbstractRepository{
    
    /**
     * @var array 
     */
    protected $fileList;
    
    /**
     * @var \TYPO3\Deployment\Domain\Model\FileReference
     */
    protected $fileReference;
    
    /**
     * @var \XmlWriter
     */
    protected $xmlwriter;

    /**
     * @var \SimpleXml
     */
    protected $xmlreader;
    
    
    /**
     * Schreibt eine XML-Datei mit allen im Fileadmin befindlichen Dateien, 
     * ohne Pfadangabe zum Fileadmin
     */
    public function writeXmlMediaList(){
        // Neues XMLWriter-Objekt
        $this->xmlwriter = new \XMLWriter();

        // Dokumenteneigenschaften
        $this->xmlwriter->openMemory();
        $this->xmlwriter->setIndent(TRUE);
        $this->xmlwriter->startDocument('1.0');
        $this->xmlwriter->startElement('xml');
        $this->xmlwriter->writeAttribute('version', '1.0');
        $this->xmlwriter->writeAttribute('encoding', 'UTF_8');
        $this->xmlwriter->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        // Daten schreiben
        $this->xmlwriter->startElement('medialist');

        foreach($this->fileList as $file) {
            $this->xmlwriter->startElement('file');
            $this->xmlwriter->writeElement('uid', $file->getUid());
            $this->xmlwriter->writeElement('pid', $file->getPid());
            $this->xmlwriter->writeElement('tstamp', $file->getTstamp());
            $this->xmlwriter->writeElement('type', $file->getType());
            $this->xmlwriter->writeElement('storage', $file->getStorage());
            $this->xmlwriter->writeElement('identifier', $file->getIdentifier());
            $this->xmlwriter->writeElement('extension', $file->getExtension());
            $this->xmlwriter->writeElement('mimeType', $file->getMimeType());
            $this->xmlwriter->writeElement('name', $file->getName());
            $this->xmlwriter->writeElement('size', $file->getSize());
            $this->xmlwriter->writeElement('creationDate', $file->getCreationDate());
            $this->xmlwriter->writeElement('modificationDate', $file->getModificationDate());
            $this->xmlwriter->writeElement('width', $file->getWidth());
            $this->xmlwriter->writeElement('height', $file->getHeight());
            
            $this->fileReference = $this->getFileReferenceFromTable($file->getUid());
            if($this->fileReference != null){
                $this->xmlwriter->startElement('fileReference');
                $this->xmlwriter->writeElement('tablenames', $this->fileReference->getTablenames());
                $this->xmlwriter->writeElement('fieldname', $this->fileReference->getFieldname());
                $this->xmlwriter->writeElement('title', $this->fileReference->getTitle());
                $this->xmlwriter->writeElement('description', $this->fileReference->getDescription());
                $this->xmlwriter->writeElement('alternative', $this->fileReference->getAlternative());
                $this->xmlwriter->writeElement('link', $this->fileReference->getLink());
                $this->xmlwriter->endElement();
            } else {
                $this->xmlwriter->startElement('filereference');
                $this->xmlwriter->writeElement('tablenames');
                $this->xmlwriter->writeElement('fieldname');
                $this->xmlwriter->writeElement('title');
                $this->xmlwriter->writeElement('description');
                $this->xmlwriter->writeElement('alternative');
                $this->xmlwriter->writeElement('link');
                $this->xmlwriter->endElement();
            }
            $this->xmlwriter->endElement();
        }

        $this->xmlwriter->endElement();
        $this->xmlwriter->endElement();
        $this->xmlwriter->endDocument();
        $writeString = $this->xmlwriter->outputMemory();

        $file = GeneralUtility::tempnam('media_');
        GeneralUtility::writeFile($file, $writeString);

        $folder = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/media/'.date('Y_m_d', time());
        GeneralUtility::mkdir($folder);
        
        GeneralUtility::upload_copy_move($file, $folder . '/' . date('H-i-s', time()) . '_media.xml');
    }
    
    
    /**
     * Liest alle noch nicht deployten Datensätze aus der Media-XML Datei 
     * und gibt diese als Array zurück.
     * 
     * @return array
     */
    public function readXmlMediaList(){
        $arrcount = 0;
        $fileArr = $dateFolder = $contentArr = $exFaf = array();
        $registry = GeneralUtility::makeInstance('t3lib_Registry');
        $timestamp = $registry->get('deployment', 'last_deploy', time());
        $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/media/');
        
        if ($filesAndFolders) {
            // Dateipfad ausplitten
            foreach ($filesAndFolders as $faf) {
                $exFaf[] = explode('/', $faf);
            }
            
            // Initialwert
            $initDate = $exFaf[0][7];
            // pro Ordner/Datum ein Array mit allen Dateinamen darin
            foreach ($exFaf as $item) {
                if ($initDate == $item[7]) {
                    $dateFolder[$initDate][] = $item[8];
                } else {
                    $dateFolder[$item[7]][] = $item[8];
                }
            }
        }
        
        //Dateien einlesen
        foreach ($dateFolder as $folder => $filename) {
            // Datum aus Ordner extrahieren
            $expDate = explode('_', $folder);
            
            foreach ($filename as $file) {
                // für jede Datei die Uhrzeit extrahieren
                $temp = explode('_', $file);
                $expTime = explode('-', $temp[0]);
                // Timestamp erstellen
                $dateAsTstamp = mktime($expTime[0], $expTime[1], $expTime[2], $expDate[1], $expDate[2], $expDate[0]);
                
                // wenn Datei-Timestamp später als letztes Deployment,
                // dann die Datei lesen und umwandeln
                if ($dateAsTstamp >= $timestamp) {
                    $xmlString = file_get_contents(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/media/'.$folder.'/'.$file);
                    
                    $this->xmlreader = new \SimpleXMLElement($xmlString);
                    foreach ($this->xmlreader->medialist->file as $fileset) {
                        foreach ($fileset as $key => $value) {
                            $contentArr[$arrcount][$key] = (string) $value;
                        }
                        $arrcount++;
                    }
                }
            }
        }
        
        return $contentArr;
    }
    
    
    /**
     * Gibt die referenzierten Daten für den übergebenen Datensatz zurück
     * 
     * @param string $uid
     * @return \TYPO3\Deployment\Domain\Model\FileReference
     */
    protected function getFileReferenceFromTable($uid){
        /** @var \TYPO3\Deployment\Domain\Repository\FileReferenceRepository $fileRefObj */
        $fileRefObj = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileReferenceRepository');
        
        $res = $fileRefObj->findByUidForeign($uid);

        return ($res != null) ? $res[0] : null;
    }
    
    
    /**
     * Schreibt eine Dateiliste des Fileadmins, ohne Deploymentdateien
     */
    public function readFilesInFileadmin(){
        $fileArr = $newArr = array();
        
        // direktes auslesen des Ordners, da evtl. nicht alle Dateien in Tabellen indexiert sind
        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/';
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $path);
        
        $pathCount = strlen($path);
        // deployment-Ordner exkludieren
        foreach($fileList as $filekey => $filevalue){
            if(strstr($filevalue, '/fileadmin/deployment') == false){
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
    public function getNotIndexedFiles(){
        $fileArr = $newFileArr = $notIndexedFiles = $filesInFileadmin = array();
        
        $filesInFileadmin = $this->readFilesInFileadmin();

        // processed Data raus
        foreach($filesInFileadmin as $filevalue){
            if(strstr($filevalue, '_processed_/') == false){
                $fileArr[] = $filevalue;
            }
        }
        
        // temp Data raus
        foreach($fileArr as $filevalue){
            if(strstr($filevalue, '_temp_/') == false){
                $newFileArr[] = $filevalue;
            }
        }
        
        foreach($newFileArr as $file){
            $fileRef = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            $result = $fileRef->findByIdentifier($file);
            
            if($result->getFirst() == null){
                $notIndexedFiles[] = $file;
            }
        }
        
        return $notIndexedFiles;
    }
    
    
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
     * @return \TYPO3\Deployment\Domain\Model\FileReference
     */
    public function getFileReference() {
        return $this->fileReference;
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\FileReference $fileReference
     */
    public function setFileReference($fileReference) {
        $this->fileReference = $fileReference;
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
    
    
    /**
     * @return DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
    
}