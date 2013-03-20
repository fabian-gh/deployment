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
        DebuggerUtility::var_dump($unserializedHistoryData);die();
        
        $this->view->assign('historyEntries', $unserializedHistoryData);
    }

    /**
     * @param \TYPO3\Deployment\Domain\Model\Request\Deploy $deploy
     * @dontvalidate $deploy
     */
    public function deployAction(Deploy $deploy){ 
        $historyEntries = $this->historyRepository->findHistoryData($deploy->getLogEntries());
        DebuggerUtility::var_dump($historyEntries);die();

        $this->xmlParserService->setHistoryData($historyEntries);
        $this->xmlParserService->writeXML();
    }
    
}

?>