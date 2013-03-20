<?php
/**
 * LogData
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * LogData
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class LogData extends AbstractEntity{
    
    /**
     * @var string
     */
    protected $uid;

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
