<?php

/* * *************************************************************
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
 * ************************************************************* */
/**
 * InsertDataService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * InsertDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class InsertDataService extends AbstractDataService {

    /**
     * Prüft ob der übergebene Eintrag eingefügt oder aktualisert werden muss.
     * Falls der Eintrag älter ist als der vorhandene, dann für manuelle Fehlerbehung sammeln
     * 
     * @param array $entry
     * @param boolean $flag
     * @return mixed <b>array</b> or <b>true</b>
     */
    protected function checkDataValues($entry, $flag = false) {
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');

        if ($con->isConnected()) {
            // letzte Aktualisierung abfragen
            $lastModified = $con->exec_SELECTgetSingleRow('tstamp', $entry['tablename'], "uuid = '" . $entry['uuid'] . "'");

            // falls Datensatz noch nicht exisitert, dann einfügen
            if ($lastModified === false) {
                $table = $entry['tablename'];

                // falls neuer Eintrag in pages-Tabelle
                if ($flag === true) {
                    // pid auf 0 setzen
                    $entry['pid'] = 0;
                }
                // falls neuer Eintrag in andere Tabelle
                else {
                    // dann wieder die entsprechende PID abfragen und ersetzen
                    $entry['pid'] = $this->getUid($entry['pid'], 'pages');

                    // Link abfragen und ersetzen
                    if ($entry['header_link'] != '') {
                        $split = explode(':', $entry['header_link']);

                        if ($split[0] === 'file') {
                            $split[1] = $this->getUid($split[1], 'sys_file');
                            $entry['header_link'] = implode(':', $split);
                        } elseif ($split[0] === 'page') {
                            $entry['header_link'] = $this->getUid($split[1], 'pages');
                        }
                    } elseif ($entry['link'] != '') {
                        $split = explode(':', $entry['link']);

                        if ($split[0] === 'file') {
                            $split[1] = $this->getUid($split[1], 'sys_file');
                            $entry['link'] = implode(':', $split);
                        } elseif ($split[0] === 'page') {
                            $entry['link'] = $this->getUid($split[1], 'pages');
                        }
                    }
                    // uid_foreign & uid_local durch UID ersetzen
                    if (isset($entry['uid_foreign']) && isset($entry['uid_local'])) {
                        if ($entry['tablename'] == 'sys_file_reference') {
                            $entry['uid_foreign'] = $this->getUid($entry['uid_foreign'], 'tt_content');
                            $entry['uid_local'] = $this->getUid($entry['uid_local'], 'tt_content');
                        } 
                        // Fall für tt_news
                        elseif($entry['tablename'] == 'tt_news_cat_mm'){
                            $table = $con->exec_SELECTgetSingleRow('tablenames', 'tt_news_cat_mm', "uuid='".$entry['uid_foreign']."'");
                            $entry['uid_foreign'] = $this->getUid($entry['uid_foreign'], $table['tablenames']);
                            $entry['uid_local'] = $this->getUid($entry['uid_local'], 'tt_news');
                        } elseif($entry['tablename'] == 'tt_news_related_mm'){
                            $table = $con->exec_SELECTgetSingleRow('tablenames', 'tt_news_related_mm', "uuid='".$entry['uid_foreign']."'");
                            $entry['uid_foreign'] = $this->getUid($entry['uid_foreign'], $table['tablenames']);
                            $entry['uid_local'] = $this->getUid($entry['uid_local'], 'tt_news');
                        }
                    }
                }

                // neuen Timestamp setzen
                $entry['tstamp'] = time();
                unset($entry['tablename']);
                unset($entry['fieldlist']);
                unset($entry['uid']);

                $con->exec_INSERTquery($table, $entry);

                return true;
            }
            // wenn Eintrag älter ist als der zu aktualisierende
            elseif ($lastModified['tstamp'] <= $entry['tstamp']) {
                // Tabellennamen vor Löschung merken
                $table = $entry['tablename'];

                if ($flag === true) {
                    // entsprechende pid herausfinden
                    $uid = $this->getUid($entry['pid'], 'pages');
                    $entry['pid'] = $uid;
                } else {
                    $pid = $this->getPid($entry['pid'], 'pages');
                    $entry['pid'] = $pid;

                    // Link abfragen und ersetzen
                    if ($entry['header_link'] != '') {
                        $split = explode(':', $entry['header_link']);

                        if ($split[0] === 'file') {
                            $split[1] = $this->getUid($split[1], 'sys_file');
                            $entry['header_link'] = implode(':', $split);
                        } elseif ($split[0] === 'page') {
                            $uid = $this->getUid($split[1], 'pages');
                            $entry['header_link'] = $uid;
                        }
                    } elseif ($entry['link'] != '') {
                        $split = explode(':', $entry['link']);

                        if ($split[0] === 'file') {
                            $split[1] = $this->getUid($split[1], 'sys_file');
                            $entry['link'] = implode(':', $split);
                        } elseif ($split[0] === 'page') {
                            $uid = $this->getUid($split[1], 'pages');
                            $entry['link'] = $uid;
                        }
                    }
                    // uid_foreign & uid_local durch UID ersetzen
                    if (isset($entry['uid_foreign']) && isset($entry['uid_local'])) {
                        if ($entry['tablename'] == 'sys_file_reference') {
                            $entry['uid_foreign'] = $this->getUid($entry['uid_foreign'], 'tt_content');
                            $entry['uid_local'] = $this->getUid($entry['uid_local'], 'tt_content');
                        } 
                        // Fall für tt_news
                        elseif($entry['tablename'] == 'tt_news_cat_mm'){
                            $table = $con->exec_SELECTgetSingleRow('tablenames', 'tt_news_cat_mm', "uuid='".$entry['uid_foreign']."'");
                            $entry['uid_foreign'] = $this->getUid($entry['uid_foreign'], $table['tablenames']);
                            $entry['uid_local'] = $this->getUid($entry['uid_local'], 'tt_news');
                        } elseif($entry['tablename'] == 'tt_news_related_mm'){
                            $table = $con->exec_SELECTgetSingleRow('tablenames', 'tt_news_related_mm', "uuid='".$entry['uid_foreign']."'");
                            $entry['uid_foreign'] = $this->getUid($entry['uid_foreign'], $table['tablenames']);
                            $entry['uid_local'] = $this->getUid($entry['uid_local'], 'tt_news');
                        }
                    }
                }

                $entry['tstamp'] = time();
                // Tabellennamen, Fieldlist und UID löschen
                unset($entry['tablename']);
                unset($entry['fieldlist']);
                unset($entry['uid']);

                // Daten aktualisieren
                $con->exec_UPDATEquery($table, 'uuid='.$entry['uuid'], $entry);

                return true;
            }
            // wenn letzte Aktualisierung jünger ist als einzutragender Stand
            elseif ($lastModified['tstamp'] > $entry['tstamp']) {
                return $entry;
            }
        }
    }

    /**
     * Prüft ob Abhängigkeiten in der Seitenbaumtiefe vorhanden sind
     * 
     * @param array $dataArr
     * @return mixed <b>true</b> if no dependencies, else <b>array</b>
     */
    public function checkPageTree($dataArr) {
        $pageTreeDepth = array();
        $beforePages = array();

        // prüfen ob Seitenbaumabhängigkeiten existieren
        foreach ($dataArr as $data) {
            // wenn Tabelleneinträge für pages-Tabelle vorhanden sind
            if ($data['tablename'] == 'pages') {
                // dann Seiten-UUID speichern
                $pageTreeDepth[] = $data['pid'];
            }
        }

        foreach ($dataArr as $data) {
            // und für jede UUID prüfen ob diese nochmals vorkommt
            foreach ($pageTreeDepth as $uuid) {
                // wenn UUID nochmal vorkommt (nur auf neue Datensätze beschränken)
                if ($uuid == $data['uuid'] && $data['fieldlist'] == '*') {
                    // dann die pages-Einträge zurück liefern, damit diese noch
                    // vor den 1. Prioritätsstufe eingetragen werden
                    $beforePages[] = $data['uuid'];
                }
            }
        }

        return (empty($beforePages)) ? true : array_unique($beforePages);
    }

    /**
     * Einfügen/ Aktualisieren der Daten über 3 Prioritätsstufen hinweg
     * 
     * @param array $dataArr
     * @return mixed If no failure <b>true</b>, else <b>array</b> 
     */
    public function insertDataIntoTable($dataArr) {
        $entryCollection = array();
        $secondPriority = array();
        $thirdPriority = array();

        // Seitenbaumtiefenabhängigkeiten prüfen
        $pageTreeCheck = $this->checkPageTree($dataArr);
        if (!empty($pageTreeCheck)) {
            foreach ($dataArr as $entry) {
                // vor 1. Prioritätstsufe alle Abhängigen Seiten einfügen
                if ($pageTreeCheck !== true) {
                    // hierfür die UUIDs vergleichen
                    foreach ($pageTreeCheck as $uuid) {
                        // falls diese gleich sind
                        if ($uuid == $entry['uuid']) {
                            // Daten verarbeiten --> einfügen
                            $res = $this->checkDataValues($entry, true);
                            // falls Ergebnis nicht passt, dann in Fehlerarray schreiben
                            if ($res !== true) {
                                $entryCollection[] = $res;
                            }
                            // ansonsten den Eintrag entfernen
                            else {
                                unset($entry);
                            }
                        }
                    }
                }
            }
        }

        // Daten durchwandern und einfügen/aktualisieren
        foreach ($dataArr as $firstPriority) {
            // page-Einträge haben Vorrang, 1. Priorität
            // Sicherstellung dass erst die Seiten vorhanden sind bevor
            // diese referenziert werden
            if ($firstPriority['tablename'] == 'pages') {
                $res = $this->checkDataValues($firstPriority, true);

                if ($res !== true) {
                    $entryCollection[] = $res;
                }
            }
            // alle anderen Einträge werden gesammelt und im zweiten Schritt verarbeitet, 2. Priorität
            else {
                $secondPriority[] = $firstPriority;
            }
        }

        // zweite Prioritätsstufe
        // wenn Tabelle tt_content entspricht, dann Verarbeitung, ansonsten sammeln
        foreach ($secondPriority as $second) {
            if ($second['tablename'] == 'tt_content') {
                $res = $this->checkDataValues($second);

                if ($res !== true) {
                    $entryCollection[] = $res;
                }
            } else {
                $thirdPriority[] = $second;
            }
        }

        // dritte Prioritätsstufe
        // Daten aller restlichen Tabellen einfügen/aktualisieren
        foreach ($thirdPriority as $third) {
            if ($third['fieldlist'] !== 'l10n_diffsource' || $third['fieldlist'] !== 'l18n_diffsource') {
                $res = $this->checkDataValues($third);

                if ($res !== true) {
                    $entryCollection[] = $res;
                }
            }
        }

        return (empty($entryCollection)) ? true : $entryCollection;
    }

    /**
     * Vergleich der Resourcendatensätze über die UUID. Modifizieren bzw. 
     * einfügen des Datensatzes falls aktualisiert werden muss oder nicht 
     * existiert
     * 
     * @param array $dataArr
     * @return mixed If no failure <b>true</b>, else <b>array</b> 
     */
    public function insertResourceDataIntoTable($dataArr) {
        $entryCollection = array();
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        // Fremddatenbank initialiseren ------>>>>> SPÄTER LÖSCHEN
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy2');

        if ($con->isConnected()) {
            foreach ($dataArr as $entry) {
                // letzte Aktualisierung abfragen
                $lastModified = $con->exec_SELECTgetSingleRow('tstamp', 'sys_file', "uuid = '" . $entry['uuid'] . "'");

                // falls Datensatz noch nicht exisitert, dann einfügen
                if ($lastModified === false) {
                    unset($entry['tablename']);
                    $entry['tstamp'] = time();

                    // Daten einfügen
                    $con->exec_INSERTquery('sys_file', $entry);
                }
                // wenn Eintrag älter ist als der zu aktualisierende
                elseif ($lastModified['tstamp'] < $entry['tstamp']) {
                    unset($entry['tablename']);
                    $entry['tstamp'] = time();

                    // Daten aktualisieren
                    $con->exec_UPDATEquery('sys_file', 'uuid='.$entry['uuid'], $entry);
                }
                // wenn letzte Aktualisierung jünger ist als einzutragender Stand
                elseif ($lastModified['tstamp'] > $entry['tstamp']) {
                    $entryCollection[] = $entry;
                }
            }

            return (empty($entryCollection)) ? true : $entryCollection;
        }
    }

    /**
     * Prüft ob die Spalte UUID existiert. Wenn dies der Fall ist, dann überprüfen
     * ob hier Werte gesetzt sind. Falls nein, dann Werte generieren.
     */
    public function checkIfUuidExists() {
        $tablefields = array();
        $results = array();
        $inputArr = array();
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configuration */
        $configuration = new ConfigurationService();

        $tables = $configuration->getDeploymentTables();

        if ($con->isConnected()) {
            foreach ($tables as $table) {
                $tablefields[$table] = $con->admin_get_fields($table);
            }
        } else {
            $tablefields = null;
        }

        if ($tablefields != null) {
            foreach ($tablefields as $tablekey => $fields) {
                if (array_key_exists('uuid', $fields)) {
                    /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result */
                    $results[$tablekey] = $con->exec_SELECTgetRows('uid, uuid', $tablekey, "uuid='' OR uuid IS NULL");
                }
            }

            foreach ($results as $tabkey => $tabval) {
                foreach ($tabval as $value) {
                    $inputArr = array('uuid' => $fileService->generateUuid());
                    $con->exec_UPDATEquery($tabkey, 'uid=' . $value['uid'], $inputArr);
                }
            }
        }
    }

    /**
     * Gibt anhand der Parameter die UID zurück
     * 
     * @param string $uuid
     * @param string $table
     * @return int
     */
    public function getUid($uuid, $table) {
        $uid = $this->getDatabase()->exec_SELECTgetSingleRow('uid', $table, "uuid='" . $uuid . "'");

        return (!empty($uid['uid'])) ? $uid['uid'] : 0;
    }

    /**
     * Gibt anhand der Parameter die PID zurück
     * 
     * @param string $uuid
     * @param string $table
     * @return int
     */
    public function getPid($uuid, $table) {
        $pid = $this->getDatabase()->exec_SELECTgetSingleRow('pid', $table, "uuid='" . $uuid . "'");

        return (!empty($pid['pid'])) ? $pid['pid'] : 0;
    }

}