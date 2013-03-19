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
    protected $sysLogUid;
    
    /**
     * @var string 
     */
    protected $historyData;
    
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
    public function getSysLogUid() {
        return $this->sysLogUid;
    }

    /**
     * @param string $sys_log_uid
     */
    public function setSysLogUid($sysLogUid) { 
        $this->sysLogUid = $sysLogUid;
    }

    /**
     * @return string
     */
    public function getHistoryData() {
        return $this->historyData;
    }

    /**
     * @param string $history_data
     */
    public function setHistoryData($historyData) {
        $this->historyData = $historyData;
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
