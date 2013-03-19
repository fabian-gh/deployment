<?php
/**
 * LogData
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * LogData
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class LogData extends AbstractEntity{
    
    /**
     * @var string
     */
    protected $data;
    
    /**
     * @var string
     */
    protected $table;
    
    /**
     * @var string
     */
    protected $recId;
    
    /**
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table) {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getRecId() {
        return $this->recId;
    }

    /**
     * @param string $recId
     */
    public function setRecid($recId) {
        $this->recId = $recId;
    }
}

?>
