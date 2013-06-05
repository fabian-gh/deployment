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

use \TYPO3\CMS\Extbase\Persistence\Repository;
use \TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Abstract Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class AbstractRepository extends Repository {

    /**
     * Überschreiben der createQuery()-Methode
     * 
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    public function createQuery() {
        // aus der Repository Klasse erben
        $query = parent::createQuery();
        $query->getQuerySettings()->setRespectStoragePage(FALSE);

        return $query;
    }

    
    /**
     * Debug a Query object
     *
     * @param QueryInterface $query
     * @param bool           $plain
     *
     * @return array
     */
    public function debugQuery(QueryInterface $query, $plain = FALSE) {
        $parameters = array();
        /** @var $backend \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend */
        $backend = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Storage\\Typo3DbBackend');
        $statementParts = $backend->parseQuery($query, $parameters);
        if(!$plain) {
            return $statementParts;
        }
        return $backend->buildQuery($statementParts, $parameters);
    }
    
}