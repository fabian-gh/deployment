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
 * CopyService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\Deployment\Service\FileService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\Deployment\Service\ConfigurationService;
use TYPO3\Deployment\Service\XmlResourceService;

/**
 * CopyService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class CopyService extends AbstractDataService{
    
    /**
     * @var string
     */
    protected $taskUid;
    
    
    
    /**
     * Triggerfunktion zum Aufruf des Command Controllers über das CLI
     */
    public function trigger(){
        if($this->allPrecautionsSet()){
            $path = $this->getCliPath();
            $taskUid = $this->getTaskUid();
            exec(escapeshellcmd("$path scheduler -force -i $taskUid"));
        }
    }
    
    
    /**
     * Führt das Kopieren aus
     */
    public function execute(){
        if($this->allPrecautionsSet()){
            $this->deployResources();
        }
    }
    
    /**
     * Prüft ob der Command Controller registiert ist
     * 
     * @return boolean
     */
    public function checkIfCommandControllerIsRegistered(){
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $result = $con->exec_SELECTgetRows('serialized_task_object', 'tx_scheduler_task');
        
        foreach($result as $res){
            /** @var \TYPO3\CMS\Extbase\Scheduler\Task $object */
            $object = unserialize($res['serialized_task_object']);
            $this->taskUid = $object->getTaskUid();
            $identParts = explode(':', $object->getCommandIdentifier());
            
            if($identParts[0] == 'deployment' && $identParts[1] == 'copyresources' && $identParts[2] == 'copy'){
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * Prüft ob der benötigte _cli_scheduler-User vorhanden ist
     * 
     * @return boolean
     */
    public function checkIfCliUserIsRegistered(){
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $result = $con->exec_SELECTgetRows('username', 'be_users', "username='_cli_scheduler'");
        
        if(!empty($result)){
            foreach($result as $res){
                if($res['username'] == '_cli_scheduler'){
                    return true;
                }
            }
        } else {
            return false;
        }
    }
    
    
    /**
     * Dateien aus der sys_file-Tabelle über die XML-Datei einlesen und diese
     * mittels des Command Controller Tasks vom Quellsystem kopieren
     */
    protected function deployResources(){
        /** @var \TYPO3\Deployment\Service\FileService $fileService */
        $fileService = new FileService();
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resFact */
        $resFact = ResourceFactory::getInstance();
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configuration */
        $configuration = new ConfigurationService();
        /** @var \TYPO3\Deployment\Service\XmlResourceService $xmlResourceService */
        $xmlResourceService = new XmlResourceService();
        
        $path = $fileService->getDeploymentResourcePathWithoutTrailingSlash();
        // Daten aus Konfiguration holen
        $server = $configuration->getPullserver();
        $username = $configuration->getUsername();
        $password = $configuration->getPassword();
        
        // URL in Teile zerlegen
        $parts = parse_url($server);
        // Username & Password trimmen falls nicht leer
        if(trim($username) != ''){
            $parts['user'] = $username;
        }
        if(trim($password) != ''){
            $parts['pass'] = $password;
        }
        // Pfad mit User und PW wieder zusammensetzen
        $pullServer = trim(HttpUtility::buildUrl($parts), '/');
        
        // Betriebssystem auslesen
        $os = get_browser()->platform;

        // XML einlesen
        $data = $fileService->splitContent($xmlResourceService->readXmlResourceList());

        foreach ($data as $resource) {
            /** @var \TYPO3\CMS\Core\Resource\File $file */
            $file = $resFact->getFileObject($xmlResourceService->getUid($resource['uuid'], $resource['tablename']));
            $split = explode('/', $file->getIdentifier());
            $filename = array_pop($split);

            // Pfad wieder zusammensetzen
            $folder = '';
            foreach ($split as $sp) {
                if ($sp != '' && $sp != 'fileadmin') {
                    $folder = $folder . '/' . $sp;
                }
            }

            // erste Slash entfernen und Ordnerstruktur erstellen
            $fold = substr($folder, 1);
            if (!is_dir($path . '/' . $fold)) {
                GeneralUtility::mkdir_deep($path . '/' . $fold);
            }

            // Dateien mittels OS-Unterscheidung vom Quellsystem kopieren oder syncen
            if (strpos($os, 'Linux') !== FALSE || strpos($os, 'Mac') !== FALSE) {
                $sourceDest = escapeshellcmd("$pullServer/fileadmin/$fold/$filename $path/$fold/$filename");
                // Parameter: Dateien bei Übertragung komprimieren, neuere Dateien nicht ersetzen,
                // SymLinks als Syminks kopieren, Dateirechte beibehalten, Quellverzeichnis
                exec("rsync --compress --update --links --perms $sourceDest");
            } else {
                copy($pullServer.'/fileadmin/'.$fold.'/'.$filename, $path.'/'.$fold.'/'.$filename);
            }
        }
    }
    
    
    /**
     * Prüft ob alle Vorkehrungen getroffen sind
     * 
     * @return boolean
     */
    public function allPrecautionsSet(){
        if($this->checkIfCommandControllerIsRegistered() && $this->checkIfCliUserIsRegistered()){
            return true;
        }
    }
    
    
    /**
     * Get the TYPO3 database
     * 
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
    
    
    /**
     * @return string
     */
    public function getCliPath(){
        return GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'typo3/cli_dispatch.phpsh';
    }
    
    /**
     * @return string
     */
    public function getTaskUid() {
        return $this->taskUid;
    }
}