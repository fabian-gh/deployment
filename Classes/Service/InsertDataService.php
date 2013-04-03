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
class InsertDataService {

    /**
     * @param array $dataArr
     */
    public function insertDataIntoTable($dataArr) {
        // Fremddatenbank initialiseren
        $this->getDatabase()->connectDB('localhost', 'root', 'root', 't3masterdeploy');
        
        // TODO: Gnaze Verarbeitung!!!!!!
        foreach ($dataArr as $data) {
            foreach ($data as $key => $value) {
                DebuggerUtility::var_dump($key);
                DebuggerUtility::var_dump($value);
            }
        }

        // ohne Parameter (aktuelle DB)
        $this->getDatabase()->connectDB();

        die();
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }

}