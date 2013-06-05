<?php

/**
 * Task
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Scheduler;

use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CopyTask extends AbstractTask{
    
    /**
     * @var \TYPO3\Deployment\Service\ResourceDataService
     */
    private $resourceDataService;
    
    
    /**
     * Executes the Scheduler Task
     * 
     * @return boolean
     */
    public function execute() {
        $this->resourceDataService = GeneralUtility::makeInstance('TYPO3\\Deployment\\Service\\ResourceDataService');
        $this->resourceDataService->deployResources();
        return true;
    }
    
    
    /**
     * Prüft ob der Scheduler Task registiert ist
     * 
     * @return boolean
     */
    public function checkIfTaskIsRegistered(){
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $res = $con->exec_SELECTgetRows('serialized_task_object', 'tx_scheduler_task');
        
        foreach($res as $result){
            $object = unserialize($result['serialized_task_object']);
            if(is_a($object, 'TYPO3\Deployment\Scheduler\CopyTask')){
                $obj = true;
            }
        }
        
        return ($obj === true) ? true : false;
    }
    
    
    /**
     * Get the TYPO3 database
     * 
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase() {
        return $GLOBALS['TYPO3_DB'];
    }
}