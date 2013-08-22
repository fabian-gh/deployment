<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 * 
 * @category   Extension
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Tests\Property\TypeConverter;

/**
 * ArrayConverterTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class ArrayConverterTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * ArrayConverter
     * 
     * @var \TYPO3\Deployment\Tests\Property\TypeConverter $arrayConverter
     */
    protected $arrayConverter = NULL;
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->arrayConverter = new \TYPO3\Deployment\Property\TypeConverter\ArrayConverter();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testConvertFromIsArray(){
        $this->assertInternalType('array', $this->arrayConverter->convertFrom('', 'array'));
    }
}