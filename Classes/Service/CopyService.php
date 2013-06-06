<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabian Martinovic <fabian.martinovic(at)t-online.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * CopyService
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CopyService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class CopyService extends AbstractDataService{
    
    /**
     * Prüft ob der Command Controller registiert ist
     * 
     * @return boolean
     */
    public function checkIfCommandControllerIsRegistered(){
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $con */
        $con = $this->getDatabase();
        $result = $con->exec_SELECTgetRows('serialized_task_object', 'tx_scheduler_task');
        
        foreach($result as $res){
            $object = unserialize($res['serialized_task_object']);
            $identifierParts = explode(':', $object->getCommandIdentifier());
            
            if($identifierParts[0] == 'deployment' && $identifierParts[1] == 'copyresources' && $identifierParts[2] == 'copy'){
                DebuggerUtility::var_dump($object);
                DebuggerUtility::var_dump($object->getTaskUid());
                DebuggerUtility::var_dump($identifierParts);
            }
        }
        die();
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

?>
