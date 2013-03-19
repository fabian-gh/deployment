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

use \TYPO3\Deployment\Domain\Model\History;

/**
 * History Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class HistoryRepository extends AbstractRepository {

    /**
     * @param array|\TYPO3\Domain\Model\LogData $logData
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findHistoryData($logData) {
        $query = $this->createQuery();

        foreach($logData as $ldata){
            $constraint = $query->equals('recuid', $ldata->getRecid());
            $query->matching($constraint);
            $temp = $query->execute();
            
            $data[] = $temp->getFirst();
        }

        return $data;
    }

}

?>
