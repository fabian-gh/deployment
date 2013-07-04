<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model\Request;

use \TYPO3\Deployment\Domain\Model\AbstractModel;

/**
 * Failure
 * Class for failure requests
 *
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class Failure extends AbstractModel {

    /**
     * @var array
     * @validate NotEmpty
     */
    protected $failureEntries;

    
    /**
     * Return failure entries
     * 
     * @return array
     */
    public function getFailureEntries() {
        if (!is_array($this->failureEntries)) {
            return array();
        }
        return $this->failureEntries;
    }

    
    /**
     * Set failure entries
     * 
     * @param array $failureEntries
     */
    public function setFailureEntries($failureEntries) {
        $this->failureEntries = $failureEntries;
    }

}