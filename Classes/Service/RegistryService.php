<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * RegistryService
 * Class for registry services
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class RegistryService extends AbstractDataService {

    /**
     * Registry
     *
     * @var \TYPO3\CMS\Core\Registry
     */
    protected $registry = NULL;

    /**
     * Constructor
     */
    public function __construct() {
        $this->registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
    }

    
    /**
     * Check the regsitry for the last deployment date. If it not exists, create it
     */
    public function checkForRegistryEntry() {
        $deploy = $this->registry->get('deployment', 'last_deploy');

        if ($deploy == FALSE) {
            $this->registry->set('deployment', 'last_deploy', time());
        }
    }

    
    /**
     * Persist the entries in the registry
     *
     * @param mixed  $data
     * @param string $key
     */
    public function storeDataInRegistry($data, $key) {
        $storableData = serialize($data);
        $this->registry->set('deployment', $key, $storableData);
    }

    
    /**
     * Returns the last deployment date
     *
     * @return string
     */
    public function getLastDeploy() {
        return $this->registry->get('deployment', 'last_deploy');
    }
    
    
    /**
     * Sets a new timestamp
     */
    public function setLastDeploy(){
        $this->registry->set('deployment', 'last_deploy', time());
    }

    
    /**
     * Returns the persisted failures
     *
     * @return array
     */
    public function getStoredFailures() {
        return unserialize($this->registry->get('deployment', 'storedFailures'));
    }

    
    /**
     * Returns the persisted history entries
     *
     * @return array
     */
    public function getStoredHistoryEntries() {
        return unserialize($this->registry->get('deployment', 'storedHistoryData'));
    }
}