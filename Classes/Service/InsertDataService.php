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
     * Datensätze anhand dieser bzw. fügt den Datensatz neu ein.
     * 
     * @param array $dataArr
     */
    public function insertDataIntoTable($dataArr){
        $updateEntries = $insertEntries = array();
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');
        
        if($con->isConnected()){
            foreach($dataArr as $entry){
                DebuggerUtility::var_dump($entry);
                /*if($entry['fieldlist'] !== 'l10n_diffsource' || $entry['fieldlist'] !== 'l18n_diffsource'){
                    $controlResult = $con->exec_SELECTgetSingleRow('uid', $entry['tablename'], "uuid = '".$entry['uuid']."'");
                    
                    if($controlResult != null && $entry['fieldlist'] != '*'){
                        // Verarbeitung der einzufügenden Daten
                        $keys = array_keys($entry);
                        foreach($keys as $key){
                            if($key !== 'tablename' && $key !== 'fieldlist' && $key !== 'uid' && $key !== 'pid' && $key !== 'uuid'){
                                $updateKey = $key;
                            }
                        }
                        $updateEntries = array($updateKey => $entry[$key]);
                        
                        $con->exec_UPDATEquery($entry['tablename'], 'uid='.$controlResult['uid'], $updateEntries);
                    } else {
                        $controlResult = $con->exec_SELECTgetSingleRow('uid', $entry['tablename'], "uuid = '".$entry['uuid']."'");
                        
                        if($controlResult != null){
                            // Tabellennamen merken bevor er aus dem Array entfernt wird
                            $tablename = $entry['tablename'];

                            // Nicht mehr benötigte Felder entfernen
                            unset($entry['tablename']);
                            unset($entry['fieldlist']);
                            unset($entry['uid']);

                            // neuen Timestamp setzen
                            $entry['tstamp'] = time();
                            
                            $con->exec_INSERTquery($tablename, $entry);
                        }
                    }
                }*/
            }die();
            return true;
        }
    }
    
    
    /**
     * Vergleich der Resourcendatensätze über die UUID. Modifizieren bzw. 
     * einfügen des Datensatzes falls aktualisiert werden muss oder nicht 
     * existiert
     * 
     * @param array $dataArr
     */
    public function insertResourceDataIntoTable($dataArr){
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');
        
        if($con->isConnected()){
            foreach($dataArr as $entry){
                $controlResult = $con->exec_SELECTgetSingleRow('uid, uuid', 'sys_file', "uuid = '".$entry['uuid']."'");
                
                if($controlResult != null){
                    // Daten updaten
                    $con->exec_UPDATEquery('sys_file', 'uid='.$controlResult['uid'], $entry);
                } else {
                    unset($entry['uid']);
                    // Daten einfügen
                    $con->exec_INSERTquery('sys_file', $entry);
                }
            }
            return true;
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
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        
        foreach($fileArr as $file){
            $res = $resFact->getFileObjectFromCombinedIdentifier('/fileadmin/'.$file);
            // selbstständige Indizierung wenn etwas mit $res gemacht wird
            $res->isIndexed();
        }

        if($con->isConnected()){
            $fileRep = GeneralUtility::makeInstance('TYPO3\\Deployment\\Domain\\Repository\\FileRepository');
            $res = $fileRep->findByIdentifierWithoutHeadingSlash('/fileadmin/');
            
            foreach($res as $file){
                $identifier = $file->getIdentifier();
                if(strstr($identifier, '/fileadmin') != false){
                    $croppedIdentifier = substr($identifier, 10);
                    $con->exec_UPDATEquery('sys_file', 'uid='.$file->getUid(), array('identifier' => $croppedIdentifier));
                } else {
                    $con->exec_UPDATEquery('sys_file', 'uid='.$file->getUid(), array('identifier' => $identifier));
                }
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
                    $results[$tablekey] = $con->exec_SELECTgetRows('uid, uuid', $tablekey, "uuid='' OR uuid IS NULL");
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




// Aus alter insertDataIntoTable
// ========================================================================
/*// Verarbeitung der einzufügenden Daten
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
$con->exec_INSERTquery($entry['tablename'], $insertEntries);*/
// ========================================================================