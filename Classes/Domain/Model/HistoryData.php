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

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HistoryData
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class HistoryData extends AbstractModel {

    /**
     * @var string 
     */
    protected $uid;

    /**
     * @var string 
     */
    protected $sysLogUid;

    /**
     * @var array
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
     * @return array
     */
    public function getHistoryData() {
        return $this->historyData;
    }

    /**
     * @return string
     */
    public function getHistoryDataDiff() {
        /** @var $diff \TYPO3\CMS\Core\Utility\DiffUtility */
        $diff = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\DiffUtility');

        // todo. auf newRecord und oldRecord erstes Feld zugreifen und übergeben
        return $diff->makeDiffDisplay('heute bin ich im Kino', 'heute bin ich im Supermarkt');
    }

    /**
     * @param array $historyData
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
