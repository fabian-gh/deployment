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
    public function getUuidByUid($uid, $table) {
        $uuid = $this->getDatabase()->exec_SELECTgetSingleRow('uuid', $table, 'uid = ' . $uid);
        return $uuid['uuid'];
    }
    
    
    /**
     * Gibt anhand der Parameter die UID zurück
     *
     * @param string $uuid
     * @param string $table
     *
     * @return int
     */
    public function getUidByUuid($uuid, $table) {
        $uid = $this->getDatabase()->exec_SELECTgetSingleRow('uid', $table, "uuid='" . $uuid . "'");

        return (!empty($uid['uid'])) ? $uid['uid'] : 0;
    }
    
    
    /**
     * Gibt anhand der Parameter die PID zurück
     *
     * @param string $uuid
     * @param string $table
     *
     * @return int
     */
    public function getPidByUuid($uuid, $table) {
        $pid = $this->getDatabase()->exec_SELECTgetSingleRow('pid', $table, "uuid='" . $uuid . "'");

        return (!empty($pid['pid'])) ? $pid['pid'] : 0;
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