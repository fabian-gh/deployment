<?php

/**
 * Deployment
 *
 * Namepspace [VENDORNAME][ExtensionKey][ClassPath]
 *
 * @category   Extension
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
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
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
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
     * @var \TYPO3\CMS\Core\Registry
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
        // Registry-Objekt erstellen und prüfen
        /** @var \TYPO3\CMS\Core\Registry $registry */
        $this->registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
        $this->checkForRegistryEntry();

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
        $date = $this->registry->get('deployment', 'last_deploy');

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
            $this->storeDataInRegistry($unserializedHistoryData, 'storedHistoryData');
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
        $exFold = array();

        foreach ($deploy->getDeployEntries() as $uid) {
            $deployData[] = $this->xmlDatabaseService->compareDataWithRegistry($uid);
        }
        
        // falls deployment-Ordner noch nicht existieren, dann erstellen
        /** @var \TYPO3\CMS\Core\Resource\Driver\LocalDriver $folder */
        $folder = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Driver\\LocalDriver');
        $exFold[] = $folder->folderExists($this->fileService->getDeploymentPathWithTarilingSlash());
        $exFold[] = $folder->folderExists($this->fileService->getDeploymentDatabasePathWithTarilingSlash());
        $exFold[] = $folder->folderExists($this->fileService->getDeploymentMediaPathWithTarilingSlash());
        $exFold[] = $folder->folderExists($this->fileService->getDeploymentResourcePathWithTarilingSlash());

        foreach ($exFold as $ergkey => $ergvalue) {
            if (!$ergvalue) {
                switch ($ergkey) {
                    case 0:
                        GeneralUtility::mkdir($ergvalue);
                    break;

                    case 1:
                        GeneralUtility::mkdir($ergvalue);
                    break;

                    case 2:
                        GeneralUtility::mkdir($ergvalue);
                    break;

                    case 3:
                        GeneralUtility::mkdir($ergvalue);
                    break;
                }
            }
        }

        // Mediendaten erstellen
        $date = $this->registry->get('deployment', 'last_deploy', time());
        $resourceData = $this->fileRepository->findYoungerThen($date);
        $this->xmlResourceService->setFileList($resourceData);
        $this->xmlResourceService->writeXmlResourceList();

        // Deploydaten setzen und XML erstellen
        $this->xmlDatabaseService->setDeployData(array_unique($deployData));
        $this->xmlDatabaseService->writeXML();
        $this->xmlResourceService->deployResources();

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
        $tstamp = $this->registry->get('deployment', 'last_deploy');
        
        //Mediendaten lesen
        $resourceData = $this->xmlResourceService->readXmlResourceList();
        $result1 = $this->insertDataService->insertResourceDataIntoTable($resourceData);
        
        // XML lesen
        $content = $this->xmlDatabaseService->readXML($tstamp);
        // content in DB-Felder der jeweiligen Tabelle schreiben
        $result2 = $this->insertDataService->insertDataIntoTable($content);
        
        // Prüfen ob Dateien aus resource-Ordner im fileadmnin vorhanden sind
        $this->fileService->checkIfFileExists();
        
        if ($result1 === true && $result2 === true) {
            // letzten Deployment-Stand registrieren
            //$this->registry->set('deployment', 'last_deploy', time());
            
            // Bestätigung ausgeben
            $this->flashMessageContainer->add('Bitte leeren Sie nun noch den Cache', 'Deployment wurde erfolgreich ausgeführt', FlashMessage::OK);
            
            // Redirect auf Hauptseite
            $this->redirect('index');
        } elseif(is_array ($result1) && is_array ($result2)) {
            $failures = array_merge($result1, $result2);
            
            // leere Einträge entfernen
            foreach($failures as $fail){
                if($fail === null){
                    unset($fail);
                } else {
                    $fail2[] = $fail;
                }
            }
            
            $this->forward('listFailure', null, null, array('failures' => $fail2));
        }
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
        $this->storeDataInRegistry($failures, 'storedFailures');
        $databaseEntries = $this->failureService->getFailureEntries($failures);
        
        $this->flashMessageContainer->add('Ein Teil der Daten konnte nicht eingefügt werden. Bitte kontrollieren Sie die unteren Einträge.', 'Es sind Fehler aufgetreten!', FlashMessage::ERROR);
        $this->view->assignMultiple(array(
            'failure'           => $failure,
            'failureEntries'    => $failures,
            'databaseEntries'   => $databaseEntries
        ));
    }
    
    
    /**
     * Fehlerbehebung
     * 
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failure
     * @dontvalidate $failures
     */
    public function clearFailuresAction(Failure $failure){
        $storedFailures = $this->registry->get('deployment', 'storedFailures');
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
     * Prüft die Registry nach dem Eintrag. Falls nicht vorhanden wird dieser
     * erstellt
     */
    protected function checkForRegistryEntry() {
        $deploy = $this->registry->get('deployment', 'last_deploy');

        if ($deploy == FALSE) {
            $this->registry->set('deployment', 'last_deploy', time());
        }
    }

    
    /**
     * Persistiert Einträge in der Registry
     * 
     * @param mixed $data
     * @param string $key
     */
    protected function storeDataInRegistry($data, $key) {
        $storableData = serialize($data);
        $this->registry->set('deployment', $key, $storableData);
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