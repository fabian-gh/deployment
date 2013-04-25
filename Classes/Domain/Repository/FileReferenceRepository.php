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
 * FileReference Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class FileReferenceRepository extends AbstractRepository {

    /**
     * @param string $uid
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByUidForeign($uid) {
        $constraints = array();
        $query = $this->createQuery();

        $constraints[] = $query->equals('uid_foreign', $uid);
        $constraints[] = $query->equals('table_local', 'sys_file');

        $query->matching($query->logicalAnd($constraints));

        return $query->execute();
    }

}