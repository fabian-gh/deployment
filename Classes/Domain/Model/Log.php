<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

/**
 * Log
 * Class for log entries from table
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class Log extends AbstractModel{
    
    /**
     * Timestamp
     * 
     * @var string $tstamp
     */
    protected $tstamp;
    
    /**
     * Log data
     * 
     * @var string $log_data
     */
    protected $logData;
    
    /**
     * Action
     * 
     * @var string $action
     */
    protected $action;
    
    /**
     * Tablename
     * 
     * @var string $tablename
     */
    protected $tablename;
    
    
    /**
     * Returns log_data
     * 
     * @return string
     */
    public function getLogData() {
        return $this->logData;
    }
    
    /**
     * Sets log_data
     * 
     * @param string $logData
     */
    public function setLogData($logData){
        $this->logData = $logData;
    }

    /**
     * Returns timestamp
     * 
     * @return string
     */
    public function getTstamp() {
        return $this->tstamp;
    }

    /**
     * Sets timestamp
     * 
     * @param string $tstamp
     */
    public function setTstamp($tstamp) {
        $this->tstamp = $tstamp;
    }
    
    /**
     * Returns action
     * 
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Sets action
     * 
     * @param string $action
     */
    public function setAction($action) {
        $this->action = $action;
    }  
    
    /**
     * Returns tablename
     * 
     * @return string
     */
    public function getTablename() {
        return $this->tablename;
    }

    /**
     * Sets tablename
     * 
     * @param string $tablename
     */
    public function setTablename($tablename) {
        $this->tablename = $tablename;
    }
}