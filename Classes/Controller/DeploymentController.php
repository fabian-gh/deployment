<?php

/**
 * Deployment
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Controller
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Controller;

use \TYPO3\CMS\Core\Messaging\FlashMessage;
use \TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use \TYPO3\Deployment\Domain\Model\Log;
use \TYPO3\Deployment\Domain\Model\Request\Deploy;
use \TYPO3\Deployment\Domain\Model\Request\Failure;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Deployment
 *
 * @package    Deployment
 * @subpackage Controller
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
     * @var \TYPO3\Deployment\Service\CopyService
     * @inject
     */
    protected $copyService;
    
    /**
     * @var \TYPO3\Deployment\Scheduler\UuidTask
     * @inject
     */
    protected $scheduler;

    
    /**
     * IndexAction
     */
    public function indexAction() {
        // Registry prüfen
        $this->registry->checkForRegistryEntry();

        // prüfen ob Command Controller & Benutzer registiert ist
        if(!$this->copyService->checkIfCommandControllerIsRegistered()) {
            $this->addFlashMessage('Please create a CommandController Task in the scheduler module and disable it.', 'No Extbase CommandController Task found', FlashMessage::ERROR);
        }
        if(!$this->copyService->checkIfCliUserIsRegistered()) {
            $this->addFlashMessage("Please create a CLI-User under 'Setup Check' in the scheduler module.", 'No CLI-User found', FlashMessage::ERROR);
        }
        if($this->copyService->getDisable() == '0') {
            $this->addFlashMessage('', 'Please disable the CommandController Task', FlashMessage::ERROR);
        }
        if(!$this->scheduler->checkIfTaskIsRegistered()) {
            $this->addFlashMessage('Create a task for automatic UUID assignment.', 'UUID Scheduler Task', FlashMessage::INFO);
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
     * @dontvalidate                                        $deploy
     */
    public function listAction(Deploy $deploy = NULL) {
        $newHistoryEntries = array();
        $allHistoryEintries = array();
        $historyEntries = array();

        // Registry Eintrag holen
        $date = $this->registry->getLastDeploy();

        /** @var QueryResultInterface $logEntries */
        $logEntries = $this->logRepository->findYoungerThen($date);

        if ($logEntries->getFirst() != NULL) {
            if ($deploy === NULL) {
                $deploy = new Deploy();
            }

            $unserializedLogData = $this->xmlDatabaseService->unserializeLogData($logEntries);

            // Einträge durchlaufen, falls Action == 1 dann handelt es sich um einen komplett
            // neuen Datensatz, der zu einem Historyeintrag umgewandelt wird, damit dieser
            // widerum dargestellt werden kann
            foreach ($unserializedLogData as $entry) {
                /** @var Log $entry */
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
                'deploy' => $deploy,
                'historyEntries' => $unserializedHistoryData,
                'diffData' => $diffData
            ));
        } else {
            $this->addFlashMessage('', 'No entries found', FlashMessage::ERROR);
        }
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate                                        $deploy
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

        $this->addFlashMessage('', 'Lists were created', FlashMessage::OK);
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
        $validationContent1 = $this->fileService->splitContent($resourceData, TRUE);

        // Dateien vom Quellsystem holen
        $this->copyService->trigger();

        // XML lesen
        $content = $this->xmlDatabaseService->readXML($tstamp);
        $contentSplit2 = $this->fileService->splitContent($content);
        $result2 = $this->insertDataService->insertDataIntoTable($contentSplit2);
        $validationContent2 = $this->fileService->splitContent($content, TRUE);
        
        $validationContent = array_merge($validationContent1, $validationContent2);
        
        if ($result1 === TRUE && $result2 === TRUE) {
            // letzten Deployment-Stand registrieren
            // TODO: Entkommentieren
            //$this->registry->set('deployment', 'last_deploy', time());
            // Bestätigung ausgeben
            $this->addFlashMessage('Please clear the cache now', 'Deployment was created succesfully', FlashMessage::OK);

            // Warnung falls XML nicht valide
            if (in_array(FALSE, $validationContent)) {
                $this->addFlashMessage('However, the deployment was continued', 'XML-File not valid', FlashMessage::WARNING);
            }

            // Redirect auf Hauptseite
            $this->redirect('index');
        } elseif (is_array($result1) || is_array($result2)) {
            if (!is_array($result1)) {
                $result1 = array();
            }
            if (!is_array($result2)) {
                $result2 = array();
            }

            $failures = array_merge($result1, $result2);
            
            // leere Einträge entfernen
            $fail = $this->failureService->deleteEmptyEntries($failures);
            // Einträge an Action weiterleiten
            $this->forward('listFailure', NULL, NULL, array('failures' => $fail));
        }
    }

    
    /**
     * Leert den Cache aller registrierten Seiten
     */
    public function clearCacheAction() {
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        // Datahandler initialiseren
        $dataHandler->start(NULL, NULL);
        // ALLE Caches löschen (typo3temp/Cache + Tabellen)
        $dataHandler->clear_cacheCmd('all');
        $this->redirect('index');
    }

    
    /**
     * Fehlerbehandlung
     *
     * @param array                                          $failures
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failure
     * @dontvalidate                                         $failures
     */
    public function listFailureAction($failures, Failure $failure = NULL) {
        if ($failure === NULL) {
            $failure = new Failure();
        }

        // Fehleinträge in Registry speichern
        $this->registry->storeDataInRegistry($failures, 'storedFailures');
        $entries = $this->failureService->getFailureEntries($failures);
        $failureEntries = $this->failureService->splitEntries($entries, TRUE);
        $databaseEntries = $this->failureService->splitEntries($entries);
        $diff = $this->failureService->getFailureDataDiff($databaseEntries, $failureEntries);
        $diffData = $this->failureService->convertTimestamps($diff);

        $this->addFlashMessage('A part of the data could not be inserted. Please check the given entries.', 'An error has occured!', FlashMessage::ERROR);
        $this->view->assignMultiple(array(
            'failure' => $failure,
            'failureEntries' => $failureEntries,
            'databaseEntries' => $databaseEntries,
            'diff' => $diffData
        ));
    }

    
    /**
     * Fehlerbehebung
     *
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failure
     * @dontvalidate                                         $failures
     */
    public function clearFailuresAction(Failure $failure) {
        $storedFailures = $this->registry->getStoredFailures();
        $res = $this->failureService->proceedFailureEntries($failure->getFailureEntries(), $storedFailures);
        
        if ($res) {
            // TODO: Entkomentieren
            //$this->registry->set('deployment', 'last_deploy', time());
            $this->addFlashMessage('Please clear the cache now', 'Deployment was created succesfully', FlashMessage::OK);
            $this->redirect('index');
        } else {
            $this->forward('listFailure', NULL, NULL, array('failures' => $storedFailures));
        }
    }

    
    /**
     * Add a flash message
     *
     * @param string $message
     * @param string $title
     * @param string $mode
     */
    protected function addFlashMessage($message, $title, $mode) {
        $this->controllerContext->getFlashMessageQueue()->addMessage(new FlashMessage($message, $title, $mode, true));
    }

}