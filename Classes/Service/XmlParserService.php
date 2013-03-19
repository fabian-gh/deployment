<?php

/**
 * XmlParserService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\Deployment\Domain\Model\LogData;
use \TYPO3\Deployment\Domain\Model\HistoryData;

/**
 * XmlParserService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class XmlParserService {

    /**
     * @var \TYPO3\Domain\Model\HistoryData
     */
    protected $historyData;
    
    /**
     * @var \TYPO3\Domain\Model\LogData 
     */
    protected $logData;

    /**
     * @var array
     */
    protected $contentData;

    /**
     * @var \XmlWriter
     */
    protected $xmlwriter;

    /**
     * @var \SimpleXml
     */
    protected $xmlreader;

    
    /**
     * Geänderte Datensätze in ein XML-Dokument schreiben
     */
    public function writeXML(){
        // Neues XMLWriter-Objekt
        $this->xmlwriter = new \XMLWriter();
        $count = 1;
        // standalone view in template -> array übergeben -> foreach
        // writeFile, readFile? getfilesinfolder aus GeneralUtility::
        DebuggerUtility::var_dump(GeneralUtility::array2xml($this->historydata), '', 3, '', 4);die();

        // Dokumenteneigenschaften
        $this->xmlwriter->openURI('fileadmin/deployment/changes.xml');  // Pfad zur Datei
        DebuggerUtility::var_dump($this->xmlwriter->openURI('fileadmin/deployment/changes.xml'));die();
        $this->xmlwriter->setIndent(TRUE);                              // Einzug aktivieren
        $this->xmlwriter->startDocument('1.0');                         // Document-Tag erzeugen
        $this->xmlwriter->startElement('xml');                          // Element erzeugen
        $this->xmlwriter->writeAttribute('version', '1.0');             // Attribute für das vorherige Element vergeben
        $this->xmlwriter->writeAttribute('encoding', 'UTF_8');
        $this->xmlwriter->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        // Daten schreiben
        $this->xmlwriter->startElement('changeSet');
        foreach ($this->contentData as $cData) {
            // für jeden Datensatz ein neues data-Element mit eigener ID
            $this->xmlwriter->startElement('data');
            $this->xmlwriter->writeAttribute('id', $count);
            foreach ($cData as $key => $value) {
                // Datensatz schreiben
                $this->xmlwriter->writeElement(utf8_encode($key), utf8_encode($value));

                // UTF-8 prüfen. TYPO3 sollte default UTF-8 sein
            }
            $this->xmlwriter->endElement();
            $count++;
        }
        $this->xmlwriter->endElement();
        $this->xmlwriter->endElement();
        $this->xmlwriter->endDocument(); // Dokument schließen
        $this->xmlwriter->flush(); // in Ausgabedatei schreiben
        //$count = 1; // Zähler Reset
    }

    
    /**
     * XML-Datei lesen und in Array abspeichern
     */
    
    // ES GIBT EINE METHODE DIE xml2array() HEIßT, EVTL. BENUTZEN
    public function readXML() {
        // Neues SimpleXMLElement-Objekt erzeugen
        $this->xmlreader = new \SimpleXMLElement('changes.xml', NULL, TRUE);
        // Referenzzähler
        $refcount = 0;
        $arrcount = 0;
        // Daten unterhalb der 'data'-Ebene durchgehen
        foreach ($this->xmlreader->changeSet->data as $dataset) {
            //$count = $dataset->count(); // Gesamtanzahl an Daten = Tupel
            // Kindknoten werden durchschritten und die Daten ausgelesen
            foreach ($dataset as $key => $value) {
                $this->contentData[$arrcount][$key] = (string) $value;
                $refcount++;
            }
            $refcount = 0;
            $arrcount++;
        }
    }

    /**
     * @param \TYPO3\Domain\Model\Log $logData
     * @return array|\TYPO3\Domain\Model\LogData $data
     */
    public function unserializeLogData($logData) {
        if ($logData != NULL) {
            foreach($logData as $log){
                $this->logData = new LogData();
                $unlogdata = unserialize($log->getLogData());
                
                $tableAndId = explode(':', $unlogdata[1]);
                $this->logData->setData($unlogdata[0]);
                $this->logData->setTable($tableAndId[0]);
                $this->logData->setRecId($tableAndId[1]);

                $data[] = $this->logData;
            }
            
            return $data;
        }
        else {
            return $data = array();
        }
    }
    
    /**
     * 
     * @param array|\TYPO3\Domain\Model\History $historyData
     * @return array|\TYPO3\Domain\Model\HistoryData $data
     */
    public function unserializeHistoryData($historyData){
        if ($historyData != NULL) {
            foreach($historyData as $his){
                $this->historyData = new HistoryData();
                $unlogdata = unserialize($his->getHistoryData());
                
                foreach($unlogdata as $key => $value){
                    foreach($value as $k => $val){
                        if(preg_match('/[a-z]{1}:[0-9]+/', $val)){
                            $data[$k] = unserialize($val);
                        } else {
                            $data[$k] = $val;
                        }
                    }
                    $unlogdata[$key] = $data;
                }

                $this->historyData->setHistoryData($unlogdata);

                $hisData[] = $this->historyData;
            }
            return $hisData;
        }
        else {
            return $data = array();
        }
    }
    
    /**
     * Get the TYPO3 database
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
    
    /**
     * @return Array $logdata
     */
    public function getHistoryData() {
        return $this->historydata;
    }
    
    /**
     * @param array $historyEntries
     */
    public function setHistoryData($historyEntries){
        $this->historydata = $historyEntries;
    }

    /**
     * @return Array $contentData
     */
    public function getContentData() {
        return $this->contentData;
    }
    
    /**
     * @param array $contentData
     */
    public function setContentData($contentData){
        $this->contentData = $contentData;
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




// ===============================================================================================

/**
     * sys_log + sys_history Tabellen abfragen auf Änderungen seit dem übergebenem Timestamp
     *
     * @param int $tstamp
     */
    /*public function queryDatabase($tstamp) {
        $data = array();
        /* $query = "SELECT l.tstamp, l.log_data, h.fieldlist
          FROM sys_log as l
          INNER JOIN sys_history as h
          ON l.tstamp = h.tstamp
          WHERE l.tablename != ''
          AND l.tstamp = " . $tstamp . "
          AND h.sys_log_uid > 0
          GROUP BY l.log_data"; */

        /*$rows = $this->getDatabase()->exec_SELECT_mm_query('l.tstamp, l.log_data, h.fieldlist', 'sys_log as l', 'sys_history as h', '', 'l.tablename != "" AND l.tstamp = ' . $tstamp . ' AND h.sys_log_uid > 0', 'l.log_data');

        if (sizeof($rows)) {
            foreach ($rows as $row) {
                $logdata = $this->unserializeLogData($row['log_data']);
                
                $data[] = array(
                    'timestamp' => $row['tstamp'],
                    'table' => $logdata['table'],
                    'field' => $row['fieldlist'],
                    'recordID' => $logdata['recID'],
                    'data' => $logdata['data']
                );
            }
            $this->logdata = $data;
        }*/

        /* while ($row = $result->fetch_assoc()) {
          $logdata = $this->unserializeLogData($row['log_data']);

          $data[] = $row;
          $data[] = array(
          'timestamp' => $row['tstamp'],
          'table' => $logdata['table'],
          'field' => $row['fieldlist'],
          'recordID' => $logdata['recID'],
          'data' => $logdata['data']
          );
          } */

        //$this->logdata = $data;
    //}

    /**
     * Geänderte Datensätze seit dem letzten Timestamp abfragen
     *
     * @param int $tstamp
     */
    /*public function getDataRecordsFromTable($tstamp) {
        $data = array();
        foreach ($this->logdata as $log) {
            $rows = $this->getDatabase()->exec_SELECTgetRows('*', $log['table'], 'tstamp >= ' . $tstamp . ' AND pid = ' . $log['recordID']);
            
            if (sizeof($rows)) {
                foreach ($rows as $row) {
                    $data[] = $row;
                }
                $this->contentData = $data;
            }
        }
    }*/

?>