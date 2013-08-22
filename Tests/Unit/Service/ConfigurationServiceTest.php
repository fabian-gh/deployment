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
 * ConfigurationServiceTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class ConfigurationServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * Configuration Service
     *
     * @var \TYPO3\Deployment\Service\ConfigurationService|NULL $configurationService
     */
    protected $configurationService = NULL;
    

    /**
     * Build up the test
     */
    public function __construct() {
        $this->configurationService = new \TYPO3\Deployment\Service\ConfigurationService();
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeploymentTablesNotNull() {
        $this->assertNotNull($this->configurationService->getDeploymentTables());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeploymentTablesIsArray() {
        $this->assertInternalType('array', $this->configurationService->getDeploymentTables());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetNotDeploymentTablesNotNull() {
        $this->assertNotNull($this->configurationService->getNotDeployableTables());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetNotDeploymentTablesIsArray() {
        $this->assertInternalType('array', $this->configurationService->getNotDeployableTables());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeploymentTablesContainsTtContent() {
        $this->assertContains('tt_content', $this->configurationService->getDeploymentTables());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeploymentTablesCotainsPages() {
        $this->assertContains('pages', $this->configurationService->getDeploymentTables());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeploymentTablesCotainsSysFile() {
        $this->assertContains('sys_file', $this->configurationService->getDeploymentTables());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeploymentTablesCotainsSysFileReference() {
        $this->assertContains('sys_file_reference', $this->configurationService->getDeploymentTables());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetNotDeployableColumnsIsArray(){
        $this->assertInternalType('array', $this->configurationService->getNotDeployableColumns());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetNotDeployableColumnsIsNotNull() {
        $this->assertNotNull($this->configurationService->getNotDeployableColumns());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeleteStateIsNotNull() {
        $this->assertNotNull($this->configurationService->getDeleteState());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDeleteStateIsInt() {
        $this->assertInternalType('int', $this->configurationService->getDeleteState());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetPullServerIsNotNull() {
        $this->assertNotNull($this->configurationService->getPullserver());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetPullServerIsString() {
        $this->assertInternalType('string', $this->configurationService->getPullserver());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetUsernameIsNotNull() {
        $this->assertNotNull($this->configurationService->getUsername());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetUsernameIsString() {
        $this->assertInternalType('string', $this->configurationService->getUsername());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetPasswordIsNotNull() {
        $this->assertNotNull($this->configurationService->getPassword());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetPasswordIsString() {
        $this->assertInternalType('string', $this->configurationService->getPassword());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetPhpPathIsNotNull() {
        $this->assertNotNull($this->configurationService->getPhpPath());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetPhpPathIsString() {
        $this->assertInternalType('string', $this->configurationService->getPhpPath());
    }
}