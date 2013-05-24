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
     * @return string
     */
    public function getUid() {
        return $this->uid;
    }
}