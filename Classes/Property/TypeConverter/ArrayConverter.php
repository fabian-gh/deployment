<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Property\TypeConverter
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Property\TypeConverter;

/**
 * ArrayConverter
 * Converter which transforms strings to arrays.
 * 
 * @package    Deployment
 * @subpackage Property\TypeConverter
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class ArrayConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter {

    /**
     * Sourcetype
     * 
     * @var array<string> $sourceTypes
     */
    protected $sourceTypes = array('string');

    /**
     * Priority
     * 
     * @var integer $priority
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