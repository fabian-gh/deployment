<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class RegistryServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

	/**
	 * @test
	 */
	function testGetLastDeployIsInt() {
		/** @var \TYPO3\Deployment\Service\RegistryService $con */
		$reg = new \TYPO3\Deployment\Service\RegistryService ();

		$this->assertInternalType('int', $reg->getLastDeploy());
	}

	/**
	 * @test
	 */
	function testGetStoredFailuresIsArray() {
		/** @var \TYPO3\Deployment\Service\RegistryService $con */
		$reg = new \TYPO3\Deployment\Service\RegistryService ();

		$this->assertInternalType('array', unserialize($reg->getStoredFailures()));
	}

	/**
	 * @test
	 */
	function testGetStoredHistoryEntriesIsArray() {
		/** @var \TYPO3\Deployment\Service\RegistryService $con */
		$reg = new \TYPO3\Deployment\Service\RegistryService ();

		$this->assertInternalType('array', unserialize($reg->getStoredHistoryEntries()));
	}
}