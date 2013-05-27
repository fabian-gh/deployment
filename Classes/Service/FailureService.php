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
        $con = $this->getDatabase();
        
        if($con->isConnected()){
            foreach($failures as $failure){
                DebuggerUtility::var_dump($failure['tablename']);
                DebuggerUtility::var_dump($failure['uuid']);
                //$failureEntries[] = $con->exec_SELECTgetSingleRow('*', $failure['tablename'], 'uuid='.$failure['uuid']);
            }
        }die();
        
        return $failureEntries;
    }
}