<?php

/**
 * AbstractDataService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Service;

/**
 * AbstractDataService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class AbstractDataService {
    
    /**
     * Gibt die entsprechende UUID passend zum Datensatz zurück
     * 
     * @param string $uid
     * @param string $table
     * @return string
     */
    public function getUuid($uid, $table){
        /** @var TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $uuid = $con->exec_SELECTgetSingleRow('uuid', $table, 'uid = '.$uid);
        
        return $uuid['uuid'];
    }
    
    
    /**
     * Get the TYPO3 database
     * 
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
}