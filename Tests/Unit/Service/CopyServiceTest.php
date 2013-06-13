<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class CopyServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

	/**
	 * @test
	 */
	function testCheckIfCommandControllerIsRegistered() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertTrue($copy->checkIfCommandControllerIsRegistered());
	}

	/**
	 * @test
	 */
	function testCheckIfCliUserIsRegistered() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertTrue($copy->checkIfCliUserIsRegistered());
	}

	/**
	 * @test
	 */
	function testGetTaskUidNotEmpty() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertNotEmpty($copy->getTaskUid());
	}

	/**
	 * @test
	 */
	function testGetDisable() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertEquals(1, $copy->getDisable());
	}

	/**
	 * @test
	 */
	function testGetDisableNotEmpty() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertNotEmpty($copy->getDisable());
	}

	/**
	 * @test
	 */
	function testAllPrecausionsSet() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertTrue($copy->allPrecautionsSet());
	}

	/**
	 * @test
	 */
	function testGetCliPathIsString() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertInternalType('string', $copy->getCliPath());
	}

	/**
	 * @test
	 */
	function testGetCliPath() {
		/** @var \TYPO3\Deployment\Service\CopyService $con */
		$copy = new \TYPO3\Deployment\Service\CopyService();

		$this->assertEquals(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'typo3/cli_dispatch.phpsh', $copy->getCliPath());
	}

}