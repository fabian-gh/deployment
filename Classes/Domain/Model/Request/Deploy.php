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
 * Deploy
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model\Request;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Deploy
 *
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class Deploy extends AbstractEntity {
    /* ============================================
     * Das Objekt dieser Klasse wird dem Formular
     * mitgegeben. In diesen Objekt werden dann die
     * angekreuzten Daten geschrieben
     * ============================================
     */
    
    /**
     * @var array
     * @validate NotEmpty
     */
    protected $deployEntries;

    /**
     * @return array
     */
    public function getDeployEntries() {
        if(!is_array($this->deployEntries)){
            return array();
        }
        return $this->deployEntries;
    }

    /**
     * @param array $logEntries
     */
    public function setDeployEntries($deployEntries) {
        $this->deployEntries = $deployEntries;
    }

}