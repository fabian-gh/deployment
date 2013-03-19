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
    protected $tstamp;
    
    /**
     * @var string
     */
    protected $logData;
    
    /**
     * @return string
     */
    public function getLogData() {
        return $this->logData;
    }
    
    /**
     * @param string $logData
     */
    public function setLogData($logData){
        $this->logData = $logData;
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
