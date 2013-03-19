<?php
/**
 * HistoryData
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * HistoryData
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class HistoryData extends AbstractEntity{
    
    /**
     * @var string 
     */
    protected $uid;
    
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
    protected $recuid;

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
    public function getSysLogUid() {
        return $this->sysLogUid;
    }

    /**
     * @param string $sysLogUid
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
     * @param string $historyData
     */
    public function setHistoryData($historyData) {
        $this->historyData = $historyData;
    }
    
    /**
     * @return string
     */
    public function getRecuid() {
        return $this->recuid;
    }

    /**
     * @param string $recuid
     */
    public function setRecuid($recuid) {
        $this->recuid = $recuid;
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
