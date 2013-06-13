<?php

/**
 * RegistryService
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
 *
 * @package    Deployment
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
     * Konstruktor
     */
    public function __construct() {
        $this->registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
    }

    
    /**
     * Prüft die Registry nach dem Eintrag. Falls nicht vorhanden wird dieser
     * erstellt
     */
    public function checkForRegistryEntry() {
        $deploy = $this->registry->get('deployment', 'last_deploy');

        if ($deploy == FALSE) {
            $this->registry->set('deployment', 'last_deploy', time());
        }
    }

    
    /**
     * Persistiert Einträge in der Registry
     *
     * @param mixed  $data
     * @param string $key
     */
    public function storeDataInRegistry($data, $key) {
        $storableData = serialize($data);
        $this->registry->set('deployment', $key, $storableData);
    }

    
    /**
     * Gibt den letzten Deploymentstand zurück
     *
     * @return string
     */
    public function getLastDeploy() {
        return $this->registry->get('deployment', 'last_deploy');
    }

    
    /**
     * Gibt die gespeicherten Fehler zurück
     *
     * @return array
     */
    public function getStoredFailures() {
        return $this->registry->get('deployment', 'storedFailures');
    }

    
    /**
     * Gibt die gespeicherten Historyeinträge zurück
     *
     * @return array
     */
    public function getStoredHistoryEntries() {
        return $this->registry->get('deployment', 'storedHistoryData');
    }

}