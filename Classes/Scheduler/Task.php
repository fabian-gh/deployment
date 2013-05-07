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

class Task extends AbstractTask{
    
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
        $this->resourceDataService->deployResources(true);
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
        $res = $con->exec_SELECTgetRows('uid, serialized_task_object', 'tx_scheduler_task');
        
        foreach($res as $result){
            $object = unserialize($result['serialized_task_object']);
            
            if(is_a($object, '\TYPO3\Deployment\Scheduler\Task')){
                return true;
            } else {
                return false;
            }
        }
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