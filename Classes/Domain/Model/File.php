<?php
/**
 * File
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

/**
 * File
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class File extends AbstractModel {
    
    /**
     * @var string 
     */
    protected $uid;
    
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
    protected $type;
    
    /**
     * @var string 
     */
    protected $storage;
    
    /**
     * @var string 
     */
    protected $identifier;
    
    /**
     * @var string 
     */
    protected $mimeType;
    
    /**
     * @var string 
     */
    protected $size;
    
    /**
     * @var string 
     */
    protected $creationDate;
    
    /**
     * @var string 
     */
    protected $modificationDate;
    
    /**
     * @var string 
     */
    protected $width;
    
    /**
     * @var string 
     */
    protected $height; 
    
    
    /**
     * @return string
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }

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
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getStorage() {
        return $this->storage;
    }

    /**
     * @param string $storage
     */
    public function setStorage($storage) {
        $this->storage = $storage;
    }

    /**
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    
    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType) {
        $this->mimeType = $mimeType;
    }
    
    /**
     * @return string
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getCreationDate() {
        return $this->creationDate;
    }

    /**
     * @param string $creation_date
     */
    public function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    /**
     * @return string
     */
    public function getModificationDate() {
        return $this->modificationDate;
    }

    /**
     * @param string $modification_date
     */
    public function setModificationDate($modificationDate) {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return string
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * 
     * @param string $width
     */
    public function setWidth($width) {
        $this->width = $width;
    }

    /**
     * @return string
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * 
     * @param string $height
     */
    public function setHeight($height) {
        $this->height = $height;
    }
    
}