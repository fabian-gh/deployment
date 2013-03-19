<?php
/**
 * Log
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Log
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class Log extends AbstractEntity{
    
    /**
     * @var string
     */
    protected $action;
    
    /**
     * @var string
     */
    protected $tablename;
    
    /**
     * @var string
     */
    protected $details;
    
    /**
     * @var \DateTime
     */
    protected $tstamp;
    

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
    
    /**
     * @return string
     */
    public function getDetails() {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails($details) {
        $this->details = $details;
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
