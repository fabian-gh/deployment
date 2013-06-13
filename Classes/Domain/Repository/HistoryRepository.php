<?php

/**
 * Repository
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Repository;

/**
 * History Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class HistoryRepository extends AbstractRepository {

    /**
     * Returned die benötigten Historydaten
     * 
     * @param \TYPO3\Deployment\Domain\Model\LogData $logData
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findHistoryData($logData) {
        $query = $this->createQuery();
        
        $constraint = $query->equals('sys_log_uid', $logData->getUid());
        $query->matching($constraint);
        $data = $query->execute();
        
        return $data->getFirst();
    }
}