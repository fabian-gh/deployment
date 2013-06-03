<?php

/**
 * XmlDatabaseService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Domain\Model\LogData;
use \TYPO3\Deployment\Domain\Model\HistoryData;
use \TYPO3\Deployment\Domain\Model\History;
use \TYPO3\Deployment\Service\FileService;

/**
 * XmlDatabaseService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class XmlDatabaseService extends AbstractDataService{

    /**
     * @var \TYPO3\Deployment\Domain\Model\HistoryData
     */
    protected $historyData;

    /**
     * @var \TYPO3\Deployment\Domain\Model\LogData 
     */
    protected $logData;

    /**
     * @var array
     */
    protected $deployData;

    /**
     * @var \XmlWriter
     */
    protected $xmlwriter;

    /**
     * @var \SimpleXml
     */
    protected $xmlreader;

    
    /**
     * Geänderte Datensätze in ein XML-Dokument schreiben.
     * XML-Dateien sind unter fileadmin/deployment/database/YYYY-MM-DD/ zu finden
     */
    public function writeXML() {
        $newInsert = array();
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $fileService = new FileService();
        
        // Neues XMLWriter-Objekt
        $this->xmlwriter = new \XMLWriter();

        // Dokumenteneigenschaften
        $this->xmlwriter->openMemory();                         // Daten in Speicher schreiben
        $this->xmlwriter->setIndent(TRUE);                      // Einzug aktivieren
        $this->xmlwriter->startDocument('1.0');                 // Document-Tag erzeugen
        // Document Type Definition (DTD)
        $this->xmlwriter->startDtd('changeSet');
        $this->xmlwriter->writeDtdElement('changeSet', '(data)');
        $this->xmlwriter->writeDtdElement('data', 'ANY');
        $this->xmlwriter->endDtd();

        // Daten schreiben
        $this->xmlwriter->startElement('changeSet');

        foreach ($this->deployData as $cData) {
            // Alle neuen Datensätze abfragen
            if($cData->getSysLogUid() == 'NEW' && $cData->getFieldlist() == '*'){
                $newInsert = $con->exec_SELECTgetSingleRow('*', $cData->getTablename(), 'uid='.$cData->getUid());
                
                // für jeden Datensatz ein neues data-Element mit UID als Attribut
                $this->xmlwriter->startElement('data');
                $this->xmlwriter->writeElement('tablename', $cData->getTablename());
                $this->xmlwriter->writeElement('fieldlist', '*');
                
                foreach($newInsert as $newkey => $newval){
                    if($newkey != 'l18n_diffsource'){
                        // PID durch UUID ersetzen
                        if($newkey == 'pid'){
                            $pageUuid = $this->getPageUuid($newval);
                            $this->xmlwriter->writeElement('pid', $pageUuid);
                        } 
                        // uid_local durch UUId ersetzen
                        elseif($newkey == 'uid_local'){
                            $fileUuid = $this->getFileUuid($newval);
                            $this->xmlwriter->writeElement('uid_local', $fileUuid);
                        }
                        // uid_foreign durch UUID ersetzen
                        elseif($newkey == 'uid_foreign'){
                            $contentUuid = $this->getContentUuid($newval);
                            $this->xmlwriter->writeElement('uid_foreign', $contentUuid);
                        }
                        // header_link (tt_content) durch entsprechende UUID ersetzen
                        elseif($newkey == 'header_link') {
                            $substring = $fileService->checkLinks($newval);
                            $this->xmlwriter->writeElement($newkey, $substring);
                        } 
                        // link (sys_file_reference) durch UUId ersetzen
                        elseif($newkey == 'link'){
                            $substring = $fileService->checkLinks($newval);
                            $this->xmlwriter->writeElement($newkey, $substring);
                        } 
                        else {
                            $this->xmlwriter->writeElement($newkey, $newval);
                        }
                    }
                }
                
                $this->xmlwriter->endElement();
            } 
            // Veränderte Datensätze erstellen
            else {
                // pid abfragen
                $pid = $this->getPid($cData->getRecuid(), $cData->getTablename());
                
                // für jeden Datensatz ein neues data-Element mit UID als Attribut
                $this->xmlwriter->startElement('data');

                // Einzelne Feldelemente schreiben
                $this->xmlwriter->writeElement('tablename', $cData->getTablename());
                $this->xmlwriter->writeElement('fieldlist', $cData->getFieldlist());
                $this->xmlwriter->writeElement('pid', $this->getPageUuid($pid));
                $this->xmlwriter->writeElement('tstamp', $cData->getTstamp()->getTimestamp());
                $this->xmlwriter->writeElement('uuid', $this->getUuid($cData->getRecuid(), $cData->getTablename()));

                // geänderte Historydaten durchlaufen
                foreach ($cData->getHistoryData() as $datakey => $data) {
                    if ($datakey == 'newRecord') {
                        foreach ($data as $key => $value) {
                            $this->xmlwriter->writeElement($key, $value);
                        }
                    }
                }

                $this->xmlwriter->endElement();
            }
        }
        die();
        //$this->xmlwriter->endElement();
        $this->xmlwriter->endElement();
        $this->xmlwriter->endDocument(); // Dokument schließen
        $writeString = $this->xmlwriter->outputMemory();
        
        $file = GeneralUtility::tempnam('deploy_');
        GeneralUtility::writeFile($file, $writeString);

        $folder = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/database/'.date('Y_m_d', time());
        GeneralUtility::mkdir($folder);
        
        GeneralUtility::upload_copy_move($file, $folder.'/'.date('H-i-s', time()).'_changes.xml');
    }

    
    /**
     * Liest alle noch nicht deployeten XML-Datensätze
     * 
     * @param $string $timestamp
     * @return array
     */
    public function readXML($timestamp) {
        $arrcount = 0;
        $fileArr = $dateFolder = $contentArr = $exFaf = array();
        $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/database/');
        
        if ($filesAndFolders) {
            // Dateipfad ausplitten
            foreach ($filesAndFolders as $faf) {
                $exFaf[] = str_replace('database/', '', strstr($faf, 'database'));
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
                    $xmlString = file_get_contents(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/database/'.$folder.'/'.$file);

                    $this->xmlreader = new \SimpleXMLElement($xmlString);
                    foreach ($this->xmlreader->data as $dataset) {
                        foreach ($dataset as $key => $value) {
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
     * Ersetzt die uid im übergebenen Link durch die UUID
     * 
     * @param string $link
     * @return string
     */
    public function checkLinks($link){
        $split = explode(':', $link);
        
        if(is_numeric($link)){
            return $link;
        } elseif($split[0] === 'file'){
            $split[1] = $this->getFileUuid($split[1]);
            return implode(':', $split);
        } else {
            return $link;
        }
    }
    
    
    /**
     * Gibt die Differenzen der Daten zurück
     * 
     * @param \TYPO3\Deployment\Domain\Model\HistoryData $historyData
     * @return string
     */
    public function getHistoryDataDiff($historyData){
        $data = array();
        $differences = array();
        /** @var $diff \TYPO3\CMS\Core\Utility\DiffUtility */
        $diff = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\DiffUtility');
        
        // Daten pro Datensatz in einem Array organisieren
        foreach($historyData as $hisData){
            foreach($hisData->getHistoryData() as $records){
                foreach($records as $reckey => $recval){
                    $data[$hisData->getRecuid()][$reckey][$hisData->getRecuid()][] = $recval;
                }
            }
        }
        
        // Array durchwandern und Differenz aus old/newRecord erstellen
        foreach($data as $dat){
            foreach($dat as $columnkey => $cloumnval){
                foreach($cloumnval as $recuid => $dataArr){
                    if($columnkey != 'l18n_diffsource'){
                        $differences[$recuid][$columnkey][] = $diff->makeDiffDisplay($dataArr[0], $dataArr[1]);
                    }
                }
            }
        }
        
        return $differences;
    }

    
    /**
     * Deserialisiert die übergebenen Log-Daten
     * 
     * @param \TYPO3\Deployment\Domain\Model\Log $logData
     * @return array<\TYPO3\Deployment\Domain\Model\LogData> $data
     */
    public function unserializeLogData($logData) {
        $date = new \DateTime();
        $data = array();

        if ($logData != NULL) {
            foreach ($logData as $log) {
                /** @var $log Log */
                $this->logData = new LogData();
                $this->logData->setUid($log->getUid());
                $this->logData->setAction($log->getAction());
                $unlogdata = unserialize($log->getLogData());
                
                $tableAndId = explode(':', $unlogdata[1]);
                $this->logData->setData($unlogdata[0]);
                $this->logData->setTable($tableAndId[0]);
                $this->logData->setRecuid($tableAndId[1]);
                $this->logData->setTstamp($date->setTimestamp($log->getTstamp()));
                
               if($log->getAction() == '1'){
                    $this->logData->setPid($unlogdata[3]);
                }

                $data[] = $this->logData;
            }

            return $data;
        } else {
            return $data = array();
        }
    }

    
    /**
     * Deserialisiert die übergebenen History-Daten
     * 
     * @param array<\TYPO3\Deployment\Domain\Model\History> $historyData
     * @return array<\TYPO3\Deployment\Domain\Model\HistoryData> $data
     */
    public function unserializeHistoryData($historyData) {
        $hisData = array();

        if ($historyData != NULL) {
            foreach ($historyData as $his) {
                if ($his != NULL) {
                    $this->historyData = new HistoryData();
                    $this->historyData->setPid($his->getPid());
                    $this->historyData->setUid($his->getUid());
                    $this->historyData->setSysLogUid($his->getSysLogUid());
                    
                    $unlogdata = unserialize($his->getHistoryData());
                    
                    // wird benötigt um das l18n_diffsource-Feld zu deserialisieren
                    foreach ($unlogdata as $key => $value) {
                        $data = array();
                        foreach ($value as $k => $val) {
                            if (preg_match('/[a-z]{1}:[0-9]+/', $val)) {
                                $data[$k] = unserialize($val);
                            } else {
                                $data[$k] = $val;
                            }
                        }
                        $unlogdata[$key] = $data;
                    }

                    $this->historyData->setHistoryData($unlogdata);
                    $this->historyData->setFieldlist($his->getFieldlist());
                    $this->historyData->setRecuid($his->getRecuid());
                    $this->historyData->setTablename($his->getTablename());
                    $this->historyData->setTstamp($his->getTstamp());

                    $hisData[] = $this->historyData;
                }
            }

            return $hisData;
        } else {
            return $hisData = array();
        }
    }
    
    
    /**
     * Gibt die uuid der übergebenen pid aus der pages-Tabelle zurück
     * 
     * @param string $pid
     * @return string
     */
    public function getPageUuid($pid){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $uuid = $con->exec_SELECTgetSingleRow('uuid', 'pages', 'uid = '.$pid);
        
        return (!empty($uuid['uuid'])) ? $uuid['uuid'] : 0;
    }
    
    
    /**
     * Gibt die uuid der übergebenen uid aus der tt_content-Tabelle zurück
     * 
     * @param string $pid
     * @return string
     */
    public function getContentUuid($uid){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $uuid = $con->exec_SELECTgetSingleRow('uuid', 'tt_content', 'uid = '.$uid);
        
        return (!empty($uuid['uuid'])) ? $uuid['uuid'] : 0;
    }
    
    
    /**
     * Gibt die uuid der übergebenen uid aus der sys_file-Tabelle zurück
     * 
     * @param string $pid
     * @return string
     */
    public function getFileUuid($uid){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $uuid = $con->exec_SELECTgetSingleRow('uuid', 'sys_file', 'uid = '.$uid);
        
        return (!empty($uuid['uuid'])) ? $uuid['uuid'] : 0;
    }
    
    
    /**
     * Gibt die pid der übergebenen uid zurück
     * 
     * @param string $uid
     * @param string $table
     * @return int
     */
    public function getPid($uid, $table){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $pid = $con->exec_SELECTgetSingleRow('pid', $table, 'uid = '.$uid);
        
        return (!empty($pid['pid'])) ? $pid['pid'] : 0;
    }

    
    /**
     * Löscht alle XML-Dateien und Ordner, die älter als ein halbes Jahr sind
     */
    public function deleteOlderFiles(){
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        $deleteState = $configuration['deleteOlderFiles'];
        
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
    
    
    /**
     * Konvertiert neue Logeinträge, die noch nicht in der History Tabelle erfasst sind, 
     * zu HistoryData-Objekten
     * 
     * @param \TYPO3\Deployment\Domain\Model\LogData $entry
     * @return \TYPO3\Deployment\Domain\Model\History
     */
    public function convertFromLogDataToHistory($entry){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $res = $con->exec_SELECTgetSingleRow('*', $entry->getTable(), 'uid='.$entry->getRecuid());
        $sRes = serialize($res);
        
        /** @var \TYPO3\Deployment\Domain\Model\History $history */
        $history = new History();
        $history->setUid($entry->getRecuid());
        $history->setSysLogUid('NEW');
        $history->setHistoryData($sRes);
        $history->setFieldlist('*');
        $history->setRecuid($entry->getRecuid());
        $history->setTablename($entry->getTable());
        $history->setTstamp($entry->getTstamp());
        $history->setPid($res['pid']);
        
        return $history;
    }
    
    
    /**
     * Sucht die übergebene UID innerhalb der in der Registry gespeicherten History Daten
     * 
     * @param string $uid
     * @return \TYPO3\Deployment\Domain\Model\HistoryData
     */
    public function compareDataWithRegistry($uid){
        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
        $data = unserialize($registry->get('deployment', 'storedHistoryData'));
        
        foreach($data as $hisdata){
            if($hisdata->getUid() == $uid){
                return $hisdata;
            }
        }
    }


    /**
     * @return Array $logdata
     */
    public function getHistoryData() {
        return $this->historyData;
    }

    /**
     * @param array $historyEntries
     */
    public function setHistoryData($historyEntries) {
        $this->historyData = $historyEntries;
    }

    /**
     * @return \TYPO3\Deployment\Domain\Model\LogData 
     */
    public function getLogData() {
        return $this->logData;
    }

    /**
     * @param \TYPO3\Deployment\Domain\Model\LogData $logData
     */
    public function setLogData(\TYPO3\Deployment\Domain\Model\LogData $logData) {
        $this->logData = $logData;
    }

    /**
     * @return array
     */
    public function getDeployData() {
        return $this->deployData;
    }

    /**
     * @param array $deployData
     */
    public function setDeployData($deployData) {
        $this->deployData = $deployData;
    }

    /**
     * @return \XmlWriter
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