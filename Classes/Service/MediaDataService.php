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
        $fileArr = $dateFolder = $contentArr = $exFaf = array();
        /** @var \TYPO3\CMS\Core\Registry $registry */
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
     * Falls nötig wird vorher die Ordnerstruktur erstellen.
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
            
            // Nur Dateien <= 10 MB kopieren 
            if($file->getSize() <= 10000000){
                // Dateien auf Dateiebene kopieren
                copy($fileAdminPath.'/'.$fold.'/'.$filename, $path.'/'.$fold.'/'.$filename);
            }
        }
    }
    
    
    public function checkIfFileExists(){
        /**
         * + Dateien aus deplyoment/resources holen und mit Dateien im fileadmin (readFilesInFileadmin) vgl.
         * - Wenn diese nicht existieren dann kopieren (evtl. mit rsync, ohne File-Obj.)
         * - Diese Methode vor der Indizierung in der indexAction ausführen lassen
         */
        $resourceFiles = $newArr = array();
        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/';
        $resPath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/resource/';
        
        // Dateilisten 
        $fileadminFiles = $this->readFilesInFileadmin();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($resourceFiles, $resPath);
        
        // TODO: Dateien vergleichen, evtl. vorher kürzen
        
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






    /**
     * Prüft ob die mediendaten schon existieren, falls nicht dann werden Sie 
     * an die richtige Stelle eingefügt
     */
//    public function checkIfFileExists(){
//        $path = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin';
//        $resPath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/resource';
//        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
//        $resFact = ResourceFactory::getInstance();
//        /** @var \TYPO3\Deployment\Domain\Repository\FileRepository $fileRep */
//        $fileRep = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
//        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $result */
//        $result = $fileRep->findAll();
//        
//        foreach($result as $res){
//            /** @var \TYPO3\CMS\Core\Resource\File $file */
//            $file = $resFact->getFileObject($res->getUid());
//            $identifier = $file->getIdentifier();
//            
//            if(!file_exists($path.$identifier)){
//                if(file_exists($resPath.$identifier)){
//                    $split = explode('/', $identifier);
//                    array_pop($split);
//                    $folder = '';
//                    foreach($split as $sp){
//                        if($sp != '' && $sp != 'fileadmin'){
//                            $folder = $folder.'/'.$sp;
//                        }
//                    }
//                    $fold = substr($folder, 1);
//                    $folderObj = $resFact->getObjectFromCombinedIdentifier('0:/fileadmin'.$fold);
//                    $file->copyTo($folderObj, null, 'overrideExistingFile');
//                }
//            }
//        }
//   }