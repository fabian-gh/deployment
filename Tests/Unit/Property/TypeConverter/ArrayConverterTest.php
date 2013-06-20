<?php

namespace TYPO3\Deployment\Tests\Property\TypeConverter;

class ArrayConverterTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * ArrayConverter
     * 
     * @var \TYPO3\Deployment\Tests\Property\TypeConverter
     */
    protected $arrayConverter = NULL;
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->arrayConverter = new \TYPO3\Deployment\Property\TypeConverter\ArrayConverter();
    }
    
    /**
     * @test
     */
    public function testConvertFromIsArray(){
        $this->assertInternalType('array', $this->arrayConverter->convertFrom('', 'array'));
    }
}