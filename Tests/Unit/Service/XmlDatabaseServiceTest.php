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
 * XmlDatabaseServiceTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class XmlDatabaseServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * XmlDatabaseService
     * 
     * @var \TYPO3\Deployment\Service\XmlDatabaseService $xmlDatabaseService
     */
    protected $xmlDatabaseService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->xmlDatabaseService = new \TYPO3\Deployment\Service\XmlDatabaseService();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadXmlIsArray(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertInternalType('array', $this->xmlDatabaseService->readXML($timestamp));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadXmlIsNotEmpty(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotEmpty($this->xmlDatabaseService->readXML($timestamp));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadXmlIsNotNull(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotNull($this->xmlDatabaseService->readXML($timestamp));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCheckLinksIsString(){
        $this->assertInternalType('string', $this->xmlDatabaseService->checkLinks('2'));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCheckLinksIsNotEmpty(){
        $this->assertNotEmpty($this->xmlDatabaseService->checkLinks('2'));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCheckLinksIsNotNull(){
        $this->assertNotNull($this->xmlDatabaseService->checkLinks('2'));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCompareDataWithRegistryIsHistoryData(){
        $this->assertInstanceOf('\TYPO3\Deployment\Domain\Model\HistoryData', $this->xmlDatabaseService->compareDataWithRegistry(2));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCompareDataWithRegistryIsNotNull(){
        $this->assertNotNull($this->xmlDatabaseService->compareDataWithRegistry(2));
    }
}