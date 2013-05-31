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
use \TYPO3\Deployment\Domain\Model\Request\Databasefailure;
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
     * @var \TYPO3\Deployment\Service\XmlParserService
     * @inject
     */
    protected $xmlParserService;

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
     * @var \TYPO3\CMS\Core\Registry
     * @inject
     */
    protected $registry;

    /**
     * @var \TYPO3\Deployment\Service\ResourceDataService
     * @inject
     */
    protected $resource;

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
        $notIndexed = $this->resource->getNotIndexedFiles();
        $this->insertDataService->processNotIndexedFiles($notIndexed);

        // prüft ob die Spalte UUID & der Wert existieren
        $this->insertDataService->checkIfUuidExists();

        // XML-Dateien die älter als 0.5 Jahre sind löschen
        $this->xmlParserService->deleteOlderFiles();
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

            $unserializedLogData = $this->xmlParserService->unserializeLogData($logEntries);
            
            // Einträge durchlaufen, falls Action == 1 dann handelt es sich um einen komplett 
            // neuen Datensatz, der zu einem Historyeintrag umgewandelt wird, damit dieser 
            // widerum dargestellt werden kann
            foreach ($unserializedLogData as $entry) {
                if ($entry->getAction() == '1') {
                    $newHistoryEntries[] = $this->xmlParserService->convertFromLogDataToHistory($entry);
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
            $unserializedHistoryData = $this->xmlParserService->unserializeHistoryData($allHistoryEintries);
            $this->storeHistoryDataInRegistry($unserializedHistoryData);
            $diffData = $this->xmlParserService->getHistoryDataDiff($unserializedHistoryData);
            
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
            $deployData[] = $this->xmlParserService->compareDataWithRegistry($uid);
        }

        // falls deployment-Ordner noch nicht existiert, dann erstellen
        /** @var \TYPO3\CMS\Core\Resource\Driver\LocalDriver $folder */
        $folder = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Driver\\LocalDriver');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/database/');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media/');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/resource/');

        foreach ($exFold as $ergkey => $ergvalue) {
            if (!$ergvalue) {
                switch ($ergkey) {
                    case 0:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/');
                        break;

                    case 1:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/database/');
                        break;

                    case 2:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/media/');
                        break;

                    case 3:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'fileadmin/deployment/resource/');
                        break;
                }
            }
        }

        // Mediendaten erstellen
        $date = $this->registry->get('deployment', 'last_deploy', time());
        $resourceData = $this->fileRepository->findYoungerThen($date);
        $this->resource->setFileList($resourceData);
        $this->resource->writeXmlResourceList();

        // Deploydaten setzen und XML erstellen
        $this->xmlParserService->setDeployData(array_unique($deployData));
        $this->xmlParserService->writeXML();
        $this->resource->deployResources();

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
        $resourceData = $this->resource->readXmlResourceList();
        $result1 = $this->insertDataService->insertResourceDataIntoTable($resourceData);
        
        // XML lesen
        $content = $this->xmlParserService->readXML($tstamp);
        // content in DB-Felder der jeweiligen Tabelle schreiben
        $result2 = $this->insertDataService->insertDataIntoTable($content);
        
        // Prüfen ob Dateien aus resource-Ordner im fileadmnin vorhanden sind
        $this->resource->checkIfFileExists();
        
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
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failures
     * @dontvalidate $failures
     */
    public function listFailureAction($failures, Failure $failureObj = null){
        if ($failureObj === null) {
            $failureObj = new Failure();
        }

        $databaseEntries = $this->failureService->getFailureEntries($failures);
        //$diff = $this->failureService->getFailureDataDiff($failures, $databaseEntries);
        
        $this->flashMessageContainer->add('Ein Teil der Daten konnte nicht eingefügt werden. Bitte kontrollieren Sie die unteren Einträge.', 'Es sind Fehler aufgetreten!', FlashMessage::ERROR);
        $this->view->assignMultiple(array(
            'failure'           => $failureObj,
            'failureEntries'    => $failures,
            'databaseEntries'   => $databaseEntries
        ));
    }
    
    
    /**
     * Fehlerbehebung
     * 
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failures
     * @dontvalidate $failures
     */
    public function clearFailuresAction(Failure $failures){
        // TODO: Verarbeitung nachdem das Formular abgeschickt wurde
        // TODO: Ausschluss von zwei Checkboxen in einer Zeile gleichgzeitig angekreuzt
        DebuggerUtility::var_dump($failures);die();
        
        //$this->registry->set('deployment', 'last_deploy', time());
        // Bestätigung ausgeben
        //$this->flashMessageContainer->add('Bitte leeren Sie nun noch den Cache', 'Deployment wurde erfolgreich ausgeführt', FlashMessage::OK);
        // Redirect auf Hauptseite
        //$this->redirect('index');
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
     * Speichert die erfragten Historyeinträge als serialisiertes Objekt in der Registry
     * 
     * @param \TYPO3\Deployment\Domain\Model\HistoryData $hisData
     */
    protected function storeHistoryDataInRegistry($hisData) {
        $storableHisData = serialize($hisData);
        $this->registry->set('deployment', 'storedHistoryData', $storableHisData);
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