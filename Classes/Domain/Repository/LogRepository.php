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

/**
 * Log Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class LogRepository extends Repository {
    /* =======================================
     * Repository dient als Schnittstelle zur 
     * Datenabfrage bzw. zur Datensicherung 
     * des Models
     * =======================================
     */

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    public function createQuery() {
        // aus der Repository Klasse erben
        $query = parent::createQuery();
        $query
                ->getQuerySettings()
                ->setRespectStoragePage(FALSE);
        return $query;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findYoungerThen(\DateTime $dateTime) {
        $query = $this->createQuery();

        $constraints = array();
        $constraints[] = $query->greaterThanOrEqual('tstamp', $dateTime);
        $constraints[] = $query->equals('error', '0');
        $constraints[] = $query->greaterThan('action', '0');
        $constraints[] = $query->logicalNot($query->equals('tablename', ''));
        
        $query->matching($query->logicalAnd($constraints));
        //DebuggerUtility::var_dump($query->matching($query->logicalAnd($constraints)));die();
        return $query->execute();
    }

    /**
     * Beispiel für individuelle Abfrage. Bitte in find* umbennnen und korrigieren
     */
    public function customQuery() {
        /** @var $query \TYPO3\CMS\Extbase\Persistence\Generic\Query */
        $query = $this->createQuery();

        $query->statement('SELECT sys_log.* FROM sys_log');

        return $query->execute();
    }

}

?>
