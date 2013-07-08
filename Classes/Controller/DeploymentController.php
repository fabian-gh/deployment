<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
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
 * DeploymentController
 * Controller class for the deployment extension
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
     * IndexAction to start the extension
     */
    public function indexAction() {
        // check registry for last deploy date, if it not exists than write it
        $this->registry->checkForRegistryEntry();

        // check if command controller task, cli_user, and a scheduler task are registered
        // also check if the command controller task is disabled
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

        // index not indexed files
        $notIndexed = $this->fileService->getNotIndexedFiles();
        $this->fileService->processNotIndexedFiles($notIndexed);

        // check if the coloumn uuid and a value exists
        $this->insertDataService->checkIfUuidExists();

        // delete xml-files older than a half year
        $this->fileService->deleteOlderFiles();
    }

    
    /**
     * Lists all changed and new entries to create a xml-file
     * 
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate                                        $deploy
     */
    public function listAction(Deploy $deploy = NULL) {
        $newHistoryEntries = array();
        $allHistoryEintries = array();
        $historyEntries = array();

        // get registry entry
        $date = $this->registry->getLastDeploy();

        /** @var QueryResultInterface $logEntries */
        $logEntries = $this->logRepository->findYoungerThen($date);

        if ($logEntries->getFirst() != NULL) {
            if ($deploy === NULL) {
                $deploy = new Deploy();
            }

            $unserializedLogData = $this->xmlDatabaseService->unserializeLogData($logEntries);

            // traverse entries, if action == 1 than we have a complete new entry which 
            // have to be converted to a history entry to get displayed properly
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
            
            // further data treatment
            $allHistoryEintries = array_merge($newHistoryEntries, $historyEntries);
            $unserializedHistoryData = $this->xmlDatabaseService->unserializeHistoryData($allHistoryEintries);
            $this->registry->storeDataInRegistry($unserializedHistoryData, 'storedHistoryData');
            $diffData = $this->xmlDatabaseService->getHistoryDataDiff($unserializedHistoryData);

            // view assigning
            $this->view->assignMultiple(array(
                'deploy' => $deploy,
                'historyEntries' => $unserializedHistoryData,
                'diffData' => $diffData
            ));
        } else {
            // if no entries were found, display an error message
            $this->addFlashMessage('', 'No entries found', FlashMessage::ERROR);
        }
    }

    
    /**
     * Creates a deployment by creating new xml lists
     * 
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate                                        $deploy
     */
    public function createDeployAction(Deploy $deploy) {
        $deployData = array();

        foreach ($deploy->getDeployEntries() as $uid) {
            $deployData[] = $this->xmlDatabaseService->compareDataWithRegistry($uid);
        }

        // if there is noch deployment directory create one
        $this->fileService->createDirectories();

        // create resource data
        $date = $this->registry->getLastDeploy();
        $resourceData = $this->fileRepository->findYoungerThen($date);
        $this->xmlResourceService->setFileList($resourceData);
        $this->xmlResourceService->writeXmlResourceList();

        // create database data
        $this->xmlDatabaseService->setDeployData(array_unique($deployData));
        $this->xmlDatabaseService->writeXML();

        $this->addFlashMessage('', 'Lists were created', FlashMessage::OK);
        $this->redirect('index');
    }

    
    /**
     * DeployAction to execute a whole deployment
     */
    public function deployAction() {
        $result1 = array();
        $result2 = array();
        // read last deployment date
        $tstamp = $this->registry->getLastDeploy();

        // read resource data
        $resourceData = $this->xmlResourceService->readXmlResourceList();
        // split all read data, because the read data as well as the validation 
        // results are in the same array
        $contentSplit1 = $this->fileService->splitContent($resourceData);
        $result1 = $this->insertDataService->insertResourceDataIntoTable($contentSplit1);
        $validationContent1 = $this->fileService->splitContent($resourceData, TRUE);

        // get data from source system
        $this->copyService->trigger();

        // read xml
        $content = $this->xmlDatabaseService->readXML($tstamp);
        $contentSplit2 = $this->fileService->splitContent($content);
        $result2 = $this->insertDataService->insertDataIntoTable($contentSplit2);
        $validationContent2 = $this->fileService->splitContent($content, TRUE);
        
        $validationContent = array_merge($validationContent1, $validationContent2);
        
        // if there are no errors
        if ($result1 === TRUE && $result2 === TRUE) {
            // register last deployment status
            // TODO: Entkommentieren
            //$this->registry->setLastDeploy();
            // display ok-message
            $this->addFlashMessage('Please clear the cache now', 'Deployment was created succesfully', FlashMessage::OK);

            // warning if xml not valid
            if (in_array(FALSE, $validationContent)) {
                $this->addFlashMessage('However, the deployment was continued', 'XML-File not valid', FlashMessage::WARNING);
            }

            // Redirect to index
            $this->redirect('index');
        } elseif (is_array($result1) || is_array($result2)) {
            if (!is_array($result1)) {
                $result1 = array();
            }
            if (!is_array($result2)) {
                $result2 = array();
            }

            $failures = array_merge($result1, $result2);
            
            // delete empty entries
            $fail = $this->failureService->deleteEmptyEntries($failures);
            // forward the results to the action
            $this->forward('listFailure', NULL, NULL, array('failures' => $fail));
        }
    }

    
    /**
     * Clear the cache
     */
    public function clearCacheAction() {
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        // Initialize datahandler
        $dataHandler->start(NULL, NULL);
        // delete ALL caches (typo3temp/cache + tables)
        $dataHandler->clear_cacheCmd('all');
        $this->redirect('index');
    }

    
    /**
     * Error listing action for displaying the errors
     *
     * @param array                                          $failures
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failure
     * @dontvalidate                                         $failures
     */
    public function listFailureAction($failures, Failure $failure = NULL) {
        if ($failure === NULL) {
            $failure = new Failure();
        }

        // persist the error entries in the registry
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
     * Error treatment to clear the failures
     *
     * @param \TYPO3\Deployment\Domain\Model\Request\Failure $failure
     * @dontvalidate                                         $failures
     */
    public function clearFailuresAction(Failure $failure) {
        $storedFailures = $this->registry->getStoredFailures();
        $res = $this->failureService->proceedFailureEntries($failure->getFailureEntries(), $storedFailures);
        
        if ($res) {
            // TODO: Entkomentieren
            //$this->registry->setLastDeploy();
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