<?php
/**
 * Deploy
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model\Request;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Deploy
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class Deploy extends AbstractEntity {
    
    /* =======================================
     * Das Objekt dieser Klasse wird erstellt
     * nachdem der Senden-Button im Formular
     * abgeschickt wurde
     * =======================================
     */
    
    /**
     * @var array
     * @validate NotEmpty
     */
    protected $logEntries;

    /**
     * @return array
     */
    public function getLogEntries() {
        if(!is_array($this->logEntries)){
            return array();
        }
        return $this->logEntries;
    }

    /**
     * @param array $logEntries
     */
    public function setLogEntries($logEntries) {
        $this->logEntries = $logEntries;
    }
    
}
?>