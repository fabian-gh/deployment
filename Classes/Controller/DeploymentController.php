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

use \TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\Deployment\Domain\Model\Request\Deploy;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Extbase\Mvc\Controller\FlashMessageContainer;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Registry;

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
     * @var \TYPO3\CMS\Core\Registry
     * @inject
     */
    protected $registry;
 
    
    public function indexAction(){
        $this->registry = GeneralUtility::makeInstance('t3lib_Registry');
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function listAction(Deploy $deploy = null) {
        $last_deploy = date('Y-m-d', $this->registry->get('deployment', 'last_deploy'));
        
        $date = new \DateTime($last_deploy);
        $logEntries = $this->logRepository->findYoungerThen($date);
        
        if($logEntries->getFirst() != null){
            if ($deploy === null) {
                $deploy = new Deploy();
            }
            $this->view->assign('deploy', $deploy);
            
            $unserializedLogData = $this->xmlParserService->unserializeLogData($logEntries);
            $historyEntries = $this->historyRepository->findHistoryData($unserializedLogData);
            $unserializedHistoryData = $this->xmlParserService->unserializeHistoryData($historyEntries);
            
            $this->view->assign('historyEntries', $unserializedHistoryData);
        } else {
            FlashMessageContainer::add('Keine Einträge gefunden', '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
        }
    }

    
    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function createDeployAction(Deploy $deploy) {
        $deployData = array();
        
        foreach($deploy->getDeployEntries() as $dep){
            $deployData[] = $this->historyRepository->findByUid($dep);
        }
        
        $this->xmlParserService->setDeployData($deployData);
        $this->xmlParserService->writeXML();
        
        $this->redirect('index');
        
        FlashMessageContainer::add('Daten wurden erstellt', '', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
    }
    
    
    /**
     * DeployAction
     */
    public function deployAction(){
        // letztes Deployment-Datum lesen
        $tstamp = $this->registry->get('deployment', 'last_deploy');
        // XML lesen
        $content = $this->xmlParserService->readXML($tstamp);
        
        // TODO: content in DB-Felder schreiben
        
        // TODO: evtl. ID Konflikte anzeigen
        
        // letzten Deployment-Stand registrieren
        //$this->registry->set('deployment', 'last_deploy', time());
        
        $this->redirect('index');
        
        FlashMessageContainer::add('Deployment wurde erfolgreich ausgeführt', '', \TYPO3\CMS\Core\Messaging\FlashMessage::OK);
    }
    
}

?>