<?php

/**
 * Repository
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Repository;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * File Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class FileRepository extends AbstractRepository {
    
    /**
     * Gibt alle noch nicht deployten Datensätze zurück
     * 
     * @param string $timestamp
     * @return array<\TYPO3\CMS\Extbase\Persistence\QueryResultInterface>
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
}