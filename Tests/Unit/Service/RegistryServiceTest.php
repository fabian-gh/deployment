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
 * RegistryServiceTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class RegistryServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * RegistryService
     * 
     * @var \TYPO3\Deployment\Service\RegistryService $registryService
     */
    protected $registryService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->registryService = new \TYPO3\Deployment\Service\RegistryService();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetLastDeployIsString(){
        $this->assertInternalType('string', $this->registryService->getLastDeploy());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetStoredFailuresIsArray(){
        $this->assertInternalType('array', $this->registryService->getStoredFailures());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetStoredHistoryEntriesIsArray(){
        $this->assertInternalType('array', $this->registryService->getStoredHistoryEntries());
    }
}