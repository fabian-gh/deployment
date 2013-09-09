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
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * InsertDataService
 * Class for updating and inserting the data into the database
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class InsertDataService extends AbstractDataService {

    /**
     * Check if the assigned entry has to be updated or inserted
     * If a newer entry exists, than collect entries for bug fixing
     *
     * @param array   $entry
     * @param boolean $flag
     *
     * @return mixed <b>array</b> or <b>true</b>
     */
    protected function checkDataValues($entry) {
        // Connect to test database
        DatabaseService::connectTestDatabaseIfExist();
        
        // query the last update
        $lastModified = $this->getControlResult('tstamp', $entry['tablename'], $entry['uuid']);
        
        // if data not exists, insert it
        if ($lastModified === NULL && $entry['fieldlist'] == '*') {
            $tablename = $entry['tablename'];
            
            // query the pid and replace it
            $entry['pid'] = $this->getUidByUuid($entry['pid'], 'pages');
            
            // query the link and replace it
            if ($entry['header_link'] != '') {
                $split = explode(':', $entry['header_link']);

                if ($split[0] === 'file') {
                    $split[1] = $this->getUidByUuid($split[1], 'sys_file');
                    $entry['header_link'] = implode(':', $split);
                } elseif ($split[0] === 'page') {
                    $entry['header_link'] = $this->getUidByUuid($split[1], 'pages');
                }
            } elseif ($entry['link'] != '') {
                $split = explode(':', $entry['link']);

                if ($split[0] === 'file') {
                    $split[1] = $this->getUidByUuid($split[1], 'sys_file');
                    $entry['link'] = implode(':', $split);
                } elseif ($split[0] === 'page') {
                    $entry['link'] = $this->getUidByUuid($split[1], 'pages');
                }
            }
            // replace uid_foreign & uid_local with uid
            if (isset($entry['uid_foreign']) && isset($entry['uid_local'])) {
                if ($entry['tablename'] == 'sys_file_reference') {
                    $entry['uid_foreign'] = $this->getUidByUuid($entry['uid_foreign'], 'tt_content');
                    $entry['uid_local'] = $this->getUidByUuid($entry['uid_local'], 'sys_file');
                } 
                // case for tt_news
                elseif ($entry['tablename'] == 'tt_news_cat_mm') {
                    $table = $this->getControlResult('tablenames', 'tt_news_cat_mm', $entry['uid_foreign']);
                    $entry['uid_foreign'] = $this->getUidByUuid($entry['uid_foreign'], $table);
                    $entry['uid_local'] = $this->getUidByUuid($entry['uid_local'], 'tt_news');
                } elseif ($entry['tablename'] == 'tt_news_related_mm') {
                    $table = $this->getControlResult('tablenames', 'tt_news_related_mm', $entry['uid_foreign']);
                    $entry['uid_foreign'] = $this->getUidByUuid($entry['uid_foreign'], $table);
                    $entry['uid_local'] = $this->getUidByUuid($entry['uid_local'], 'tt_news');
                }
            }

            // set new timestamp
            $entry['tstamp'] = time();
            unset($entry['tablename']);
            unset($entry['fieldlist']);
            unset($entry['uid']);
            
            $this->getDatabase()->exec_INSERTquery($tablename, $entry);
            
            return true;
        } 
        // if database entry is older than the one to be updated (from xml)
        elseif ($lastModified <= $entry['tstamp']) {
            // notice the tablename before it's getting deleted
            $table = $entry['tablename'];
            
            $entry['pid'] = $this->getUidByUuid($entry['pid'], 'pages');

            // query link and replace
            if ($entry['header_link'] != '') {
                $split = explode(':', $entry['header_link']);

                if ($split[0] === 'file') {
                    $split[1] = $this->getUidByUuid($split[1], 'sys_file');
                    $entry['header_link'] = implode(':', $split);
                } elseif ($split[0] === 'page') {
                    $uid = $this->getUidByUuid($split[1], 'pages');
                    $entry['header_link'] = $uid;
                }
            } elseif ($entry['link'] != '') {
                $split = explode(':', $entry['link']);

                if ($split[0] === 'file') {
                    $split[1] = $this->getUidByUuid($split[1], 'sys_file');
                    $entry['link'] = implode(':', $split);
                } elseif ($split[0] === 'page') {
                    $uid = $this->getUidByUuid($split[1], 'pages');
                    $entry['link'] = $uid;
                }
            }
            // replace uid_foreign & uid_local with uid
            if (isset($entry['uid_foreign']) && isset($entry['uid_local'])) {
                if ($entry['tablename'] == 'sys_file_reference') {
                    $entry['uid_foreign'] = $this->getUidByUuid($entry['uid_foreign'], 'tt_content');
                    $entry['uid_local'] = $this->getUidByUuid($entry['uid_local'], 'sys_file');
                } 
                // case for tt_news
                elseif ($entry['tablename'] == 'tt_news_cat_mm') {
                    $table = $this->getControlResult('tablenames', 'tt_news_cat_mm', $entry['uid_foreign']);
                    $entry['uid_foreign'] = $this->getUidByUuid($entry['uid_foreign'], $table);
                    $entry['uid_local'] = $this->getUidByUuid($entry['uid_local'], 'tt_news');
                } elseif ($entry['tablename'] == 'tt_news_related_mm') {
                    $table = $this->getControlResult('tablenames', 'tt_news_related_mm', $entry['uid_foreign']);
                    $entry['uid_foreign'] = $this->getUidByUuid($entry['uid_foreign'], $table);
                    $entry['uid_local'] = $this->getUidByUuid($entry['uid_local'], 'tt_news');
                }
            }

            $entry['tstamp'] = time();
            // delete tablename, fieldlist and uid
            unset($entry['tablename']);
            unset($entry['fieldlist']);
            unset($entry['uid']);

            $this->getDatabase()->exec_UPDATEquery($table, "uuid='". $entry['uuid']."'", $entry);

            return true;
        } 
        // if last update is younger than the to be updated entry (xml) than 
        // collect them for the failure handling
        elseif ($lastModified > $entry['tstamp']) {
            return $entry;
        }
    }

    
    /**
     * Check if there are page dependencies
     *
     * @param array $dataArr
     *
     * @return mixed <b>true</b> if no dependencies, else <b>array</b>
     */
    public function checkPageTree($dataArr) {
        $pageTreeDepth = array();
        $beforePages = array();
        $count = 0;

        foreach ($dataArr as $data) {
            // if table entries for pages exists
            if ($data['tablename'] == 'pages') {
                // than save the page-uuid and the sorting
                $pageTreeDepth[$count]['pid'] = $data['pid'];
                $pageTreeDepth[$count]['sorting'] = $data['sorting'];
            }
            $count++;
        }
        
        foreach ($dataArr as $data) {
            // check for each uuid if it is repeatedly listed
            foreach ($pageTreeDepth as $ptd) {
                // if yes, than (only constricted on data entries)
                if ($ptd['pid'] == $data['uuid'] && $data['fieldlist'] == '*' && $ptd['sorting'] != null) {
                    // return the page-entry, so it can be inserted before the first priority level
                    $beforePages[] = $data['uuid'];
                }
            }
        }
        
        return (empty($beforePages)) ? TRUE : array_unique($beforePages);
    }

    
    /**
     * Insert or update the data over three priority level away
     *
     * @param array $dataArr
     *
     * @return mixed If no failure <b>true</b>, else <b>array</b>
     */
    public function insertDataIntoTable($dataArr) {
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        $entryCollection = array();
        $secondPriority = array();
        $thirdPriority = array();

        // check page tree dependencies
        $pageTreeCheck = $this->checkPageTree($dataArr);
        if(!empty($pageTreeCheck)){
            foreach ($dataArr as $key => $entry) {
                // insert all dependencies before the first priority level
                // use the uuid for it
                foreach ($pageTreeCheck as $uuid) {
                    // if uuids equals each other
                    if ($uuid == $entry['uuid']) {
                        // insert data
                        $res = $this->checkDataValues($entry);
                        // if result don't match, collect for failure array
                        if(!$res){
                            $entryCollection[] = $res;
                        } 
                        // else delete the entry
                        else {
                            unset($dataArr[$key]);
                        }
                    }
                }
            }
        }
        
        // traverse data and update/insert
        foreach ($dataArr as $firstPriority) {
            // page entries have precedence
            // 1. prioriry level: Ensure that all pages exists before they get referenced
            if ($firstPriority['tablename'] == 'pages') {
                $res = $this->checkDataValues($firstPriority);

                if ($res !== TRUE) {
                    $entryCollection[] = $res;
                }
            } 
            // collect other entries for 2. priority level
            else {
                $secondPriority[] = $firstPriority;
            }
        }
        
        // 2. priority level: if table equals tt_content, than proceed, else collect
        foreach ($secondPriority as $second) {
            if ($second['tablename'] == 'tt_content') {
                $res = $this->checkDataValues($second);

                if ($res !== TRUE) {
                    $entryCollection[] = $res;
                }
            } else {
                $thirdPriority[] = $second;
            }
        }

        // 3. priority level: insert/update data for all other tables
        foreach ($thirdPriority as $third) {
            if (!in_array($third['fieldlist'], $configurationService->getNotDeployableColumns())) {
                $res = $this->checkDataValues($third);

                if ($res !== TRUE) {
                    $entryCollection[] = $res;
                }
            }
        }
        
        // reset test database
        DatabaseService::reset();
        
        return (empty($entryCollection)) ? TRUE : $entryCollection;
    }

    
    /**
     * Comparison for resources over uuid. Modify respectively insert data
     * if they have to be updated or don't exists
     *
     * @param array $dataArr
     *
     * @return mixed If no failure <b>true</b>, else <b>array</b>
     */
    public function insertResourceDataIntoTable($dataArr) {
        $entryCollection = array();
        // connect to test database
        DatabaseService::connectTestDatabaseIfExist();
        
        if ($this->getDatabase()->isConnected()) {
            foreach ($dataArr as $entry) {
                // query last deployment date
                $lastModified = $this->getControlResult('tstamp', 'sys_file', $entry['uuid']);

                // if data doesn't exist, insert
                if ($lastModified === NULL) {
                    unset($entry['tablename']);
                    $entry['tstamp'] = time();

                    // insert data
                    $this->getDatabase()->exec_INSERTquery('sys_file', $entry);
                } 
                // if entry older than the one to be updated (xml)
                elseif ($lastModified < $entry['tstamp']) {
                    unset($entry['tablename']);
                    $entry['tstamp'] = time();

                    // update data
                    $this->getDatabase()->exec_UPDATEquery('sys_file', 'uuid=' . $entry['uuid'], $entry);
                } 
                // if last update younger than the one to be updated (xml)
                elseif ($lastModified > $entry['tstamp']) {
                    $entryCollection[] = $entry;
                }
            }
            
            // reset test database
            DatabaseService::reset();
            
            return (empty($entryCollection)) ? TRUE : $entryCollection;
        }
    }

    
    /**
     * Check if the coloumn uuid exists. If this is the case, also check the value
     * If there is no value, generate it
     */
    public function checkIfUuidExists() {
        $tablefields = array();
        $results = array();
        $inputArr = array();
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configuration */
        $configuration = new ConfigurationService();

        $tables = $configuration->getDeploymentTables();

        if ($this->getDatabase()->isConnected()) {
            foreach ($tables as $table) {
                $tablefields[$table] = $this->getDatabase()->admin_get_fields($table);
            }
        } else {
            $tablefields = NULL;
        }

        if ($tablefields != NULL) {
            foreach ($tablefields as $tablekey => $fields) {
                if (array_key_exists('uuid', $fields)) {
                    /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
                    $results[$tablekey] = $this->getDatabase()->exec_SELECTgetRows('uid, uuid', $tablekey, "uuid='' OR uuid IS NULL");
                }
            }

            foreach ($results as $tabkey => $tabval) {
                foreach ($tabval as $value) {
                    $inputArr = array('uuid' => $fileService->generateUuid());
                    $this->getDatabase()->exec_UPDATEquery($tabkey, 'uid=' . $value['uid'], $inputArr);
                }
            }
        }
    }
}