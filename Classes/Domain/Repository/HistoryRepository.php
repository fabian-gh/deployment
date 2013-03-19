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

//use \TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * History Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class HistoryRepository extends AbstractRepository {

    /**
     * @param array $uids
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findHistoryData($logData) {
        $query = $this->createQuery();
        
        foreach($logData as $ldata){
            $constraint = $query->equals('recuid', $ldata['recID']);
            $query->matching($constraint);
            $data[] = $query->execute()->toArray();
        }
        
        return $data;
    }

}

?>
