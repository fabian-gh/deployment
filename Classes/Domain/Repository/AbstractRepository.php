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

use \TYPO3\CMS\Extbase\Persistence\Repository;
use \TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Abstract Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
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