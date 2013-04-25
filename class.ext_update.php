<?php

namespace \TYPO3\Deployment;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ext_update for checking if registry value exsits
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class ext_update {
    
    /**
     * Executes the registry update if necessary
     */
    public function main(){
        $this->registry = GeneralUtility::makeInstance('t3lib_Registry');
        $deploy = $this->registry->get('deployment', 'last_deploy');
        
        if($deploy == false){
            $this->registry->set('deployment', 'last_deploy', time());
            $this->access(true);
        } else {
           $this->access(true); 
        }
    }
    
    
    /**
     * Returns the current registry state
     * 
     * @param boolena $value
     * @return boolean
     */
    public function access($value){
        return $value;
    }
}