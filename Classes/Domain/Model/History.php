<?php
/**
 * History
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * History
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class History extends AbstractEntity{
    
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
    protected $sys_log_uid;
    
    /**
     * @var string 
     */
    protected $history_data;
    
    /**
     * @var string 
     */
    protected $fieldlist;
    
    /**
     * @var string 
     */
    protected $tablename;
    
    /**
     * @var \DateTime
     */
    protected $tstamp;
    
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
    public function getPid() {
        return $this->pid;
    }

    /**
     * @param string $pid
     */
    public function setPid($pid) {
        $this->pid = $pid;
    }

    /**
     * @return string
     */
    public function getSysLogUid() {
    //public function getSys_log_uid() {
        return $this->sys_log_uid;
    }

    /**
     * @param string $sys_log_uid
     */
    public function setSysLogUid($sys_log_uid) {
    //public function setSys_log_uid($sys_log_uid) {   
        $this->sys_log_uid = $sys_log_uid;
    }

    /**
     * @return string
     */
    public function getHistoryData() {
    //public function getHistory_data() {
        return $this->history_data;
    }

    /**
     * @param string $history_data
     */
    public function setHistoryData($history_data) {
    //public function setHistory_data($history_data) {
        $this->history_data = $history_data;
    }

    /**
     * @return string
     */
    public function getFieldlist() {
        return $this->fieldlist;
    }

    /**
     * @param string $fieldlist
     */
    public function setFieldlist($fieldlist) {
        $this->fieldlist = $fieldlist;
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

    /**
     * @return \DateTime
     */
    public function getTstamp() {
        return $this->tstamp;
    }

    /**
     * @param \DateTime $tstamp
     */
    public function setTstamp(\DateTime $tstamp) {
        $this->tstamp = $tstamp;
    }
}

?>
