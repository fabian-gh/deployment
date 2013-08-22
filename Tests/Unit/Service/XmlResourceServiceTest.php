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

namespace TYPO3\Deployment\Tests\Unit\Service;

/**
 * XmlResourceServiceTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class XmlResourceServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * XmlResourceService
     * 
     * @var \TYPO3\Deployment\Service\XmlResourceService $xmlResourceService
     */
    protected $xmlResourceService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->xmlResourceService = new \TYPO3\Deployment\Service\XmlResourceService();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadXmlResourceListIsArray(){
        $this->assertInternalType('array', $this->xmlResourceService->readXmlResourceList());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadXmlResourceListIsNotEmpty(){
        $this->assertNotEmpty($this->xmlResourceService->readXmlResourceList());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadXmlResourceListIsNotNull(){
        $this->assertNotNull($this->xmlResourceService->readXmlResourceList());
    }
}