<?php

/**
 * FailureService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FailureService
 *
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class FailureService extends AbstractDataService {

    /**
     * Gibt die Einträge potenzieller Fehler der Datenbank zurück
     * 
     * @param array $failures
     * @return array
     */
    public function getFailureEntries($failures) {
        $failuresFromDatabase = array();
        $usedFailureEntries = array();
        $allEntries = array();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');

        if ($con->isConnected()) {
            foreach ($failures as $failure) {
                $keyListArr = array();
                // Array mit Schlüsseln erstellen
                foreach ($failure as $key => $value) {
                    if ($key != 'tablename' && $key != 'fieldlist') {
                        $keyListArr[] = $key;
                    }
                }
                // Liste erstellen
                $keyList = implode(',', $keyListArr);

                $res = $con->exec_SELECTgetSingleRow($keyList, $failure['tablename'], "uuid='" . $failure['uuid'] . "'");
                if ($res != null) {
                    $usedFailureEntries[] = $failure;
                    $failuresFromDatabase[] = $res;
                }
            }
        }
        $allEntries['usedFailures'] = $usedFailureEntries;
        $allEntries['fromDatabase'] = $failuresFromDatabase;

        return $allEntries;
    }

    
    /**
     * Splittet das übergebene Array zur Weiterverarbeitung
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
     * Gibt Differenzen zwischen den Datensätzen zurück
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
                if ($key == 'tablename' && $key == 'fieldlist') {
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
    public function proceedFailureEntries($failures, $storedFailures) {
        $fails = array();
        $res = array();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');

        // Fehler aus Registry deserialisieren
        $unserializedFailures = unserialize($storedFailures);

        // Einträge splitten
        foreach ($failures as $fail) {
            $fails[] = explode('.', $fail);
        }

        // wenn 'list' ausgewählt wurde dann update, bei database nichts tun
        foreach ($fails as $entry) {
            if ($entry[0] == 'list') {
                foreach ($unserializedFailures as $unFail) {
                    if ($unFail['tablename'] == $entry[1] && $unFail['uuid'] == $entry[2]) {
                        // nicht benötigte Einträge entfernen
                        unset($unFail['tablename']);
                        if (isset($unFail['fieldlist']) || isset($unFail['uid']) || isset($unFail['pid'])) {
                            unset($unFail['fieldlist']);
                            unset($unFail['uid']);
                            unset($unFail['pid']);
                        }

                        // Timestamp ändern
                        $unFail['tstamp'] = time();

                        // In DB updaten
                        // @todo HDNET ist $unFail hier richtig?
                        $res[] = $con->exec_UPDATEquery($entry[1], 'uuid=' . $entry[2], $unFail);
                    }
                }
            }
        }

        // HDNET
        # return !in_array(false, $res);

        foreach ($res as $result) {
            if ($result === false) {
                return false;
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
     * Konvertiert Timestamps zur korrekten Darstellung
     * 
     * @param array $diff
     * @return array
     */
    public function convertTimestamps($diff) {
        $arr = array();
        $count = 0;

        foreach ($diff as $entry) {
            foreach ($entry as $key => $value) {
                if ($key === 'tstamp' || $key === 'crdate' || $key === 'modification_date' || $key === 'creation_date') {
                    // Zeichen bis zum ersten '>' entfernen. Dann von 1.-10. Zeichen zurückgeben -> date1
                    $date1 = substr(strstr($value, '>'), 1, 10);
                    // Alle Zeichen in Charlist entfernen -> date2
                    $date2 = trim(str_replace('</span>', '', str_replace('<span class="diff-r"></span> <span class="diff-g">', '', str_replace($date1, '', $value))));

                    // Daten umwandeln und in Zeichenkette ersetzen, so dass die span-Tags erhalten bleiben
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