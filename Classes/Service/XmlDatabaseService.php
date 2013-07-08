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
use \TYPO3\Deployment\Domain\Model\Log;
use \TYPO3\Deployment\Domain\Model\LogData;
use \TYPO3\Deployment\Domain\Model\HistoryData;
use \TYPO3\Deployment\Domain\Model\History;
use \TYPO3\Deployment\Service\FileService;
use \TYPO3\Deployment\Service\RegistryService;

/**
 * XmlDatabaseService
 * Class for creating and reading the database xml file
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class XmlDatabaseService extends AbstractDataService {

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
     * Write new and changed entries into a xml file
     */
    public function writeXML() {
        $newInsert = array();
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();

        // new XMLWriter-Object
        $this->xmlwriter = new \XMLWriter();

        // document properties
        $this->xmlwriter->openMemory(); // write data in memory
        $this->xmlwriter->setIndent(TRUE); // activate indent
        $this->xmlwriter->startDocument('1.0'); // create document tag
        // Document Type Definition (DTD)
        $this->xmlwriter->startDtd('changeSet');
        $this->xmlwriter->writeDtdElement('changeSet', '(data+)');
        $this->xmlwriter->writeDtdElement('data', 'ANY');
        $this->xmlwriter->endDtd();

        // write data
        $this->xmlwriter->startElement('changeSet');

        foreach ($this->deployData as $cData) {
            /** @var HistoryData $cData */
            // query all new entries
            if(in_array($cData->getTablename(), $configurationService->getDeploymentTables())){
                if ($cData->getSysLogUid() == 'NEW' && $cData->getFieldlist() == '*') {
                    $newInsert = $this->getDatabase()->exec_SELECTgetSingleRow('*', $cData->getTablename(), 'uid=' . $cData->getUid());

                    // for each entry a new data-element
                    $this->xmlwriter->startElement('data');
                    $this->xmlwriter->writeElement('tablename', $cData->getTablename());
                    $this->xmlwriter->writeElement('fieldlist', '*');

                    foreach ($newInsert as $newkey => $newval) {
                        if (!in_array($newkey, $configurationService->getNotDeployableColumns())) {
                            // replace pid with uuid
                            if ($newkey == 'pid') {
                                $pageUuid = $this->getUuidByUid($newval, 'pages');
                                $this->xmlwriter->writeElement('pid', $pageUuid);
                            } 
                            // replace uid_foreign with uuid
                            elseif ($newkey == 'uid_foreign') {
                                // this approach works always, because 'tablenames' in is available in each relation
                                // query reference table for uid_local
                                $table = $this->getDatabase()->exec_SELECTgetSingleRow('tablenames', $cData->getTablename(), 'uid_foreign=' . $newval);
                                // query uuid of the entry
                                $uuid_foreign = $this->getUuidByUid($newval, $table['tablenames']);
                                // process data
                                $this->xmlwriter->writeElement('uid_foreign', $uuid_foreign);
                            } 
                            // replace uid_local with uuid
                            elseif ($newkey == 'uid_local') {
                                // at this place we have to differ, because table_local is not available in each relational
                                if ($cData->getTablename() == 'sys_file_reference') {
                                    $table = $this->getDatabase()->exec_SELECTgetSingleRow('table_local', 'sys_file_reference', 'uid_local=' . $newval);
                                    $uuid_local = $this->getUuidByUid($newval, $table['table_local']);
                                    $this->xmlwriter->writeElement('uid_local', $uuid_local);
                                } 
                                // determination for tt_news
                                elseif ($cData->getTablename() == 'tt_news_cat_mm' || $cData->getTablename() == 'tt_news_related_mm') {
                                    $uuid_local = $this->getUuidByUid($newval, 'tt_news');
                                    $this->xmlwriter->writeElement('uid_local', $uuid_local);
                                }
                            } 
                            // replace header_link (tt_content) with uuid
                            elseif ($newkey == 'header_link' || $newkey == 'link') {
                                $substring = $this->checkLinks($newval);
                                $this->xmlwriter->writeElement($newkey, $substring);
                            } else {
                                $this->xmlwriter->writeElement($newkey, $newval);
                            }
                        }
                    }

                    $this->xmlwriter->endElement();
                } 
                // create changed data
                else {
                    // query pid
                    $pid = $this->getPid($cData->getRecuid(), $cData->getTablename());

                    // create for each entry a new data-element
                    $this->xmlwriter->startElement('data');

                    // write individual field elements
                    $this->xmlwriter->writeElement('tablename', $cData->getTablename());
                    $this->xmlwriter->writeElement('fieldlist', $cData->getFieldlist());
                    $this->xmlwriter->writeElement('pid', $this->getUuidByUid($pid, 'pages'));
                    $this->xmlwriter->writeElement('tstamp', $cData->getTstamp()->getTimestamp());
                    $this->xmlwriter->writeElement('uuid', $this->getUuidByUid($cData->getRecuid(), $cData->getTablename()));

                    // travers changed history entries
                    foreach ($cData->getHistoryData() as $datakey => $data) {
                        if ($datakey == 'newRecord') {
                            foreach ($data as $key => $value) {
                                if ($key === 'header_link' || $key == 'link') {
                                    $substring = $this->checkLinks($value);
                                    $this->xmlwriter->writeElement($key, $substring);
                                } else {
                                    $this->xmlwriter->writeElement($key, $value);
                                }
                            }
                        }
                    }

                    $this->xmlwriter->endElement();
                }
            }
        }

        $this->xmlwriter->endElement();
        $this->xmlwriter->endDocument();
        $writeString = $this->xmlwriter->outputMemory();

        $file = GeneralUtility::tempnam('deploy_');
        GeneralUtility::writeFile($file, $writeString);

        $folder = $fileService->getDeploymentDatabasePathWithTrailingSlash() . date('Y_m_d', time());
        GeneralUtility::mkdir($folder);

        GeneralUtility::upload_copy_move($file, $folder . '/' . date('H-i-s', time()) . '_changes.xml');
    }

    
    /**
     * Reads all not deployed data from the xml
     *
     * @param string $timestamp
     *
     * @return array
     */
    public function readXML($timestamp) {
        $arrcount = 0;
        $validationResult = array();
        $fileArr = array();
        $dateFolder = array();
        $contentArr = array();
        $exFaf = array();
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();

        $filesAndFolders = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $fileService->getDeploymentDatabasePathWithTrailingSlash());

        if ($filesAndFolders) {
            // split file path
            foreach ($filesAndFolders as $faf) {
                $exFaf[] = str_replace('database/', '', strstr($faf, 'database'));
            }

            // split date and time
            $splittedDateTime = array();
            foreach ($exFaf as $dateTime) {
                $splittedDateTime[] = explode('/', $dateTime);
            }

            // for each date an own directory with all filename inside
            foreach ($splittedDateTime as $dateTime) {
                $dateFolder[$dateTime[0]][] = $dateTime[1];
            }
        }

        // read file
        foreach ($dateFolder as $folder => $filename) {
            // extract date from directory
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
                    $validationResult['validation']['database/'.$folder.'/'.$file] = $fileService->xmlValidation($fileService->getDeploymentDatabasePathWithTrailingSlash().$folder.'/'.$file);
                    $xmlString = file_get_contents($fileService->getDeploymentDatabasePathWithTrailingSlash().$folder.'/'.$file);

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
        return array_merge($contentArr, $validationResult);
    }

    
    /**
     * Replace uid with uuid in the assigned link
     *
     * @param string $link
     *
     * @return string
     */
    public function checkLinks($link) {
        $split = explode(':', $link);

        if (is_numeric($link)) {
            return 'page:' . $this->getUuidByUid($link, 'pages');
        } elseif ($split[0] === 'file') {
            $split[1] = $this->getUuidByUid($split[1], 'sys_file');
            return implode(':', $split);
        } else {
            return $link;
        }
    }

    
    /**
     * Get the Difference between the history data
     *
     * @param \TYPO3\Deployment\Domain\Model\HistoryData $historyData
     *
     * @return string
     */
    public function getHistoryDataDiff($historyData) {
        $data = array();
        $differences = array();
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        /** @var $diff \TYPO3\CMS\Core\Utility\DiffUtility */
        $diff = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\DiffUtility');

        // organize date for each entry in an array
        foreach ($historyData as $hisData) {
            /** @var HistoryData $hisData */
            foreach ($hisData->getHistoryData() as $records) {
                foreach ($records as $reckey => $recval) {
                    $data[$hisData->getRecuid()][$reckey][$hisData->getRecuid()][] = $recval;
                }
            }
        }

        // traverse array and create the difference between old/new record
        foreach ($data as $dat) {
            foreach ($dat as $columnkey => $cloumnval) {
                foreach ($cloumnval as $recuid => $dataArr) {
                    if (!in_array($columnkey, $configurationService->getNotDeployableColumns())) {
                        $differences[$recuid][$columnkey][] = $diff->makeDiffDisplay($dataArr[0], $dataArr[1]);
                    }
                }
            }
        }

        return $differences;
    }

    
    /**
     * Unserialize the assigned log-data
     *
     * @param \TYPO3\Deployment\Domain\Model\Log $logData
     *
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

                if ($log->getAction() == '1') {
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
     * Unserialize the assigned history-data
     *
     * @param array<\TYPO3\Deployment\Domain\Model\History> $historyData
     *
     * @return array<\TYPO3\Deployment\Domain\Model\HistoryData> $data
     */
    public function unserializeHistoryData($historyData) {
        $hisData = array();

        if ($historyData != NULL) {
            foreach ($historyData as $his) {
                /** @var HistoryData $his */
                if ($his != NULL) {
                    $this->historyData = new HistoryData();
                    $this->historyData->setPid($his->getPid());
                    $this->historyData->setUid($his->getUid());
                    $this->historyData->setSysLogUid($his->getSysLogUid());

                    $unlogdata = unserialize($his->getHistoryData());

                    // this for each is needed to unserialize the l18n_diffsource-field
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
     * Converts new log entries, which aren't captured in history table, to history entries
     *
     * @param \TYPO3\Deployment\Domain\Model\LogData $entry
     *
     * @return \TYPO3\Deployment\Domain\Model\History
     */
    public function convertFromLogDataToHistory($entry) {
        $res = $this->getDatabase()->exec_SELECTgetSingleRow('*', $entry->getTable(), 'uid=' . $entry->getRecuid());
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
     * Search for the uid inside the history data in the registry
     *
     * @param string $uidTable
     *
     * @return \TYPO3\Deployment\Domain\Model\HistoryData
     */
    public function compareDataWithRegistry($uidTable) {
        /** @var \TYPO3\Deployment\Service\RegistryService $registry */
        $registry = new RegistryService();
        $data = $registry->getStoredHistoryEntries();
        
        $temp = explode('.', $uidTable);
        $uid = $temp[0];
        $table = $temp[1];
        
        foreach ($data as $hisdata) {
            /** @var HistoryData $hisdata */
            if ($hisdata->getUid() == $uid && $hisdata->getTablename() == $table) {
                return $hisdata;
            }
        }
    }

    
    /**
     * Returns the pid of the assigned uid
     *
     * @param string $uid
     * @param string $table
     *
     * @return int
     */
    public function getPid($uid, $table) {
        $pid = $this->getDatabase()->exec_SELECTgetSingleRow('pid', $table, 'uid = ' . $uid);

        return (!empty($pid['pid'])) ? $pid['pid'] : 0;
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