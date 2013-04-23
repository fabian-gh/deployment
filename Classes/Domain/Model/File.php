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
    protected $crdate;
    
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
    protected $extension;
    
    /**
     * @var string
     */
    protected $mimeType;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var string
     */
    protected $title;
    
    /**
     * @var string
     */
    protected $sha1;
    
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
    protected $uuid;
    
    
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
    public function getCrdate() {
        return $this->crdate;
    }

    /**
     * @param string $crdate
     */
    public function setCrdate($crdate) {
        $this->crdate = $crdate;
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
    public function getExtension() {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension) {
        $this->extension = $extension;
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
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
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
    public function getSha1() {
        return $this->sha1;
    }

    /**
     * @param string $sha1
     */
    public function setSha1($sha1) {
        $this->sha1 = $sha1;
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
     * @param string $creationDate
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
     * @param string $modificationDate
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
     * @param string $height
     */
    public function setHeight($height) {
        $this->height = $height;
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
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }
}