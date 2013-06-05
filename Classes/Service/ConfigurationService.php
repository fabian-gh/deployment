<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabian Martinovic <fabian.martinovic(at)t-online.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * ConfigurationService
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
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class ConfigurationService extends AbstractDataService {
    
    /**
     * Überprüft ob tt_content & pages im Array vorhanden und gibt dieses zurück
     * 
     * @return array
     */
    public function checkTableEntries(){
        $tables = $this->getDeploymentTables();
        array_unique($tables);
        
        if(!in_array('tt_content', $tables)){
            array_push($tables, 'tt_content');
        }
        if(!in_array('pages', $tables)){
            array_push($tables, 'pages');
        }
        
        $allEntries = $this->getAllEntries();
        $allEntries['deploymentTables'] = implode(',', $tables);
        $this->setAllEntries($allEntries);
    }
    
    
    /**
     * Filtert alle Einträge heraus, die aus Tabellen kommen, die nicht deployed 
     * werden sollen
     * 
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $result
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
     */
    public function filterEntries($result){
        $count = 0;
        $tables = $this->getDeploymentTables();
        
        foreach($result as $res){
            if(!in_array($res->getTablename(), $tables)){
                unset($result[$count]);
            }
            $count++;
        }
        
        return $result;
    }

    
    /**
     * Gibt alle Deploymenteinträge zurück
     * 
     * @return array
     */
    protected function getAllEntries(){
        return unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
    }
    
    /**
     * @param array $deploymentTables
     */
    protected function setAllEntries($allEntries){
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment'] = $allEntries;
        $serEntries = serialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment'] = $serEntries;
    }
    
    /**
     * Gibt die Depolymenttabellen zurück
     * 
     * @return array
     */
    public function getDeploymentTables(){
        $configuration = $this->getAllEntries();
        return GeneralUtility::trimExplode(',', $configuration['deploymentTables'], TRUE);
    }
    
    /**
     * Gibt den aktuellen Löschungsstatus zurück
     * 
     * @return int
     */
    public function getDeleteState(){
        $configuration = $this->getAllEntries();
        return $configuration['deleteOlderFiles'];
    }
    
    
    /**
     * Gibt die Adresse des PullServers zurück
     * 
     * @return string
     */
    public function getPullserver(){
        $configuration = $this->getAllEntries();
        return $configuration['pullServer'];
    }
    
    
    /**
     * Gibt den Benutzernamen zurück
     * 
     * @return string
     */
    public function getUsername(){
        $configuration = $this->getAllEntries();
        return $configuration['username'];
    }
    
    
    /**
     * Gibt das Passwort zurück
     * 
     * @return string
     */
    public function getPassword(){
        $configuration = $this->getAllEntries();
        return $configuration['password'];
    }
    
    
    /**
     * Gibt die maximale Dateigröße zurück
     * 
     * @return int
     */
    public function getMaxFileSize(){
        $configuration = $this->getAllEntries();
        return $configuration['maxFileSize'];
    }
}