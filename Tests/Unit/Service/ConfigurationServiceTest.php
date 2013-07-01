<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class ConfigurationServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * Configuration Service
     *
     * @var \TYPO3\Deployment\Service\ConfigurationService|NULL
     */
    protected $configurationService = NULL;

    /**
     * Build up the test
     */
    public function __construct() {
        $this->configurationService = new \TYPO3\Deployment\Service\ConfigurationService();
    }

    /**
     * @test
     */
    public function testGetDeploymentTablesNotNull() {
        $this->assertNotNull($this->configurationService->getDeploymentTables());
    }
    
    /**
     * @test
     */
    public function testGetDeploymentTablesIsArray() {
        $this->assertInternalType('array', $this->configurationService->getDeploymentTables());
    }

    /**
     * @test
     */
    public function testGetDeploymentTablesContainsTtContent() {
        $this->assertContains('tt_content', $this->configurationService->getDeploymentTables());
    }

    /**
     * @test
     */
    public function testGetDeploymentTablesCotainsPages() {
        $this->assertContains('pages', $this->configurationService->getDeploymentTables());
    }

    /**
     * @test
     */
    public function testGetDeploymentTablesCotainsSysFile() {
        $this->assertContains('sys_file', $this->configurationService->getDeploymentTables());
    }

    /**
     * @test
     */
    public function testGetDeploymentTablesCotainsSysFileReference() {
        $this->assertContains('sys_file_reference', $this->configurationService->getDeploymentTables());
    }
    
    /**
     * @test
     */
    public function testGetNotDeployableColumnsIsArray(){
        $this->assertInternalType('array', $this->configurationService->getNotDeployableColumns());
    }

    /**
     * @test
     */
    public function testGetNotDeployableColumnsIsNotNull() {
        $this->assertNotNull($this->configurationService->getNotDeployableColumns());
    }
    
    /**
     * @test
     */
    public function testGetDeleteStateIsNotNull() {
        $this->assertNotNull($this->configurationService->getDeleteState());
    }

    /**
     * @test
     */
    public function testGetDeleteStateIsInt() {
        $this->assertInternalType('int', $this->configurationService->getDeleteState());
    }

    /**
     * @test
     */
    public function testGetPullServerIsNotNull() {
        $this->assertNotNull($this->configurationService->getPullserver());
    }

    /**
     * @test
     */
    public function testGetPullServerIsString() {
        $this->assertInternalType('string', $this->configurationService->getPullserver());
    }

    /**
     * @test
     */
    public function testGetUsernameIsNotNull() {
        $this->assertNotNull($this->configurationService->getUsername());
    }

    /**
     * @test
     */
    public function testGetUsernameIsString() {
        $this->assertInternalType('string', $this->configurationService->getUsername());
    }

    /**
     * @test
     */
    public function testGetPasswordIsNotNull() {
        $this->assertNotNull($this->configurationService->getPassword());
    }

    /**
     * @test
     */
    public function testGetPasswordIsString() {
        $this->assertInternalType('string', $this->configurationService->getPassword());
    }

}