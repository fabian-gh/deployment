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
 * Repository
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Repository;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Service\ConfigurationService;
/**
 * Log Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class LogRepository extends AbstractRepository {

    /**
     * Gibt alle noch nicht deployeten Datensätze zurück
     * 
     * @param string $timestamp
     * @return array<\TYPO3\Deployment\Domain\Model\Log>
     */
    public function findYoungerThen($timestamp) {
        $query = $this->createQuery();
        
        $configuration = new ConfigurationService();
        $configuration->checkTableEntries();
        
        $constraints = array();
        $constraints[] = $query->greaterThanOrEqual('tstamp', $timestamp);
        $constraints[] = $query->equals('error', '0');
        $constraints[] = $query->greaterThan('action', '0');
        $constraints[] = $query->logicalNot($query->equals('tablename', ''));
        
        $query->matching($query->logicalAnd($constraints));
        $result = $query->execute();
        
        // Filterung von Datensätzen die in Tabellen stehen, die auch in der 
        // Konfiguration aufgelistet sind
        return $configuration->filterEntries($result);
    }

    /**
     * Beispiel für individuelle Abfrage. Bitte in find* umbennnen und korrigieren
     */
    /*public function customQuery() {
        /** @var $query \TYPO3\CMS\Extbase\Persistence\Generic\Query */
        /*$query = $this->createQuery();

        $query->statement('SELECT sys_log.* FROM sys_log');

        return $query->execute();
    }*/

}