<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class XmlDatabaseServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * XmlDatabaseService
     * 
     * @var \TYPO3\Deployment\Service\XmlDatabaseService
     */
    protected $xmlDatabaseService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->xmlDatabaseService = new \TYPO3\Deployment\Service\XmlDatabaseService();
    }
    
    /**
     * @test
     */
    public function testReadXmlIsArray(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertInternalType('array', $this->xmlDatabaseService->readXML($timestamp));
    }
    
    /**
     * @test
     */
    public function testReadXmlIsNotEmpty(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotEmpty($this->xmlDatabaseService->readXML($timestamp));
    }
    
    /**
     * @test
     */
    public function testReadXmlIsNotNull(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotNull($this->xmlDatabaseService->readXML($timestamp));
    }
    
    /**
     * @test
     */
    public function testCheckLinksIsString(){
        $this->assertInternalType('string', $this->xmlDatabaseService->checkLinks('2'));
    }
    
    /**
     * @test
     */
    public function testCheckLinksIsNotEmpty(){
        $this->assertNotEmpty($this->xmlDatabaseService->checkLinks('2'));
    }
    
    /**
     * @test
     */
    public function testCheckLinksIsNotNull(){
        $this->assertNotNull($this->xmlDatabaseService->checkLinks('2'));
    }
    
    /**
     * @test
     */
    public function testCompareDataWithRegistryIsHistoryData(){
        $this->assertInstanceOf('\TYPO3\Deployment\Domain\Model\HistoryData', $this->xmlDatabaseService->compareDataWithRegistry(2));
    }
    
    /**
     * @test
     */
    public function testCompareDataWithRegistryIsNotNull(){
        $this->assertNotNull($this->xmlDatabaseService->compareDataWithRegistry(2));
    }
}