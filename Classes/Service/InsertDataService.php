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
     * Fügt Daten in eine Tabelle ein. Unabhängig von der Tabelle
     * 
     * @param array $dataArr
     * @return boolean
     */
    public function insertDataIntoTable($dataArr) {
        $data = array();
        $fields = array();
        $insertParams = array();
        $updateParams = array();
        $updateParams2 = array();
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        
        // Fremddatenbank initialiseren
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy');
        
        // Schlüsselfelder überprüfen
        foreach($dataArr as $data) {
            foreach($data as $key => $value) {
                if($key === 'tablename'){
                    $table = $value;
                } elseif($key === 'fieldlist'){
                    unset($key);
                } elseif($key === 'uid'){
                    $uid = $value;
                } elseif($key === 'pid'){
                    $pid = $value;
                } else{
                    $contents[$key] = $value;
                }
            }
            
            // pro Paket Felder mit den Schlüsseln der Daten abgleichen um diese einzufügen
            foreach($contents as $contentkey => $contentvalue){
                // prüfen ob Datensatz bereits existiert
                $controlResult = $con->exec_SELECTgetSingleRow('uid', $table, 'uid = '.$uid);
                
                // falls ja, dann update, ansonsten einfügen
                if($controlResult != false){
                    $updateParams[] = array(
                        'uid'       => $uid,
                        'pid'       => ($pid == null) ? -1 : $pid,
                        $contentkey => $contentvalue,
                        'tstamp'    => time()
                    );
                    
                    foreach($updateParams as $param){
                        //$con->exec_UPDATEquery($table, 'uid = '.$param['uid'], $param);
                    }
                } else {
                    // Prüfen ob Datensatz evtl. unter anderer ID existiert, 
                    // abhängig vom label-Feld des TCA
                    GeneralUtility::loadTCA($table);
                    $label = $GLOBALS['TCA'][$table]['ctrl']['label'];

                    if($label != $contentkey){
                        $alreadyExists = $con->exec_SELECTgetSingleRow("uid, $contentkey", $table, $contentkey." LIKE '%$contentvalue%'");
                    } else {
                        DebuggerUtility::var_dump($label);
                        DebuggerUtility::var_dump($table);
                        DebuggerUtility::var_dump($contentvalue);die();
                        $alreadyExists = $con->exec_SELECTgetSingleRow("uid, $label", $table, $label." LIKE '%$contentvalue%'");
                        DebuggerUtility::var_dump($alreadyExists);
                    }

                    // falls nein -> insert, falls ja -> update
                    if($alreadyExists == false){
                        DebuggerUtility::var_dump('if');die();
                        $insertParams[] = array(
                            'uid' => $uid,
                            'pid' => ($pid == null) ? -1 : $pid,
                            'tstamp' => time(),
                            'crdate' => time(),
                            $contentkey => $contentvalue
                        );

                        foreach($insertParams as $param){
                            $con->exec_INSERTquery($table, $param);
                        }
                    } else {
                        // vor dem Update verfeinerte Suche, ob der Datensatz evtl.
                        // doch schon existiert
                        $uidFromTable = $alreadyExists['uid'];
                        $field = $alreadyExists[$contentkey];
                        $count = strlen($field);
                        $sCount = strlen($contentvalue);
                        if($count > $sCount){
                            $erg = strstr($count, $sCount);
                        } elseif($count < $sCount) {
                            $erg = strstr($sCount, $count);
                        } else {
                            $erg = $contentvalue;
                        }

                        $updateParams2[] = array(
                            'uid'       => $uidFromTable,
                            'pid'       => ($pid == null) ? -1 : $pid,
                            $contentkey => $erg,
                            'tstamp'    => time()
                        );

                        foreach($updateParams2 as $param){
                            $con->exec_UPDATEquery($table, 'uid = '.$param['uid'], $param);
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
        }
        
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