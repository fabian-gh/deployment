<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

/**
 * File
 * Class for file models
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
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