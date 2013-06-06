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
 * RegistryService
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
 * RegistryService
 *
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class RegistryService extends AbstractDataService{
    
    /**
     * Prüft die Registry nach dem Eintrag. Falls nicht vorhanden wird dieser
     * erstellt
     */
    public function checkForRegistryEntry() {
        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
        
        $deploy = $registry->get('deployment', 'last_deploy');

        if ($deploy == FALSE) {
            $registry->set('deployment', 'last_deploy', time());
        }
    }
    
    
    /**
     * Persistiert Einträge in der Registry
     * 
     * @param mixed $data
     * @param string $key
     */
    public function storeDataInRegistry($data, $key) {
        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
        
        $storableData = serialize($data);
        $registry->set('deployment', $key, $storableData);
    }
}

?>
