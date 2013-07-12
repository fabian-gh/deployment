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

/**
 * FailureService
 * Class for failure treatment
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class FailureService extends AbstractDataService {

    /**
     * Returns potential failure entries from the database
     * 
     * @param array $failures
     * @return array
     */
    public function getFailureEntries($failures) {
        $failuresFromDatabase = array();
        $usedFailureEntries = array();
        $allEntries = array();
        
        DatabaseService::connectTestDatabaseIfExist();

        if ($this->getDatabase()->isConnected()) {
            foreach ($failures as $failure) {
                $keyListArr = array();
                // create an array with keys
                foreach ($failure as $key => $value) {
                    if ($key != 'tablename' && $key != 'fieldlist') {
                        $keyListArr[] = $key;
                    }
                }
                // create list for query
                $keyList = implode(',', $keyListArr);

                $res = $this->getDatabase()->exec_SELECTgetSingleRow($keyList, $failure['tablename'], "uuid='" . $failure['uuid'] . "'");
                if ($res != null) {
                    $usedFailureEntries[] = $failure;
                    $failuresFromDatabase[] = $res;
                }
            }
        }
        $allEntries['usedFailures'] = $usedFailureEntries;
        $allEntries['fromDatabase'] = $failuresFromDatabase;
        
        DatabaseService::reset();

        return $allEntries;
    }

    
    /**
     * Splittet das übergebene Array zur Weiterverarbeitung
     * Split the assigned array for further treatment
     * 
     * @param array $entries
     * @param boolean $failurePart
     * @return array
     */
    public function splitEntries($entries, $failurePart = false) {
        if (!$failurePart) {
            return $entries['fromDatabase'];
        } else {
            return $entries['usedFailures'];
        }
    }

    
    /**
     * Return the differences between the entries
     * 
     * @param array $failures
     * @param array $database
     * @return array
     */
    public function getFailureDataDiff($failures, $database) {
        $differences = array();
        $count = 0;
        /** @var \TYPO3\CMS\Core\Utility\DiffUtility $diff */
        $diff = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\DiffUtility');

        foreach ($failures as $failure) {
            foreach ($failure as $key => $value) {
                if ($key == 'fieldlist') {
                    unset($key);
                } else {
                    // if the values differ from each other
                    if($value != $database[$count][$key]){
                        // exclude the timestamp from diff making, because 
                        // the clocks of each system aren't equal
                        // you can add here some more keys which shouldn't been checked
                        if($key != 'tstamp' && $key != 'uid'){
                            $differences[$count][$key] = $diff->makeDiffDisplay($value, $database[$count][$key]);
                        }
                    }
                }
            }
            $count++;
        }
        
        return $differences;
    }

    
    /**
     * Treatment of ticked failures
     * 
     * @param array $failures
     * @param string $storedFailures serialized array from registry
     * @return boolean
     */
    public function proceedFailureEntries($failures, $storedFailures) {
        $fails = array();
        $res = array();
        
        // connect to test database
        DatabaseService::connectTestDatabaseIfExist();

        // split entries
        foreach ($failures as $fail) {
            $fails[] = explode('.', $fail);
        }

        // if 'list' is ticked than update, if 'database' is ticked do nothing
        foreach ($fails as $entry) {
            if ($entry[0] == 'list') {
                foreach ($storedFailures as $sfail) {
                if ($sfail['tablename'] == $entry[1] && $sfail['uuid'] == $entry[2] && $sfail['fieldlist'] != '*') {
                        // remove not needed entries
                        unset($sfail['tablename']);
                        if (isset($sfail['fieldlist']) || isset($sfail['uid']) || isset($sfail['pid'])){
                            unset($sfail['fieldlist']);
                            unset($sfail['uid']);
                            unset($sfail['pid']);
                        }

                        // change timestamp
                        $sfail['tstamp'] = time();
                        
                        // update
                        $res[] = $this->getDatabase()->exec_UPDATEquery($entry[1], "uuid='". $entry[2]."'", $sfail);
                    }
                }
            }
        }
        
        DatabaseService::reset();
        
        if(in_array(false, $res)){
            return false;
        }
        return true;
    }

    
    /**
     * Delete empty entries from the failure array
     * 
     * @param array $failures
     * @return array
     */
    public function deleteEmptyEntries($failures) {
        $fail2 = array();

        foreach ($failures as $fail) {
            if ($fail === null) {
                unset($fail);
            } else {
                $fail2[] = $fail;
            }
        }

        return $fail2;
    }

    
    /**
     * Convert the timestamp for correct presentation
     * 
     * @param array $diff
     * @return array
     * 
     * @deprecated
     */
    public function convertTimestamps($diff) {
        $arr = array();
        $count = 0;

        foreach ($diff as $entry) {
            foreach ($entry as $key => $value) {
                if ($key === 'tstamp' || $key === 'crdate' || $key === 'modification_date' || $key === 'creation_date') {
                    // remove chars until '>'. Return the 1.-10. chars -> date 1
                    $date1 = substr(strstr($value, '>'), 1, 10);
                    // remove all chars in charlist -> date2
                    $date2 = trim(str_replace('</span>', '', str_replace('<span class="diff-r"></span> <span class="diff-g">', '', str_replace($date1, '', $value))));
                    // convert data and replace them in charlist, so that the span-tags get preserved
                    $conDate1 = date('d.m.Y H:i:s', $date1);
                    $conDate2 = date('d.m.Y H:i:s', $date2);

                    $arr[$count][$key] = str_replace($date2, $conDate2, str_replace($date1, $conDate1, $value));
                } else {
                    $arr[$count][$key] = $value;
                }
            }
            $count++;
        }

        return $arr;
    }
}