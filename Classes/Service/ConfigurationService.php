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
 * ConfigurationService
 * Class for reading the deployment configuration
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class ConfigurationService extends AbstractDataService {

    /**
     * Returns the deplyoment tables
     *
     * @return array
     */
    public function getDeploymentTables() {
        $configuration = $this->getAllEntries();
        $tables = GeneralUtility::trimExplode(',', $configuration['deploymentTables'], TRUE);

        array_push($tables, 'tt_content');
        array_push($tables, 'pages');
        array_push($tables, 'sys_file');
        array_push($tables, 'sys_file_reference');
        array_unique($tables);
        
        return $tables;
    }
    
    
    /**
     * returns coloumns which shouldn't be deplyoed
     * 
     * @return mixed array or NULL
     */
    public function getNotDeployableColumns(){
        $columns = $this->getAllEntries();
        $conArr = GeneralUtility::trimExplode(',', $columns['notDeployableColumns']);
        
        return (!empty($conArr)) ? $conArr : NULL;
    }
    
    
    /**
     * Returns the mysqldump path
     *
     * @return mixed string or NULL
     */
    public function getMysqldumpPath() {
        $configuration = $this->getAllEntries();
        return isset($configuration['mysqldumpPath']) ? $configuration['mysqldumpPath'] : NULL;
    }
    
    
    /**
     * Returns the PHP path
     *
     * @return mixed string or NULL
     */
    public function getPhpPath() {
        $configuration = $this->getAllEntries();
        return isset($configuration['phpPath']) ? $configuration['phpPath'] : NULL;
    }

    
    /**
     * Returns the delete state
     *
     * @return mixed int or NULL
     */
    public function getDeleteState() {
        $configuration = $this->getAllEntries();
        return isset($configuration['deleteOlderFiles']) ? (int) $configuration['deleteOlderFiles'] : NULL;
    }
    
    
    /**
     * Returns the address of the pull server
     *
     * @return mixed string or NULL
     */
    public function getPullserver() {
        $configuration = $this->getAllEntries();
        return isset($configuration['pullServer']) ? $configuration['pullServer'] : NULL;
    }

    
    /**
     * Returns the username
     *
     * @return mixed string or NULL
     */
    public function getUsername() {
        $configuration = $this->getAllEntries();
        return isset($configuration['username']) ? $configuration['username'] : NULL;
    }

    
    /**
     * Returns the password
     *
     * @return mixed string or NULL
     */
    public function getPassword() {
        $configuration = $this->getAllEntries();
        return isset($configuration['password']) ? $configuration['password'] : NULL;
    }

    
    /**
     * Returns all deplyoment entries
     *
     * @return array
     */
    protected function getAllEntries() {
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        return is_array($configuration) ? $configuration : array();
    }
}