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

/**
 * History
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class History extends AbstractModel{
    
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
     * @todo: ist das nicht ein Array?
     *
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
