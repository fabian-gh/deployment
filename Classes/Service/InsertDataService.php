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
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * InsertDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class InsertDataService{

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
     */
    public function insertDataIntoTable($dataArr) {
        $data = $fields = $insertParams = $updateParams = $updateParams2 = $alreadyExists = $assump = array();
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
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
     */
    public function insertMediaDataIntoTable($dataArr) {
        $table = 'sys_file';
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
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
    
    
    
    public function processNotIndexedFiles($fileArr){
        $con = $this->getDatabase();
        $con->debugOutput = true;
        $con->debug_lastBuiltQuery;
        
        foreach($fileArr as $file){
            $resFact = ResourceFactory::getInstance();
            $res = $resFact->getFileObjectFromCombinedIdentifier('/fileadmin/'.$file);
            // hier werden die Daten selbststädnig indexiert, 
            // unabhängig davon welche Methode aufgerufen wird
            $res->isIndexed();
            
            // '/fileadmin' aus dem identifier entfernen --> funktioniert nicht
            /*if($res->isIndexed()){
                $identifier = $res->getProperty('identifier');
                $croppedIdentifier = substr($identifier, 10);
                $res->updateProperties(array('identifier' => $croppedIdentifier));
            }*/
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
     * @return DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
    
}





// Alter Code für das Einfügen/Aktualisieren der Daten

// falls false -> insert, falls ergebnis -> update
/*if($alreadyExists == false){
    $insertParams[] = array(
        'uid' => $uid,
        'pid' => ($pid == null) ? -1 : $pid,
        'tstamp' => time(),
        'crdate' => time(),
        $contentkey => $contentvalue
    );

    foreach($insertParams as $param){
        //$con->exec_INSERTquery($table, $param);
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
        //$con->exec_UPDATEquery($table, 'uid = '.$param['uid'], $param);
    }
}*/