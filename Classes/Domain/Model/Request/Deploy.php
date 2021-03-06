<?php
/**
 * Deploy
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model\Request;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Deploy
 *
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class Deploy extends AbstractEntity {
    /* ============================================
     * Das Objekt dieser Klasse wird dem Formular
     * mitgegeben. In diesen Objekt werden dann die
     * angekreuzten Daten geschrieben
     * ============================================
     */
    
    /**
     * @var array
     * @validate NotEmpty
     */
    protected $deployEntries;

    /**
     * @return array
     */
    public function getDeployEntries() {
        if(!is_array($this->deployEntries)){
            return array();
        }
        return $this->deployEntries;
    }

    /**
     * @param array $logEntries
     */
    public function setDeployEntries($deployEntries) {
        $this->deployEntries = $deployEntries;
    }

}