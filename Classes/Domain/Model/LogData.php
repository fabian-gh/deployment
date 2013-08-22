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
 * LogData
 * Class for processed log data
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class LogData extends AbstractModel{
    
    /**
     * UID
     * 
     * @var string $uid
     */
    protected $uid;
    
    /**
     * PID
     * 
     * @var string $pid
     */
    protected $pid;

    /**
     * Data
     * 
     * @var string $data
     */
    protected $data;
    
    /**
     * Table
     * 
     * @var string $table
     */
    protected $table;
    
    /**
     * Recuid
     * 
     * @var string $recuid
     */
    protected $recuid;
    
    /**
     * Timestamp
     * 
     * @var string $tstamp
     */
    protected $tstamp;
    
    /**
     * Action
     * 
     * @var string $action
     */
    protected $action;


    /**
     * Returns uid
     * 
     * @return string
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * Sets uid
     * 
     * @param string $uid
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }
    
    /**
     * Returns data
     * 
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Sets data
     * 
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * Returns table
     * 
     * @return string
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * Sets table
     * 
     * @param string $table
     */
    public function setTable($table) {
        $this->table = $table;
    }

    /**
     * Returns recuid
     * 
     * @return string
     */
    public function getRecuid() {
        return $this->recuid;
    }

    /**
     * Sets recuid
     * 
     * @param string $recId
     */
    public function setRecuid($recuid) {
        $this->recuid = $recuid;
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
     * Set action
     * 
     * @param string $action
     */
    public function setAction($action) {
        $this->action = $action;
    }
    
    /**
     * Returns pid
     * 
     * @return string
     */
    public function getPid() {
        return $this->pid;
    }

    /**
     * Sets pid
     * 
     * @param string $pid
     */
    public function setPid($pid) {
        $this->pid = $pid;
    }   
}