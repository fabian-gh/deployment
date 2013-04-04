<?php

/**
 * InsertDataService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Xclass\DatabaseConnection;

/**
 * InsertDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class InsertDataService{

    /**
     * @param array $dataArr
     * @return boolean
     */
    public function insertDataIntoTable($dataArr) {
        $data = $fields = $insertParams = $updateParams = array();
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        
        // Fremddatenbank initialiseren
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy');
        
        foreach($dataArr as $data) {
            foreach($data as $key => $value) {
                // Schlüsselfelder überprüfen
                if($key === 'tablename'){
                    $table = $value;
                } elseif($key === 'uid'){
                    $uid = $value;
                } elseif($key === 'pid'){
                    $pid = $value;
                } else{
                    $contents[$key] = $value;
                }
            }
            
            // Felder mit den Schlüsseln der Daten abgleichen um diese einfügen
            foreach($contents as $contentkey => $contentvalue){
                if($contentkey != 'fieldlist'){
                    // prüfen ob Datensatz bereits existiert
                    $controlResult = $con->exec_SELECTgetSingleRow('uid', $table, 'uid = '.$uid);

                    // falls ja, dann update, ansonsten einfügen
                    if($controlResult != false){
                        $updateParams[] = array(
                            'pid'       => ($pid == null) ? -1 : $pid,
                            $contentkey => $contentvalue,
                            'tstamp'    => time()
                        );
                        
                        foreach($updateParams as $param){
                            $con->exec_UPDATEquery($table, $uid, $param);
                        }
                    } else {
                        // Prüfen ob Datensatz evtl. unter anderer ID existiert
                        GeneralUtility::loadTCA($table);
                        $label = $GLOBALS['TCA'][$table]['ctrl']['label'];
                        if($label != $contentkey){
                            $alreadyExists = $con->exec_SELECTgetSingleRow('uid', $table, $contentkey." LIKE '%$contentvalue%'");
                        } else {
                            $alreadyExists = $con->exec_SELECTgetSingleRow('uid', $table, $label." LIKE '%$contentvalue%'");
                        }

                        // falls ja, dann update, ansonsten insert
                        if($alreadyExists == false){
                            $insertParams = array(
                                'uid' => $uid,
                                'pid' => ($pid == null) ? -1 : $pid,
                                'tstamp' => time(),
                                'crdate' => time(),
                                $contentkey => $contentvalue
                            );

                            $con->exec_INSERTquery($table, $insertParams);
                        }
                    }
                }
            }

            // Variablen zurücksetzen
            unset($table);
            unset($fields);
            unset($uid);
            unset($pid);
            unset($contents);
        }die();
        
        // Datenbankverbindung zurücksetzen
        $this->getDatabase()->connectDB();
        
        return true;
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }

}