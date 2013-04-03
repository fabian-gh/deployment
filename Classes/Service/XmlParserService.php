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
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Domain\Model\History;
use \TYPO3\Deployment\Domain\Model\Log;
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
     * Geänderte Datensätze in ein XML-Dokument schreiben
     */
    public function writeXML() {
        // Daten deserialisieren
        $this->deployData = $this->unserializeHistoryData($this->deployData);

        // Neues XMLWriter-Objekt
        $this->xmlwriter = new \XMLWriter();

        // Dokumenteneigenschaften
        $this->xmlwriter->openMemory();                         // Daten in Speicher schreiben
        $this->xmlwriter->setIndent(TRUE);                      // Einzug aktivieren
        $this->xmlwriter->startDocument('1.0');                 // Document-Tag erzeugen
        $this->xmlwriter->startElement('xml');                  // Element erzeugen
        $this->xmlwriter->writeAttribute('version', '1.0');     // Attribute für das vorherige Element vergeben
        $this->xmlwriter->writeAttribute('encoding', 'UTF_8');
        $this->xmlwriter->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        // Daten schreiben
        $this->xmlwriter->startElement('changeSet');

        foreach ($this->deployData as $cData) {
            /** @var $cData History */
            // für jeden Datensatz ein neues data-Element mit UID als Attribut
            $this->xmlwriter->startElement('data');
            $this->xmlwriter->writeAttribute('uid', $cData->getRecuid());

            // Einzelne Feldelemente schreiben
            $this->xmlwriter->writeElement('tablename', $cData->getTablename());
            $this->xmlwriter->writeElement('fieldlist', $cData->getFieldlist());
            $this->xmlwriter->writeElement('uid', $cData->getRecuid());
            $this->xmlwriter->writeElement('tstamp', $cData->getTstamp());

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

        $this->xmlwriter->endElement();
        $this->xmlwriter->endElement();
        $this->xmlwriter->endDocument(); // Dokument schließen
        $writeString = $this->xmlwriter->outputMemory();

        $file = GeneralUtility::tempnam('deploy_');
        GeneralUtility::writeFile($file, $writeString);

        $folder = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/fileadmin/deployment/' . date('Y_m_d', time());
        GeneralUtility::mkdir($folder);
        
        GeneralUtility::upload_copy_move($file, $folder . '/' . date('H-i-s', time()) . '_changes.xml');
    }

    /**
     * @param $string $timestamp
     * @return array
     */
    public function readXML($timestamp) {
        $arrcount = 0;
        $fileArr = array();
        $dateFolder = array();
        $contentArr = array();

        $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').'/fileadmin/deployment/');

        if ($filesAndFolders) {
            $exFaf = array();
            // Dateipfad ausplitten
            foreach ($filesAndFolders as $faf) {
                $exFaf[] = explode('/', $faf);
            }

            // Initialwert
            $initDate = $exFaf[0][3];
            // pro Ordner/Datum ein Array mit allen Dateinamen darin
            foreach ($exFaf as $item) {
                if ($initDate == $item[3]) {
                    $dateFolder[$initDate][] = $item[4];
                } else {
                    $dateFolder[$item[3]][] = $item[4];
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
                    $xmlString = file_get_contents(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').'/fileadmin/deployment/' . $folder . '/' . $file);

                    $this->xmlreader = new \SimpleXMLElement($xmlString);
                    foreach ($this->xmlreader->changeSet->data as $dataset) {
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
     * @return string
     */
    public function getHistoryDataDiff($historyData){
        $data = array();
        $differences = array();
        /** @var $diff \TYPO3\CMS\Core\Utility\DiffUtility */
        $diff = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\DiffUtility');
        
        foreach($historyData as $hisData){
            foreach($hisData->getHistoryData() as $records){
                foreach($records as $reckey => $recval){
                    $data[$hisData->getRecuid()][$reckey][$hisData->getRecuid()][] = $recval;
                }
            }
        }
        
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
     * @param array<\TYPO3\Deployment\Domain\Model\HistoryData> $data
     * @return array<\TYPO3\Deployment\Domain\Model\HistoryData> $historyArray
     */
    /*public function splitData($data) {
        $arr = array();
        $historyArray = array();

        foreach ($data as $hData) {
            /** @var $hData HistoryData */
            /*foreach ($hData->getHistoryData() as $recordsvalue) {
                if(count($recordsvalue) >= 3) {
                    foreach($recordsvalue as $reckey => $recvalue) {
                        if(!is_array($recvalue)) {
                            $arr[$reckey][] = $recvalue;
                        }
                    }

                    foreach($arr as $key => $data) {
                        if(count($data) >= 2) {
                            $temp = array(
                                'oldRecord' => array(
                                    $key => $data[0]
                                ),
                                'newRecord' => array(
                                    $key => $data[1]
                                )
                            );
                            
                            $historyData = new HistoryData();
                            $historyData->setPid($hData->getPid());
                            $historyData->setUid($hData->getUid());
                            $historyData->setSysLogUid($hData->getSysLogUid());
                            $historyData->setHistoryData($temp);
                            $historyData->setFieldlist($hData->getFieldlist());
                            $historyData->setRecuid($hData->getRecuid());
                            $historyData->setTablename($hData->getTablename());
                            $historyData->setTstamp($hData->getTstamp());

                            $historyArray[] = $historyData;
                        }
                    }
                }
            }
            // TODO Bedingung finden, dass das Ursprungsobjekt des gesplitetetn Objekts nicht hinten anhängt wird
            if(true){
                $historyArray[] = $hData;
            }
        }
        DebuggerUtility::var_dump($historyArray);
        return $historyArray;
    }*/

    /**
     * @param \TYPO3\Deployment\Domain\Model\Log $logData
     * @return array|\TYPO3\Deployment\Domain\Model\LogData $data
     */
    public function unserializeLogData($logData) {
        $data = array();

        if ($logData != NULL) {
            foreach ($logData as $log) {
                /** @var $log Log */
                $this->logData = new LogData();
                $this->logData->setUid($log->getUid());
                $unlogdata = unserialize($log->getLogData());

                $tableAndId = explode(':', $unlogdata[1]);
                $this->logData->setData($unlogdata[0]);
                $this->logData->setTable($tableAndId[0]);
                $this->logData->setRecuid($tableAndId[1]);

                $data[] = $this->logData;
            }

            return $data;
        } else {
            return $data = array();
        }
    }

    /**
     * @param array|\TYPO3\Deployment\Domain\Model\History $historyData
     * @return array|\TYPO3\Deployment\Domain\Model\HistoryData $data
     */
    public function unserializeHistoryData($historyData) {
        $hisData = array();

        if ($historyData != NULL) {
            foreach ($historyData as $his) {
                if ($his != NULL) {
                    /** @var $his History */
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

?>