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
     * @var string
     */
    protected $uid;
    
    /**
     * @var string 
     */
    protected $pid;

    /**
     * @var string
     */
    protected $data;
    
    /**
     * @var string
     */
    protected $table;
    
    /**
     * @var string
     */
    protected $recuid;
    
    /**
     * @var string
     */
    protected $tstamp;
    
    /**
     * @var string
     */
    protected $action;


    /**
     * @return string
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }
    
    /**
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table) {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getRecuid() {
        return $this->recuid;
    }

    /**
     * @param string $recId
     */
    public function setRecuid($recuid) {
        $this->recuid = $recuid;
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
    public function getPid() {
        return $this->pid;
    }

    /**
     * @param string $pid
     */
    public function setPid($pid) {
        $this->pid = $pid;
    }   
}