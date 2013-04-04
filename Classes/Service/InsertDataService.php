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
     * @param array $dataArr
     * @return boolean
     */
    public function insertDataIntoTable($dataArr) {
        $data = array();
        $fields = $values = $insertParam = array();
        $con = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
        
        // Fremddatenbank initialiseren
        $con->connectDB('localhost', 'root', 'root', 't3masterdeploy');
        
        foreach ($dataArr as $data) {
            foreach ($data as $key => $value) {
                // alle Schlüsselfelder überprüfen
                if($key === 'tablename'){
                    $table = $value;
                } elseif($key === 'fieldlist'){
                    $fields = explode(',', $value);
                } elseif($key === 'uid'){
                    $uid = $value;
                }
                
                // Felder mit den Schlüsseln der Daten abgleichen um diese einzufügen
                foreach($fields as $field){
                    if($field == $key && $field != 'l18n_diffsource'){
                        $insertParam = array($field => $value);
                        $con->exec_UPDATEquery($table, $uid, $insertParam);
                    }
                }
                
                // TODO: Konflikte überprüfen
            }
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