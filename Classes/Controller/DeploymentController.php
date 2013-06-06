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
 * Deployment
 *
 * @category   Extension
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Controller;

use \TYPO3\CMS\Core\Messaging\FlashMessage;
use \TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\Deployment\Domain\Model\Request\Deploy;
use \TYPO3\Deployment\Domain\Model\Request\Failure;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Deployment
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class DeploymentController extends ActionController {

    /**
     * @var \TYPO3\Deployment\Domain\Repository\LogRepository
     * @inject
     */
    protected $logRepository;

    /**
     * @var \TYPO3\Deployment\Domain\Repository\HistoryRepository
     * @inject
     */
    protected $historyRepository;

    /**
     * @var \TYPO3\Deployment\Domain\Repository\FileRepository
     * @inject
     */
    protected $fileRepository;

    /**
     * @var \TYPO3\Deployment\Service\XmlDatabaseService
     * @inject
     */
    protected $xmlDatabaseService;

    /**
     * @var \TYPO3\Deployment\Service\InsertDataService
     * @inject
     */
    protected $insertDataService;
    
    /**
     * @var \TYPO3\Deployment\Service\FailureService
     * @inject
     */
    protected $failureService;
    
    /**
     * @var \TYPO3\Deployment\Service\FileService
     * @inject 
     */
    protected $fileService;

    /**
     * @var \TYPO3\Deployment\Service\RegistryService
     * @inject
     */
    protected $registry;

    /**
     * @var \TYPO3\Deployment\Service\XmlResourceService
     * @inject
     */
    protected $xmlResourceService;

    /**
     * @var \TYPO3\Deployment\Scheduler\CopyTask
     * @inject
     */
    protected $schedulerTask;

    
    /**
     * IndexAction
     */
    public function indexAction() {
        // Registry prüfen
        $this->registry->checkForRegistryEntry();

        // prüfen ob Scheduler Task registiert ist
        $reg = $this->schedulerTask->checkIfTaskIsRegistered();
        if ($reg === FALSE) {
            $this->flashMessageContainer->add('Bitte erstellen Sie einen Scheduler Task', 'Scheduler Task fehlt.', FlashMessage::ERROR);
        }

        // Noch nicht indizierte Dateien indizieren
        $notIndexed = $this->fileService->getNotIndexedFiles();
        $this->fileService->processNotIndexedFiles($notIndexed);
        
        // prüft ob die Spalte UUID & der Wert existieren
        $this->insertDataService->checkIfUuidExists();

        // XML-Dateien die älter als 0.5 Jahre sind löschen
        $this->fileService->deleteOlderFiles();
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function listAction(Deploy $deploy = NULL) {
        $newHistoryEntries = array();
        $allHistoryEintries = array();
        $historyEntries = array();

        // Registry Eintrag holen
        $date = $this->registry->getLastDeploy();

        $logEntries = $this->logRepository->findYoungerThen($date);
        
        if ($logEntries->getFirst() != NULL) {
            if ($deploy === NULL) {
                $deploy = new Deploy();
            }
            $this->view->assign('deploy', $deploy);

            $unserializedLogData = $this->xmlDatabaseService->unserializeLogData($logEntries);
            
            // Einträge durchlaufen, falls Action == 1 dann handelt es sich um einen komplett 
            // neuen Datensatz, der zu einem Historyeintrag umgewandelt wird, damit dieser 
            // widerum dargestellt werden kann
            foreach ($unserializedLogData as $entry) {
                if ($entry->getAction() == '1') {
                    $newHistoryEntries[] = $this->xmlDatabaseService->convertFromLogDataToHistory($entry);
                } else {
                    /** @var \TYPO3\Deployment\Domain\Model\History $result */
                    $result = $this->historyRepository->findHistoryData($entry);
                    
                    if ($result !== NULL) {
                        $result->setTstamp($result->getTstamp());
                        $historyEntries[] = $result;
                    }
                }
            }
            
            $allHistoryEintries = array_merge($newHistoryEntries, $historyEntries);
            $unserializedHistoryData = $this->xmlDatabaseService->unserializeHistoryData($allHistoryEintries);
            $this->registry->storeDataInRegistry($unserializedHistoryData, 'storedHistoryData');
            $diffData = $this->xmlDatabaseService->getHistoryDataDiff($unserializedHistoryData);
            
            $this->view->assignMultiple(array(
                'historyEntries'    => $unserializedHistoryData,
                'diffData'          => $diffData
            ));
        } else {
            $this->flashMessageContainer->add('Keine Einträge gefunden', '', FlashMessage::ERROR);
        }
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function createDeployAction(Deploy $deploy) {
        $deployData = array();

        foreach ($deploy->getDeployEntries() as $uid) {
            $deployData[] = $this->xmlDatabaseService->compareDataWithRegistry($uid);
        }
        
        // falls deployment-Ordner noch nicht existieren, dann erstellen
        $this->fileService->createDirectories();

        // Mediendaten erstellen
        $date = $this->registry->getLastDeploy();
        $resourceData = $this->fileRepository->findYoungerThen($date);
        $this->xmlResourceService->setFileList($resourceData);
        $this->xmlResourceService->writeXmlResourceList();

        // Deploydaten setzen und XML erstellen
        $this->xmlDatabaseService->setDeployData(array_unique($deployData));
        $this->xmlDatabaseService->writeXML();

        $this->flashMessageContainer->add('Daten wurden erstellt.', '', FlashMessage::OK);
        $this->redirect('index');
    }

    
    /**
     * DeployAction
     */
    public function deployAction() {
        $result1 = array();
        $result2 = array();
        // letztes Deployment-Datum lesen
        $tstamp = $this->registry->getLastDeploy();
        
        //Mediendaten lesen
        $resourceData = $this->xmlResourceService->readXmlResourceList();
        // Gelesene Daten splitten, da sowohl die Resultate als auch die Ergebnisse
        // der Validierung in einem Array stehen
        $contentSplit1 = $this->fileService->splitContent($resourceData);
        $result1 = $this->insertDataService->insertResourceDataIntoTable($contentSplit1);
        $validationContent1 = $this->fileService->splitContent($resourceData, true);
        
        // Dateien vom Quellsystem holen
        $reg = $this->schedulerTask->checkIfTaskIsRegistered();
        if($reg){
            $this->schedulerTask->execute();
        }
        
        // XML lesen
        $content = $this->xmlDatabaseService->readXML($tstamp);
        $contentSplit2 = $this->fileService->splitContent($content);
        $result2 = $this->insertDataService->insertDataIntoTable($contentSplit2);
        $validationContent2 = $this->fileService->splitContent($content, true);
        
        $validationContent = array_merge($validationContent1, $validationContent2);
        
        // Prüfen ob Dateien aus resource-Ordner im fileadmnin vorhanden sind
        $this->fileService->checkIfFileExists();
        
        if($result1 === true && $result2 === true){
            // letzten Deployment-Stand registrieren
            //$this->registry->set('deployment', 'last_deploy', time());
            
            // Bestätigung ausgeben
            $this->flashMessageContainer->add('Bitte leeren Sie nun noch den Cache', 'Deployment wurde erfolgreich ausgeführt', FlashMessage::OK);
            
            // Warnung falls XML nicht valide
            if(in_array(false, $validationContent)){
                $this->flashMessageContainer->add('Das Deployment wurde dennoch fortgesetzt', 'XML-Datei nicht valide', FlashMessage::WARNING);
            }
            
            // Redirect auf Hauptseite
            $this->redirect('index');
        } 
        elseif(is_array($result1) || is_array($result2)) {
            if(!is_array($result1)){
                $result1 = array();
            }
            if(!is_array($result2)){
                $result2 = array();
            }
            
            $failures = array_merge($result1, $result2);
            
            // leere Einträge entfernen
            $fail = $this->failureService->deleteEmptyEntries($failures);
            
            $this->forward('listFailure', null, null, array('failures' => $fail));
        }
    }
    
    
    /**
     * Leert den Cache aller registrierten Seiten
     */
    public function clearPageCacheAction() {
        /** @var TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        // Datahandler initialiseren
        $dataHandler->start();
        // ALLE Caches löschen (typo3temp/Cache + Tabellen)
        $dataHandler->clear_cacheCmd('all');
        $this->redirect('index');
    }
    
    
    /**
     * Fehlerbehandlung
     * 
     * @param array $failures
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failure
     * @dontvalidate $failures
     */
    public function listFailureAction($failures, Failure $failure = null){
        if ($failure === null) {
            $failure = new Failure();
        }
       
        // Fehleinträge in Registry speichern
        $this->registry->storeDataInRegistry($failures, 'storedFailures');
        $databaseEntries = $this->failureService->getFailureEntries($failures);
        $diff = $this->failureService->getFailureDataDiff($databaseEntries, $failures);
       
        $this->flashMessageContainer->add('Ein Teil der Daten konnte nicht eingefügt werden. Bitte kontrollieren Sie die unteren Einträge.', 'Es sind Fehler aufgetreten!', FlashMessage::ERROR);
        $this->view->assignMultiple(array(
            'failure'           => $failure,
            'failureEntries'    => $failures,
            'databaseEntries'   => $databaseEntries,
            'diff'              => $diff
        ));
    }
    
    
    /**
     * Fehlerbehebung
     * 
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failure
     * @dontvalidate $failures
     */
    public function clearFailuresAction(Failure $failure){
        $storedFailures = $this->registry->getStoredFailures();
        $res = $this->failureService->proceedFailureEntries($failure->getFailureEntries(), $storedFailures);
        
        if($res){
            //$this->registry->set('deployment', 'last_deploy', time());
            $this->flashMessageContainer->add('Bitte leeren Sie nun noch den Cache', 'Deployment wurde erfolgreich ausgeführt', FlashMessage::OK);
            $this->redirect('index');
        } else {
            $this->forward('listFailure', null, null, array('failures' => $storedFailures));
        }
    }
    
    
    /**
     * Überschreiben der Fehlermeldung "An error occurred while trying to call ..."
     * 
     * @return TYPO3\CMS\Core\Messaging\FlashMessage
     */
    protected function getErrorFlashMessage() {
        if ($this->actionMethodName == 'createDeployAction') {
            return false;
        }
    }

}