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
                        $con->exec_UPDATEquery($table, 'uid = '.$param['uid'], $param);
                    }
                } else {
                    // Prüfen ob Datensatz evtl. unter anderer ID existiert, 
                    // abhängig vom label-Feld des TCA
                    GeneralUtility::loadTCA($table);
                    $label = $GLOBALS['TCA'][$table]['ctrl']['label'];
                    
                    if($label != $contentkey){
                        $alreadyExists = $con->exec_SELECTgetSingleRow('uid', $table, $contentkey." LIKE '%$contentvalue%'");
                    } else {
                        $alreadyExists = $con->exec_SELECTgetSingleRow('uid', $table, $label." LIKE '%$contentvalue%'");
                    }

                    // falls nein -> insert, falls ja -> update
                    // neue Datensätze werden mit der pid -1 gekennzeichnet
                    if($alreadyExists == false){
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
                        $updateParams2[] = array(
                            'uid'       => $uid,
                            'pid'       => ($pid == null) ? -1 : $pid,
                            $contentkey => $contentvalue,
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