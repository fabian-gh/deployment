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
     * @var \TYPO3\Deployment\Domain\Repository\FileReferenceRepository
     * @inject
     */
    protected $fileReferenceRepository;

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
     * @var \TYPO3\CMS\Core\Registry
     * @inject
     */
    protected $registry;
    
    /**
     * @var \TYPO3\Deployment\Service\MediaDataService
     * @inject
     */
    protected $media;

    
    /**
     * IndexAction
     */
    public function indexAction() {
        $this->registry = GeneralUtility::makeInstance('t3lib_Registry');
        
        $this->media->readXmlMediaList();
        
        // Noch nicht indizierte Dateien indizieren
        $notIndexed = $this->media->getNotIndexedFiles();
        $this->insertDataService->processNotIndexedFiles($notIndexed);
        
        // XML-Dateien die älter als 0.5 Jahre sind löschen
        $this->xmlParserService->deleteOlderFiles();
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function listAction(Deploy $deploy = NULL) {
        // Wenn Eintrag nicht vorhanden, wird die aktuelle Zeit genommen, 
        // neuer Eintrag wird aber dennoch nicht erstellt
        $date = $this->registry->get('deployment', 'last_deploy', time());

        $logEntries = $this->logRepository->findYoungerThen($date);

        if ($logEntries->getFirst() != NULL) {
            if ($deploy === NULL) {
                $deploy = new Deploy();
            }
            $this->view->assign('deploy', $deploy);

            $unserializedLogData = $this->xmlParserService->unserializeLogData($logEntries);
            $historyEntries = $this->historyRepository->findHistoryData($unserializedLogData);
            $unserializedHistoryData = $this->xmlParserService->unserializeHistoryData($historyEntries);
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
        $deployData = $exFold = array();

        foreach ($deploy->getDeployEntries() as $dep) {
            $deployData[] = $this->historyRepository->findByUid($dep);
        }

        // falls deployment-Ordner noch nicht existiert, dann erstellen
        /** @var \TYPO3\CMS\Core\Resource\Driver\LocalDriver $folder */
        $folder = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Driver\\LocalDriver');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/database/');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/media/');
        $exFold[] = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/resource/');
        
        foreach($exFold as $ergkey => $ergvalue){
            if(!$ergvalue){
                switch($ergkey){
                    case 0:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/');
                    break;
                
                    case 1:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/database/');
                    break;
                
                    case 2:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/media/');
                    break;
                
                    case 3:
                        GeneralUtility::mkdir(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/resource/');
                    break;
                }
            }
        }
        
        // Mediendaten erstellen
        $date = $this->registry->get('deployment', 'last_deploy', time());
        $mediaData = $this->fileRepository->findYoungerThen($date);
        $this->media->setFileList($mediaData);
        $this->media->writeXmlMediaList();

        // Deploydaten setzen und XML erstellen
        $this->xmlParserService->setDeployData(array_unique($deployData));
        $this->xmlParserService->writeXML();
        $this->media->deployResources();

        $this->flashMessageContainer->add('Daten wurden erstellt.', '', FlashMessage::OK);
        $this->redirect('index');
    }

    
    /**
     * DeployAction
     */
    public function deployAction() {
        // letztes Deployment-Datum lesen
        $tstamp = $this->registry->get('deployment', 'last_deploy');
        
        //Mediendaten lesen
        $mediaData = $this->media->readXmlMediaList();
        $this->insertDataService->insertMediaDataIntoTable($mediaData);
        
        // XML lesen
        $content = $this->xmlParserService->readXML($tstamp);

        // content in DB-Felder der jeweiligen Tabelle schreiben
        $result = $this->insertDataService->insertDataIntoTable($content);
        
        // Prüfen ob Dateien aus resource-Ordner im fileadmnin vorhanden sind
        $this->media->checkIfFileExists();

        // letzten Deployment-Stand registrieren
        $this->registry->set('deployment', 'last_deploy', time());

        if($result){
            // Bestätigung ausgeben
            $this->flashMessageContainer->add('Deployment wurde erfolgreich ausgeführt', '', FlashMessage::OK);
            // Redirect auf Hauptseite
            $this->redirect('index');
        } else {
            $this->flashMessageContainer->add('Es ist ein Fehler aufgetreten', 'Dei Daten konnten nicht eingefügt werden. Bitte kontrollieren Sie das Deployment', FlashMessage::ERROR);
            $this->redirect('index');
        }
    }
    
}