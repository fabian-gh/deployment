<?php

/**
 * ConfigurationService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ConfigurationService
 *
 * @package    Deployment
 * @subpackage Domain\Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class ConfigurationService extends AbstractDataService {

    /**
     * Gibt die Depolymenttabellen zurück
     *
     * @return array
     */
    public function getDeploymentTables() {
        $configuration = $this->getAllEntries();
        $tables = GeneralUtility::trimExplode(',', $configuration['deploymentTables'], TRUE);
        // HDNET
        array_push($tables, 'tt_content');
        array_push($tables, 'pages');
        array_unique($tables);
        return $tables;
    }

    
    /**
     * Gibt den aktuellen Löschungsstatus zurück
     *
     * @return mixed int or NULL
     */
    public function getDeleteState() {
        $configuration = $this->getAllEntries();
        return isset($configuration['deleteOlderFiles']) ? (int) $configuration['deleteOlderFiles'] : NULL;
    }

    
    /**
     * Gibt die Adresse des PullServers zurück
     *
     * @return mixed string or NULL
     */
    public function getPullserver() {
        $configuration = $this->getAllEntries();
        return isset($configuration['pullServer']) ? $configuration['pullServer'] : NULL;
    }

    
    /**
     * Gibt den Benutzernamen zurück
     *
     * @return mixed string or NULL
     */
    public function getUsername() {
        $configuration = $this->getAllEntries();
        return isset($configuration['username']) ? $configuration['username'] : NULL;
    }

    
    /**
     * Gibt das Passwort zurück
     *
     * @return mixed string or NULL
     */
    public function getPassword() {
        $configuration = $this->getAllEntries();
        return isset($configuration['password']) ? $configuration['password'] : NULL;
    }

    
    /**
     * Gibt alle Deploymenteinträge zurück
     *
     * @return array
     */
    protected function getAllEntries() {
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        return is_array($configuration) ? $configuration : array();
    }

}