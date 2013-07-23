<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

/**
 * AbstractDataService
 * Class for general data service methods
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class AbstractDataService {

    /**
     * Returns the uuid by uid
     *
     * @param string $uid
     * @param string $table
     *
     * @return string
     */
    public function getUuidByUid($uid, $table) {
        $uuid = $this->getDatabase()->exec_SELECTgetSingleRow('uuid', $table, 'uid = ' . $uid);
        
        return(!empty($uuid['uuid'])) ? $uuid['uuid'] : 0;
    }
    
    
    /**
     * Return the uid by uuid
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
     * Returns the uid by uuid
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
     * Query control result
     * 
     * @param string $field
     * @param string $table
     * @param string $uuid
     * @return mixed <b>string</b> or <b>null</b>
     */
    public function getControlResult($field, $table, $uuid){
        $uuid = $this->getDatabase()->exec_SELECTgetSingleRow($field, $table, "uuid='".$uuid."'");
        
        return (!empty($uuid[$field])) ? $uuid[$field] : null;
    }

    
    /**
     * Get the TYPO3 database
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
    
    
    /**
     * Returns the current database name
     * 
     * @return string
     */
    protected function getCurrentDatabaseName(){
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['database'];
    }
    
    
    /**
     * Returns the current database hostname
     * 
     * @return string
     */
    protected function getCurrentDatabaseHost(){
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['host'];
    }
    
    
    /**
     * Returns the current database username
     * 
     * @return string
     */
    protected function getCurrentDatabaseUser(){
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['username'];
    }
    
    
    /**
     * Returns the current database password
     * 
     * @return string
     */
    protected function getCurrentDatabasePassword(){
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['password'];
    }
}