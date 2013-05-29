<?php

/**
 * FailureService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FailureService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class FailureService extends AbstractDataService {

    /**
     * Gibt die Einträge potenzieller Fehler der Datenbank zurück
     * 
     * @param array $failures
     * @return array
     */
    public function getFailureEntries($failures){
        $failureEntries = array();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');
        
        if($con->isConnected()){
            foreach($failures as $failure){
                // Schlüsseliste erstellen
                $keyList = '';
                foreach($failure as $key => $value){
                    if($key != 'tablename' && $key != 'fieldlist'){
                        $keyList .= $key.',';
                    }
                }
                //letzte Komma entfernen
                $keyList = substr($keyList, 0, strlen($keyList)-1);
                
                $failureEntries[] = $con->exec_SELECTgetSingleRow($keyList, $failure['tablename'], "uuid='".$failure['uuid']."'");
            }
        }
        
        return $failureEntries;
    }
    
    
    /**
     * Gibt Differenzen zwischen den Datensätzen zurück
     * 
     * @param array $failures
     * @param array $database
     * @return array
     */
    public function getFailureDataDiff($failures, $database){
        $differences = array();
        $count = 0;
        /** @var \TYPO3\CMS\Core\Utility\DiffUtility $diff */
        $diff = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\DiffUtility');
        
        foreach($failures as $failure){
            foreach($failure as $key => $value){
                if($key == 'tablename' && $key == 'fieldlist'){
                    unset($key);
                } else {
                    $differences[$count][$key] = $diff->makeDiffDisplay($value, $database[$count][$key]);
                }
            }
            $count++;
        }
        
        return $differences;
    }
}