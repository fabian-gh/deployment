<?php

/**
 * AbstractDataService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

/**
 * AbstractDataService
 *
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class AbstractDataService {

    /**
     * Gibt die entsprechende UUID passend zum Datensatz zurück
     *
     * @param string $uid
     * @param string $table
     *
     * @return string
     */
    public function getUuid($uid, $table) {
        $uuid = $this->getDatabase()->exec_SELECTgetSingleRow('uuid', $table, 'uid = ' . $uid);
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