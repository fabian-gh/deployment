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
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function indexAction(Deploy $deploy = null) {
        if ($deploy === null) {
            $deploy = new Deploy();
        }
        $this->view->assign('deploy', $deploy);

        // das übergebene Datum wird später automatisch aus der Tabelle gelesen 
        $date = new \DateTime('last week');
        $logEntries = $this->logRepository->findYoungerThen($date);
        $unserializedLogData = $this->xmlParserService->unserializeLogData($logEntries);

        $historyEntries = $this->historyRepository->findHistoryData($unserializedLogData);
        $unserializedHistoryData = $this->xmlParserService->unserializeHistoryData($historyEntries);
        
        $this->view->assign('historyEntries', $unserializedHistoryData);
    }

    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function deployAction(Deploy $deploy) {
        $deployData = array();
        
        foreach($deploy->getDeployEntries() as $dep){
            $deployData[] = $this->historyRepository->findByUid($dep);
        }
        
        $this->xmlParserService->setDeployData($deployData);
        $this->xmlParserService->writeXML();
        
        // XML lesen
        $xmlArray = $this->xmlParserService->readXML();
    }
    
}

?>