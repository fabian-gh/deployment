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

use \TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Abstract Model
 *
 * @package    Deployment
 * @subpackage Domain\Model
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class AbstractModel extends AbstractEntity {

    /**
     * Overwrite getCleanProperties-method from AbstractEntity,
     * because warnings may could be displayed
     * 
     * @return array
     */
    public function _getCleanProperties() {
        $properties = parent::_getCleanProperties();
        return is_array($properties) ? $properties : array();
    }
}