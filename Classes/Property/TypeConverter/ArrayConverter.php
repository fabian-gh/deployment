<?php

namespace TYPO3\Deployment\Property\TypeConverter;

/* *
 * This script belongs to the Extbase framework                           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Converter which transforms arrays to arrays.
 *
 * @api
 */
class ArrayConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter {

    /**
     * @var array<string>
     */
    protected $sourceTypes = array('string');

    /**
     * @var integer
     */
    protected $priority = 100;

    /**
     * Actually convert from $source to $targetType, in fact a noop here.
     *
     * @param array                                                             $source
     * @param string                                                            $targetType
     * @param array                                                             $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     *
     * @throws \Exception
     * @return array
     * @api
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
        if(is_string($source) && (trim($source) === '' || trim($source) === '0')){
            return array();
        }
        throw new \Exception('No valid convert from string to array', 23467324523);
    }

}