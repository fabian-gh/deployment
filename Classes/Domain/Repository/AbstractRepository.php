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
 * Abstract Repository
 *
 * @package    Deployment
 * @subpackage Domain\Repository
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class AbstractRepository extends Repository{
    /* =======================================
     * Repository dient als Schnittstelle zur 
     * Datenabfrage bzw. zur Datensicherung 
     * des Models
     * =======================================
     */
  
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
}

?>
