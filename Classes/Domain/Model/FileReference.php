<?php
/**
 * FileReference
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

/**
 * FileReference
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class FileReference extends AbstractModel {
    
    /**
     * @var string 
     */
    protected $pid;
    
    /**
     * @var string 
     */
    protected $tstamp;
    
    /**
     * @var string 
     */
    protected $uidLocal;
    
    /**
     * @var string 
     */
    protected $uidForeign;
    
    /**
     * @var string 
     */
    protected $tablenames;
    
    /**
     * @var string 
     */
    protected $fieldname;
    
    /**
     * @var string 
     */
    protected $title;
    
    /**
     * @var string 
     */
    protected $description;
    
    /**
     * @var string 
     */
    protected $alternative;
    
    /**
     * @var string 
     */
    protected $link;


    /**
     * @return string
     */
    public function getPid() {
        return $this->pid;
    }

    /**
     * @param string $pid
     */
    public function setPid($pid) {
        $this->pid = $pid;
    }

    /**
     * @return string
     */
    public function getTstamp() {
        return $this->tstamp;
    }

    /**
     * @param string $tstamp
     */
    public function setTstamp($tstamp) {
        $this->tstamp = $tstamp;
    }

    /**
     * @return string
     */
    public function getUidLocal() {
        return $this->uidLocal;
    }

    /**
     * @param string $uidLocal
     */
    public function setUidLocal($uidLocal) {
        $this->uidLocal = $uidLocal;
    }

    /**
     * @return string
     */
    public function getUidForeign() {
        return $this->uidForeign;
    }

    /**
     * @param string $uidForeign
     */
    public function setUidForeign($uidForeign) {
        $this->uidForeign = $uidForeign;
    }

    /**
     * @return string
     */
    public function getTablenames() {
        return $this->tablenames;
    }

    /**
     * @param string $tablenames
     */
    public function setTablenames($tablenames) {
        $this->tablenames = $tablenames;
    }

    /**
     * @return string
     */
    public function getFieldname() {
        return $this->fieldname;
    }

    /**
     * @param string $fieldname
     */
    public function setFieldname($fieldname) {
        $this->fieldname = $fieldname;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getAlternative() {
        return $this->alternative;
    }

    /**
     * @param string $alternative
     */
    public function setAlternative($alternative) {
        $this->alternative = $alternative;
    }

    /**
     * @return string
     */
    public function getLink() {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link) {
        $this->link = $link;
    }   
}