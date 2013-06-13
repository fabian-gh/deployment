<?php

/**
 * Deploy
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
 *
 * Das Objekt dieser Klasse wird dem Formular mitgegeben. 
 * In dieses Objekt werden die angehakten Daten geschrieben.
 *
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class Deploy extends AbstractModel{

    /**
     * @var array
     * @validate NotEmpty
     */
    protected $deployEntries;

    
    /**
     * @return array
     */
    public function getDeployEntries() {
        if (!is_array($this->deployEntries)) {
            return array();
        }
        return $this->deployEntries;
    }

    
    /**
     * @param array $deployEntries
     */
    public function setDeployEntries($deployEntries) {
        $this->deployEntries = $deployEntries;
    }

}