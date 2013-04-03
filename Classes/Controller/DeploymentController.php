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

    public function indexAction() {
        $this->registry = GeneralUtility::makeInstance('t3lib_Registry');
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function listAction(Deploy $deploy = NULL) {
        // Wenn Eintrag nicht vorhanden, wird die aktuelle zeit genommen, neuer Eintrag wird aber dennoch nicht erstellt
        $last_deploy = date('Y-m-d', $this->registry->get('deployment', 'last_deploy', time()));

        $date = new \DateTime($last_deploy);
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
        $deployData = array();

        foreach ($deploy->getDeployEntries() as $dep) {
            $deployData[] = $this->historyRepository->findByUid($dep);
        }

        // falls deployment-Ordner noch nicht existiert, dann erstellen
        $folder = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Driver\\LocalDriver');
        $erg = $folder->folderExists(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/');
        if(!$erg){
            $folder = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'fileadmin/deployment/';
            GeneralUtility::mkdir($folder);
        }

        // Deploydaten setzen und XML erstellen
        $this->xmlParserService->setDeployData(array_unique($deployData));
        $this->xmlParserService->writeXML();

        $this->flashMessageContainer->add('Daten wurden erstellt.', '', FlashMessage::OK);
        $this->redirect('index');
    }

    
    /**
     * DeployAction
     */
    public function deployAction() {
        // letztes Deployment-Datum lesen
        $tstamp = $this->registry->get('deployment', 'last_deploy');
        // XML lesen
        $content = $this->xmlParserService->readXML($tstamp);

        // content in DB-Felder der jeweiligen Tabelle schreiben
        $this->insertDataService->insertDataIntoTable($content);

        // TODO: evtl. ID Konflikte anzeigen

        // letzten Deployment-Stand registrieren
        // $this->registry->set('deployment', 'last_deploy', time());

        // Bestätigung ausgeben
        $this->flashMessageContainer->add('Deployment wurde erfolgreich ausgeführt', '', FlashMessage::OK);
        // Redirect auf Hauptseite
        $this->redirect('index');
    }

}

?>