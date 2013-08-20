<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Scheduler
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Scheduler;

use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use \TYPO3\Deployment\Service\InsertDataService;

/**
 * UuidTask
 * Class for executing the uuid scheduler task
 *
 * @package    Deployment
 * @subpackage Scheduler
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class UuidTask extends AbstractTask{
    
    /**
     * Executes the Scheduler Task
     * 
     * @return boolean
     */
    public function execute() {
        /** @var \TYPO3\Deployment\Service\InsertDataService $insertDataService */
        $insertDataService = new InsertDataService();
        $insertDataService->checkIfUuidExists();
        return true;
    }
    
    
    /**
     * Prüft ob ein Task registriert wurde
     * 
     * @return boolean
     */
    public function checkIfTaskIsRegistered(){
        $res = $this->getDatabase()->exec_SELECTgetRows('serialized_task_object', 'tx_scheduler_task', '1=1');
        
        foreach($res as $result){
            $object = unserialize($result['serialized_task_object']);
            if(is_a($object, 'TYPO3\Deployment\Scheduler\UuidTask')){
                $obj = true;
            }
        }
        
        return ($obj) ? true : false;
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