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
    public function findHistoryData($uids) {
        $query = $this->createQuery();
        
        foreach($uids as $uid){
            $constraint = $query->equals('sys_log_uid', $uid);
            $query->matching($constraint);
            $data[] = $query->execute()->toArray();
        }
        
        return $data;
    }

}

?>
