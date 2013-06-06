<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabian Martinovic <fabian.martinovic(at)t-online.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * FailureService
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
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
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
                $keyListArr = array();
                // Array mit Schlüsseln erstellen
                foreach($failure as $key => $value){
                    if($key != 'tablename' && $key != 'fieldlist'){
                        $keyListArr[] = $key;
                    }
                }
                // Liste erstellen
                $keyList = implode(',', $keyListArr);
                
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
    
    
    /**
     * Verarbeitung der angekreuzten Fehler
     * 
     * @param array $failures
     * @param string $storedFailures serialized array from registry
     * @return boolean
     */
    public function proceedFailureEntries($failures, $storedFailures){
        $fails = array();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');
        
        // Fehler aus Registry deserialisieren
        $unserializedFailures = unserialize($storedFailures);
        
        // Einträge splitten
        foreach($failures as $fail){
            $fails[] = explode('.', $fail);
        }
        
        // wenn 'list' ausgewählt wurde dann update, bei database nichts tun
        foreach($fails as $entry){
            if($entry[0] == 'list'){
                foreach($unserializedFailures as $unFail){
                    if($unFail['tablename'] == $entry[1] && $unFail['uuid'] == $entry[2]){
                        // nicht benötigte Einträge entfernen
                        unset($unFail['tablename']);
                        if(isset($unFail['fieldlist']) || isset($unFail['uid']) || isset($unFail['pid'])){
                            unset($unFail['fieldlist']);
                            unset($unFail['uid']);
                            unset($unFail['pid']);
                        }
                        
                        // Timestamp ändern
                        $unFail['tstamp'] = time();
                        
                        // In DB updaten
                        //$con->exec_UPDATEquery($entry[1], 'uuid='.$entry[2]);
                    }
                }
            }
        }
        return true;
    }
    
    
    /**
     * Löscht leere Einträge aus dem Fehlerarray
     * 
     * @param array $failures
     * @return array
     */
    public function deleteEmptyEntries($failures){
        $fail2 = array();
        
        foreach($failures as $fail){
            if($fail === null){
                unset($fail);
            } else {
                $fail2[] = $fail;
            }
        }
        
        return $fail2;
    }
    
    
    /**
     * Konvertiert Timestamps zur korrekten Darstellung
     * 
     * @param array $diff
     * @return array
     */
    public function convertTimestamps($diff){
        $arr = array();
        $count = 0;
        /** @var \DateTime $dateTime */
        $dateTime = new \DateTime();
        
        foreach($diff as $entry){
            foreach($entry as $key => $value){
                if($key === 'tstamp' || $key === 'crdate' || $key === 'modification_date' || $key === 'creation_date'){
                    $arr[$count][$key] = $dateTime->setTimestamp($value);
                } else {
                    $arr[$count][$key] = $value;
                }
            }
            $count++;
        }
        
        return $arr;
    }
}