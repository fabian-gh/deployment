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

/**
 * Log
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class Log extends AbstractModel{
    
    /**
     * @var string
     */
    protected $tstamp;
    
    /**
     * @var array
     */
    protected $logData;
    
    /**
     * @return array
     */
    public function getLogData() {
        return $this->logData;
    }
    
    /**
     * @param array $logData
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
