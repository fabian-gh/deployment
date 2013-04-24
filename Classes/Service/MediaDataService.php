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
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * MediaDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class MediaDataService extends AbstractRepository{
    
    /**
     * max file size in Bytes
     * @var int 
     */
    protected $maxFileSize = 10000000;
    
    /**
     * @var \TYPO3\Deployment\Domain\Model\File
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
            $this->xmlwriter->writeElement('crdate', $file->getCrdate());
            $this->xmlwriter->writeElement('type', $file->getType());
            $this->xmlwriter->writeElement('storage', $file->getStorage());
            $this->xmlwriter->writeElement('identifier', $file->getIdentifier());
            $this->xmlwriter->writeElement('extension', $file->getExtension());
            $this->xmlwriter->writeElement('mime_type', $file->getMimeType());
            $this->xmlwriter->writeElement('name', $file->getName());
            $this->xmlwriter->writeElement('title', $file->getTitle());
            $this->xmlwriter->writeElement('sha1', $file->getSha1());
            $this->xmlwriter->writeElement('size', $file->getSize());
            $this->xmlwriter->writeElement('creation_date', $file->getCreationDate());
            $this->xmlwriter->writeElement('modification_date', $file->getModificationDate());
            $this->xmlwriter->writeElement('width', $file->getWidth());
            $this->xmlwriter->writeElement('height', $file->getHeight());
            $this->xmlwriter->writeElement('uuid', $this->getUuid($file->getUid(), 'sys_file'));
            
            // FileRefenrece einfügen
            $this->fileReference = $this->getFileReferenceFromTable($file->getUid());
            if($this->fileReference != null){
                $this->xmlwriter->startElement('fileReference');
                $this->xmlwriter->writeElement('tablenames', ($this->fileReference->getTablenames() == null) ? '' :  $this->fileReference->getTablenames());
                $this->xmlwriter->writeElement('fieldname', ($this->fileReference->getFieldname() == null) ? '' :  $this->fileReference->getFieldname());
                $this->xmlwriter->writeElement('title', ($this->fileReference->getTitle() == null) ? null :  $this->fileReference->getTitle());
                $this->xmlwriter->writeElement('description', ($this->fileReference->getDescription() == null) ? '' :  $this->fileReference->getDescription());
                $this->xmlwriter->writeElement('alternative', ($this->fileReference->getAlternative() == null) ? '' :  $this->fileReference->getAlternative());
                $this->xmlwriter->writeElement('link', ($this->fileReference->getLink() == null) ? '' :  $this->fileReference->getLink());
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
        $fileArr = $dateFolder = $contentArr = $exFaf = $splittedDateTime = array();
        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance('t3lib_Registry');
        $timestamp = $registry->get('deployment', 'last_deploy', time());
        $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/media/');
        
        if ($filesAndFolders) {
            // Dateipfad ausplitten
            foreach ($filesAndFolders as $faf) {
                $exFaf[] = str_replace('media/', '', strstr($faf, 'media'));
            }
            
            // Datum und Uhrzeit splitten
            foreach($exFaf as $dateTime){
                $splittedDateTime[] = explode('/', $dateTime);
            }
            
            // pro Ordner/Datum ein Array mit allen Dateinamen darin
            foreach($splittedDateTime as $dateTime){
                $dateFolder[$dateTime[0]][] = $dateTime[1];
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
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $res */
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
            /** @var \TYPO3\Deployment\Domain\Repository\FileRepository $fileRef */
            $fileRef = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
            $result = $fileRef->findByIdentifier($file);
            
            if($result->getFirst() == null){
                $notIndexedFiles[] = $file;
            }
        }
        
        return $notIndexedFiles;
    }
    
    
    /**
     * Dateien aus der sys_file-Tabelle holen und in den Deployment-Ordner kopieren.
     * Falls nötig, vorher die Ordnerstruktur erstellen.
     */
    public function deployResources(){
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        $fileAdminPath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin';
        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/resource';
        
        $data = $this->readXmlMediaList();
        
        foreach($data as $resource){
            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $resFact->getFileObject($resource['uid']);
            $split = explode('/', $file->getIdentifier());
            $filename = array_pop($split);

            // Pfad wieder zusammensetzen
            $folder = '';
            foreach($split as $sp){
                if($sp != '' && $sp != 'fileadmin'){
                    $folder = $folder.'/'.$sp;
                }
            }
            
            // erste Slash entfernen und Ordnerstruktur erstellen
            $fold = substr($folder, 1);
            if(!is_dir($path.'/'.$fold)){
                GeneralUtility::mkdir_deep($path.'/'.$fold);
            }
            
            // Nur Dateien <= 10 MB auf Dateiebene kopieren 
            if($file->getSize() <= $this->maxFileSize){
                copy($fileAdminPath.'/'.$fold.'/'.$filename, $path.'/'.$fold.'/'.$filename);
            }
        }
    }
    
    
    /**
     * Prüft ob die Dateien im resource-Ordner innerhalb des fileadmins vorhanden
     * sind. Falls nein werden diese kopiert.
     */
    public function checkIfFileExists(){
        $resourceFiles = $newArr = $newFileList = array();
        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/';
        $resPath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/resource/';
        
        // Dateilisten 
        $fileadminFiles = $this->readFilesInFileadmin();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($resourceFiles, $resPath);
        
        // Pfade kürzen und in Array abspeichern
        foreach($fileList as $paths){
            $newFileList[] = str_replace('resource/', '', strstr($paths, 'resource'));
        }
        
        // Unterschiede ermitteln
        $diffFiles = array_diff($newFileList, $fileadminFiles);
        
        // Dateien aus resource ind fileadmin kopieren
        foreach($diffFiles as $file){
            if(!file_exists($path.'/'.$file)){
                copy($resPath.$file, $path.'/'.$file);
            }
        }
    }
    
    
    /**
     * Gibt die entsprechende UUID passend zum Datensatz zurück
     * 
     * @param string $uid
     * @param string $table
     * @return string
     */
    protected function getUuid($uid, $table){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $uuid = $con->exec_SELECTgetSingleRow('uuid', $table, 'uid = '.$uid);
        
        return $uuid['uuid'];
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
     * @return int
     */
    public function getMaxFileSize() {
        return $this->maxFileSize;
    }

    /**
     * @param int $maxFileSize
     */
    public function setMaxFileSize() {
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        (!empty($configuration)) ? $this->maxFileSize = $configuration['maxFileSize'] : $this->maxFileSize = 10000000;
    }
    
    /**
     * @return DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
}