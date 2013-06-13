<?php

/**
 * ArrayConverter
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Property\TypeConverter
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Property\TypeConverter;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * ArrayConverter
 * Converter which transforms arrays to arrays.
 * 
 * @package    Deployment
 * @subpackage Domain\Property\TypeConverter
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
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
     * Actually convert from $source to $targetType
     *
     * @param array                                                             $source
     * @param string                                                            $targetType
     * @param array                                                             $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     *
     * @throws \Exception
     * @return array
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
        if (is_string($source) && (trim($source) === '' || trim($source) === '0')) {
            return array();
        }
        throw new \Exception('No valid convert from string to array', 23467324523);
    }

}