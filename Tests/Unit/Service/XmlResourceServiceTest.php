<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class XmlResourceServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * XmlResourceService
     * 
     * @var \TYPO3\Deployment\Service\XmlResourceService
     */
    protected $xmlResourceService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->xmlResourceService = new \TYPO3\Deployment\Service\XmlResourceService();
    }
    
    /**
     * @test
     */
    public function testReadXmlResourceListIsArray(){
        $this->assertInternalType('array', $this->xmlResourceService->readXmlResourceList());
    }
    
    /**
     * @test
     */
    public function testReadXmlResourceListIsNotEmpty(){
        $this->assertNotEmpty($this->xmlResourceService->readXmlResourceList());
    }
    
    /**
     * @test
     */
    public function testReadXmlResourceListIsNotNull(){
        $this->assertNotNull($this->xmlResourceService->readXmlResourceList());
    }
}