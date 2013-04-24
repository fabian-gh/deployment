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
//use \TYPO3\Deployment\Xclass\DatabaseConnection;
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * InsertDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class InsertDataService extends AbstractDataService{
    
    /**
     * Diese Methode vergleicht die UUID's der Datensätze und modifiziert die
     * Datensätze anhand dieser.
     * 
     * @param array $dataArr
     */
    public function newInsertDataIntoTable($dataArr){
        $updateEntries = $insertEntries = array();
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy');
        
        if($con->isConnected()){
            foreach($dataArr as $entry){
                if($entry['fieldlist'] !== 'l10n_diffsource'){
                    $controlResult = $con->exec_SELECTgetSingleRow('uid', $entry['tablename'], "uuid = '".$entry['uuid']."'");
                    
                    if($controlResult != null){
                        // Verarbeitung der einzufügenden Daten
                        $keys = array_keys($entry);
                        foreach($keys as $key){
                            if($key !== 'tablename' && $key !== 'fieldlist' && $key !== 'uid' && $key !== 'pid' && $key !== 'uuid'){
                                $updateKey = $key;
                            }
                        }
                        $updateEntries = array($updateKey => $entry[$key]);
                        
                        // Daten updaten
                        $con->exec_UPDATEquery($entry['tablename'], 'uid='.$controlResult['uid'], $updateEntries);
                    } else {
                        // Verarbeitung der einzufügenden Daten
                        $keys = array_keys($entry);
                        foreach($keys as $key){
                            if($key !== 'tablename' && $key !== 'fieldlist' && $key !== 'uid' && $key !== 'pid' && $key !== 'uuid'){
                                $updateKey = $key;
                            }
                        }
                        $insertEntries = array(
                            'tstamp'    => time(),
                            'crdate'    => time(),
                            $updateKey  => $entry[$key],
                            'pid'       => $entry['pid'],
                            'uuid'      => $entry['uuid'],
                        );
                        
                        // Daten einfügen
                        $con->exec_INSERTquery($entry['tablename'], $insertEntries);
                    }
                }
            }
        }
    }

    
    /**
     * Fügt Daten in eine Tabelle ein. Unabhängig von der Tabelle.
     * Falls ein Datensatz unter einder anderen ID eingetragen sein 
     * sollte, werden jeweils 20 Datensätze drum herum überprüft, ob 
     * evtl ein ähnlicher Inhalt vorhanden ist. 
     * 20 Datensätze deshalb, weil der Wahrscheinlichkeit nach sich
     * Ähnliche nicht weiter drum herum aufhalten würden
     * 
     * @param array $dataArr
     * @return boolean
     * @depracated
     */
    public function insertDataIntoTable($dataArr) {
        $data = $fields = $insertParams = $updateParams = $updateParams2 = $alreadyExists = $assump = array();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        // Fremddatenbank initialiseren
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy');
        
        // Schlüsselfelder überprüfen
        if($con->isConnected()){
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

                        foreach($updateParams as $update){
                            $con->exec_UPDATEquery($table, 'uid = '.$update['uid'], $update);
                        }
                    } else {
                        // Prüfen ob Datensatz evtl. unter anderer ID existiert, 
                        // abhängig vom label-Feld des TCA
                        GeneralUtility::loadTCA($table);
                        $label = $GLOBALS['TCA'][$table]['ctrl']['label'];

                        if($label != $contentkey){
                            $alreadyExists = $con->exec_SELECTgetSingleRow("uid, $contentkey", $table, $contentkey." LIKE '%$contentvalue%'");
                        } else {
                            $alreadyExists = $con->exec_SELECTgetSingleRow("uid, $label", $table, $label." LIKE '%$contentvalue%'");
                        }

                        if($alreadyExists == false){
                            // 10 Einträge vorher und nacher rund um die UID abfragen, weil evtl ein
                            // Ähnlicher Datensatz drum herum sein könnte
                            if($uid >=11){
                                for($i = $uid - 10; $i <= $uid + 10; $i++){
                                    $assump[] = $con->exec_SELECTgetSingleRow($label, $table, "uid = $i");
                                }

                                // Ergebnisse durchlaufen und im Feld nach dem entsprechenden Inhalt suchen
                                foreach($assump as $as){
                                    if($as != false){
                                        foreach($as as $asvalue){
                                            // falls ein String länger sein sollte als der andere sind beide Fälle abgedeckt
                                            $res = array(
                                                '0' => $con->exec_SELECTgetSingleRow('uid', $table, "LOCATE('$contentvalue', '$asvalue')"),
                                                '1' => $con->exec_SELECTgetSingleRow('uid', $table, "LOCATE('$asvalue', '$contentvalue')")
                                            );
                                        }
                                    }
                                }
                            } 
                            // falls UID zwischen 1 und 10
                            elseif($uid >= 1 && $uid <= 10) {
                                // maximale Zahl die Subtrahiert werden darf
                                $maxSub = $uid - 1;

                                for($i = $uid - $maxSub; $i <= $uid + 10; $i++){
                                    $assump[] = $con->exec_SELECTgetSingleRow($label, $table, "uid = $i");
                                }

                                // Ergebnisse durchlaufen und im Feld nach dem entsprechenden Inhalt suchen
                                foreach($assump as $as){
                                    if($as != false){
                                        foreach($as as $asvalue){
                                            // falls ein String länger sein sollte als der andere sind beide Fälle abgedeckt
                                            $res = array(
                                                '0' => $con->exec_SELECTgetSingleRow('uid', $table, "LOCATE('$contentvalue', '$asvalue')"),
                                                '1' => $con->exec_SELECTgetSingleRow('uid', $table, "LOCATE('$asvalue', '$contentvalue')")
                                            );
                                        }
                                    }
                                }
                            } 
                            // Ansonsten existiert Datensatz wirklich noch nicht -> einfügen
                            else {
                                $insertParams[] = array(
                                    'uid'       => $uid,
                                    'pid'       => ($pid == null) ? -1 : $pid,
                                    $contentkey => $contentvalue,
                                    'tstamp'    => time()
                                );

                                foreach($insertParams as $insert){
                                    $con->exec_INSERTquery($table, $insert);
                                }
                            }

                            // Ergebnisse aktualisieren
                            foreach($res as $r){
                                if($r != false){
                                    $updateParams[] = array(
                                        'uid'       => $r['uid'],
                                        'pid'       => ($pid == null) ? -1 : $pid,
                                        $contentkey => $contentvalue,
                                        'tstamp'    => time()
                                    );

                                    foreach($updateParams as $update){
                                        $con->exec_UPDATEquery($table, 'uid = '.$update['uid'], $update);
                                    }
                                }
                            }

                        }
                        // Falls Daten ansonsten existieren -> aktualiseren
                        else {
                            $updateParams2[] = array(
                                'uid'       => $uid,
                                'pid'       => ($pid == null) ? -1 : $pid,
                                $contentkey => $contentvalue,
                                'tstamp'    => time()
                            );

                            foreach($updateParams2 as $update2){
                                $con->exec_UPDATEquery($table, 'uid = '.$update2['uid'], $update2);
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
        }
        // Datenbankverbindung zurücksetzen
        $this->getDatabase()->connectDB();
        
        return true;
    }
    
    
    /**
     * Fügt die gelesenen XML-Daten in die sys_file-Tabelle der Fremddatenbank ein
     * 
     * @param array $dataArr
     * @depracated
     */
    public function insertMediaDataIntoTable($dataArr) {
        $table = 'sys_file';
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        // Fremddatenbank initialiseren
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy');
        $con->debugOutput = true;
        $con->debug_lastBuiltQuery;
        
        if($con->isConnected()){
            foreach($dataArr as $data){
                $controlResult = $con->exec_SELECTgetSingleRow('uid, tstamp', $table, 'identifier = "'.$data['identifier'].'"');
                
                // wenn Datensatz bereits exisitert
                if($controlResult != false){
                    // und der tstamp ein anderer ist
                    if($controlResult['tstamp'] != $data['tstamp']){
                        // leere fileReference Einträge entfernen
                        if(strlen($data['fileReference']) <= 34){
                            unset($data['fileReference']);
                        } else {
                            // TODO: File Reference Handling
                        }
                        // und dann aktuslisieren
                        $con->exec_UPDATEquery($table, 'uid = '.$data['uid'], $data);
                    }
                } else {
                    // ansonsten neuen Datensatz einfügen
                    if(strlen($data['fileReference']) <= 34){
                        unset($data['fileReference']);
                    } else {
                        // TODO: File Reference Handling
                    }
                    // dabei die uid entfernen, da dies noch die alte ist
                    unset($data['uid']);
                    $con->exec_INSERTquery($table, $data);
                }
            }
        }
    }
    
    
    /**
     * Nicht indizierte Daten in Tabelle eintragen
     * 
     * @param array $fileArr
     */
    public function processNotIndexedFiles($fileArr){
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        
        foreach($fileArr as $file){
            $resFact = ResourceFactory::getInstance();
            $res = $resFact->getFileObjectFromCombinedIdentifier('/fileadmin/'.$file);
            // hier werden die Daten selbststädnig indexiert, 
            // unabhängig davon welche Methode aufgerufen wird
            $res->isIndexed();
        }
        
        if($con->isConnected()){
            $fileRep = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            $res = $fileRep->findByIdentifierWithoutHeadingSlash('/fileadmin/');
            
            foreach($res as $file){
                $identifier = $file->getIdentifier();
                $croppedIdentifier = substr($identifier, 10);
                
                $con->exec_UPDATEquery('sys_file', 'uid='.$file->getUid(), array('identifier' => $croppedIdentifier));
            }
        }
    }

    
    /**
     * Prüft ob die Spalte UUID existiert. Wenn dies der Fall ist, dann überprüfen
     * ob hier Werte gesetzt sind. Falls nein, dann Werte generieren.
     */
    public function checkIfUuidExists(){
        $tablefields = $results = $tables = $inputArr = array();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        $tables = GeneralUtility::trimExplode(',', $configuration['deploymentTables'], TRUE);
        
        if($con->isConnected()){
            foreach($tables as $table){
                $tablefields[$table] = $con->admin_get_fields($table);
            }
        } else {
            $tablefields = null;
        }
        
        if($tablefields != null){
            foreach($tablefields as $tablekey => $fields){
                if(array_key_exists('uuid', $fields)){
                    /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
                    $results[$tablekey] = $con->exec_SELECTgetRows('uid, uuid', $tablekey, "uuid=''");
                }
            }
            
            foreach($results as $tabkey => $tabval){
                foreach($tabval as $value){
                    $inputArr = array('uuid' => $this->generateUuid());
                    $con->exec_UPDATEquery($tabkey, 'uid='.$value['uid'], $inputArr);
                }
            }
        }
    }
    
    
    /**
     * Generiert eine UUID
     *
     * @return string
     */
    private function generateUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, 
                mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
    
}