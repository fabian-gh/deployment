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
    public function insertDataIntoTable($dataArr){
        // TODO: Hier muss noch andere DB-Connection initialisiert werden
        
        // TODO: Gnaze Verarbeitung!!!!!!
        foreach($dataArr as $data){
            foreach($data as $key => $value){
                DebuggerUtility::var_dump($key);
                DebuggerUtility::var_dump($value);
            }
        }die();
    }
}

?>
