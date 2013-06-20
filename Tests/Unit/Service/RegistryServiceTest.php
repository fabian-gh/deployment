<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class RegistryServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * RegistryService
     * 
     * @var \TYPO3\Deployment\Service\RegistryService
     */
    protected $registryService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->registryService = new \TYPO3\Deployment\Service\RegistryService();
    }
    
    /**
     * @test
     */
    public function testGetLastDeployIsString(){
        $this->assertInternalType('string', $this->registryService->getLastDeploy());
    }
    
    /**
     * @test
     */
    public function testGetStoredFailuresIsArray(){
        $this->assertInternalType('array', $this->registryService->getStoredFailures());
    }
    
    /**
     * @test
     */
    public function testGetStoredHistoryEntriesIsArray(){
        $this->assertInternalType('array', $this->registryService->getStoredHistoryEntries());
    }
}