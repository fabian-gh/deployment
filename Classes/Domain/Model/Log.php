<?php

/**
 * Log
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

/**
 * Log
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class Log extends AbstractModel{
    
    /**
     * @var string
     */
    protected $tstamp;
    
    /**
     * @var string
     */
    protected $logData;
    
    /**
     * @var string
     */
    protected $action;
    
    /**
     * @var string
     */
    protected $tablename;
    
    
    /**
     * @return string
     */
    public function getLogData() {
        return $this->logData;
    }
    
    /**
     * @param string $logData
     */
    public function setLogData($logData){
        $this->logData = $logData;
    }

    /**
     * @return string
     */
    public function getTstamp() {
        return $this->tstamp;
    }

    /**
     * @param string $tstamp
     */
    public function setTstamp($tstamp) {
        $this->tstamp = $tstamp;
    }
    
    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action) {
        $this->action = $action;
    }  
    
    /**
     * @return string
     */
    public function getTablename() {
        return $this->tablename;
    }

    /**
     * @param string $tablename
     */
    public function setTablename($tablename) {
        $this->tablename = $tablename;
    }
}