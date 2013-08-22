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
 * History
 * Class for History Entries from table
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class History extends AbstractModel {

    /**
     * UID
     * 
     * @var string $uid
     */
    protected $uid;

    /**
     * Sys log uid
     * 
     * @var string $sysLogUid
     */
    protected $sysLogUid;

    /**
     * History data
     * 
     * @var string $history_data
     */
    protected $historyData;

    /**
     * Fieldlist
     * 
     * @var string $fieldlist
     */
    protected $fieldlist;

    /**
     * Recuid
     * 
     * @var string $recuid
     */
    protected $recuid;

    /**
     * Tablename
     * 
     * @var string $tablename
     */
    protected $tablename;

    /**
     * Timestamp
     * 
     * @var \DateTime $tstamp
     */
    protected $tstamp;

    
    /**
     * Returns the uid
     * 
     * @return string
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * Sets the uid
     * 
     * @param string $uid
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }

    /**
     * Returns the sys_log_uid
     * 
     * @return string
     */
    public function getSysLogUid() {
        return $this->sysLogUid;
    }

    /**
     * Sets the sys_log_uid
     * 
     * @param string $sysLogUid
     */
    public function setSysLogUid($sysLogUid) {
        $this->sysLogUid = $sysLogUid;
    }

    /**
     * Returns history_data
     * 
     * @return string
     */
    public function getHistoryData() {
        return $this->historyData;
    }

    /**
     * Sets history_data
     * 
     * @param string $historyData
     */
    public function setHistoryData($historyData) {
        $this->historyData = $historyData;
    }

    /**
     * Returns fieldlist
     * 
     * @return string
     */
    public function getFieldlist() {
        return $this->fieldlist;
    }

    /**
     * Sets fieldslist
     * 
     * @param string $fieldlist
     */
    public function setFieldlist($fieldlist) {
        $this->fieldlist = $fieldlist;
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
     * @param string $recuid
     */
    public function setRecuid($recuid) {
        $this->recuid = $recuid;
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

    /**
     * Returns timestamp
     * 
     * @return \DateTime
     */
    public function getTstamp() {
        return $this->tstamp;
    }

    /**
     * Sets timestamp
     * 
     * @param \DateTime $tstamp
     */
    public function setTstamp(\DateTime $tstamp) {
        $this->tstamp = $tstamp;
    }
}