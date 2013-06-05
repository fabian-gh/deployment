<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabian Martinovic <fabian.martinovic(at)t-online.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
namespace TYPO3\Deployment\Property\TypeConverter;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Converter which transforms arrays to arrays.
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
     * @param array $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     *
     * @throws \Exception
     * @return array
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
        if(is_string($source) && (trim($source) === '' || trim($source) === '0')){
            return array();
        }
        throw new \Exception('No valid convert from string to array', 23467324523);
    }

}