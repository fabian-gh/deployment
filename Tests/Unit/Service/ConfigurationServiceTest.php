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
	function testGetDeploymentTablesHasKey() {
		$this->assertArrayHasKey(0, $this->configurationService->getDeploymentTables());
	}

	/**
	 * @test
	 */
	function testGetDeploymentTablesNotNull() {
		$this->assertNotNull($this->configurationService->getDeploymentTables());
	}

	/**
	 * @test
	 */
	function testGetDeploymentTablesContainsTtContent() {
		$this->assertContains('tt_content', $this->configurationService->getDeploymentTables());
	}

	/**
	 * @test
	 */
	function testGetDeploymentTablesCotainsPages() {
		$this->assertContains('pages', $this->configurationService->getDeploymentTables());
	}

	/**
	 * @test
	 */
	function testGetDeleteStateIsNotNull() {
		$this->assertNotNull($this->configurationService->getDeleteState());
	}

	/**
	 * @test
	 */
	function testGetDeleteStateIsInt() {
		$this->assertInternalType('int', $this->configurationService->getDeleteState());
	}

	/**
	 * @test
	 */
	function testGetPullServerIsNotNull() {
		$this->assertNotNull($this->configurationService->getPullserver());
	}

	/**
	 * @test
	 */
	function testGetPullServerIsString() {
		$this->assertInternalType('string', $this->configurationService->getPullserver());
	}

	/**
	 * @test
	 */
	function testGetUsernameIsNotNull() {
		$this->assertNotNull($this->configurationService->getUsername());
	}

	/**
	 * @test
	 */
	function testGetUsernameIsString() {
		$this->assertInternalType('string', $this->configurationService->getUsername());
	}

	/**
	 * @test
	 */
	function testGetPasswordIsNotNull() {
		$this->assertNotNull($this->configurationService->getPassword());
	}

	/**
	 * @test
	 */
	function testGetPasswordIsString() {
		$this->assertInternalType('string', $this->configurationService->getPassword());
	}
}