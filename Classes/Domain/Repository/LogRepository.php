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

/**
 * Log Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class LogRepository extends AbstractRepository {

    /**
     * @param \DateTime $dateTime
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

        return $query->execute();
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

?>
