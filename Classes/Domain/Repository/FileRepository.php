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

/**
 * File Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class FileRepository extends AbstractRepository {
    
    /**
     * Gibt alle noch nicht deployten Datensätze zurück
     * 
     * @param string $timestamp
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findYoungerThen($timestamp){
        $constraints = array();
        $query = $this->createQuery();
        
        $constraints[] = $query->greaterThanOrEqual('tstamp', $timestamp);
        $constraints[] = $query->logicalNot($query->like('identifier', '/deployment%'));
        $constraints[] = $query->equals('deleted', '0');
        
        $query->matching($query->logicalAnd($constraints));
        
        return $query->execute();
    }
    
    
    /**
     * Gibt alle Datensätze zurück, die zum $identifier passen
     * 
     * @param string $identifier
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByIdentifier($identifier){
        $constraints = array();
        $query = $this->createQuery();
        
        $constraints[] = $query->like('identifier', '/'.$identifier.'%');
        $constraints[] = $query->equals('deleted', '0');
        
        $query->matching($query->logicalAnd($constraints));
        
        return $query->execute();
    }
    
    
    /**
     * Gibt alle Datensätze zurück, die zum $identifier passen, allerdings
     * ohen führendes Slash
     * 
     * @param string $identifier
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByIdentifierWithoutHeadingSlash($identifier){
        $constraints = array();
        $query = $this->createQuery();
        
        $constraints[] = $query->like('identifier', '%'.$identifier.'%');
        $constraints[] = $query->equals('deleted', '0');
        
        $query->matching($query->logicalAnd($constraints));
        
        return $query->execute();
    }
}