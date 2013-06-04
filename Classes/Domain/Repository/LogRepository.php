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
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\Deployment\Service\ConfigurationService;
/**
 * Log Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
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