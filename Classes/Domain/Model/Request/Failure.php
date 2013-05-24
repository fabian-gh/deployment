<?php
/**
 * Failure
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model\Request;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Failure
 *
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class Failure extends AbstractEntity {
    /**
     * @var array
     * @validate NotEmpty
     */
    protected $failureEntries;

    /**
     * @return array
     */
    public function getFailureEntries() {
        if(!is_array($this->failureEntries)){
            return array();
        }
        return $this->failureEntries;
    }

    /**
     * @param array $logEntries
     */
    public function setFailureEntries($failureEntries) {
        $this->failureEntries = $failureEntries;
    }

}