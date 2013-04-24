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
 * History Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class HistoryRepository extends AbstractRepository {

    /**
     * Returned die benötigten Historydaten
     * 
     * @param array<\TYPO3\Deployment\Domain\Model\LogData> $logData
     * @return array<\TYPO3\CMS\Extbase\Persistence\QueryResultInterface>
     */
    public function findHistoryData($logData) {
        $data = array();
        
        $query = $this->createQuery();
        
        foreach($logData as $ldata){
            $constraint = $query->equals('sys_log_uid', $ldata->getUid());
            $query->matching($constraint);
            $temp = $query->execute();

            $data[] = $temp->getFirst();
        }

        return $data;
    }
}