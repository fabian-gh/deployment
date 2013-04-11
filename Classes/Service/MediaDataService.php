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
     * @var \XmlWriter
     */
    protected $xmlwriter;

    /**
     * @var \SimpleXml
     */
    protected $xmlreader;
    
    
    /**
     * Schreibt eine Dateiliste des Fileadmins zurück, ohne Deploymentdateien
     */
    /*public function readFilesInFileadmin(){
        $fileArr = array();
        $newArr = array();
        
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
        
        $this->fileList = $newArr;
    }*/
    
    
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
            $this->xmlwriter->writeElement('mimeType', $file->getMimeType());
            $this->xmlwriter->writeElement('size', $file->getSize());
            $this->xmlwriter->writeElement('creationDate', $file->getCreationDate());
            $this->xmlwriter->writeElement('modificationDate', $file->getModificationDate());
            $this->xmlwriter->writeElement('width', $file->getWidth());
            $this->xmlwriter->writeElement('height', $file->getHeight());
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
    
    
    /**
     * @return DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
    
}

/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        //$con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');