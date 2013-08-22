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
 * Deploy
 * Class for deployment requests which is appended to the form
 *
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class Deploy extends AbstractModel{

    /**
     * Checked deploy entries
     * 
     * @var array $deployEntries
     * @validate NotEmpty
     */
    protected $deployEntries;

    
    /**
     * Return the deploy entries
     * 
     * @return array
     */
    public function getDeployEntries() {
        if (!is_array($this->deployEntries)) {
            return array();
        }
        return $this->deployEntries;
    }

    
    /**
     * Set the deploy entries
     * 
     * @param array $deployEntries
     */
    public function setDeployEntries($deployEntries) {
        $this->deployEntries = $deployEntries;
    }

}