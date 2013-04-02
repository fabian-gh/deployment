<?php

/**
 * Abstract Model
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage ...
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Abstract Model
 *
 * @package    Deployment
 * @subpackage ...
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */
class AbstractModel extends AbstractEntity {

    /**
     * getCleanProperties-Methide aus der AbstractEntitty überschreiben,
     * da unter Umständen Warnings auftreten können.
     * 
     * @return array
     */
    public function _getCleanProperties() {
        $properties = parent::_getCleanProperties();
        return is_array($properties) ? $properties : array();
    }

}
