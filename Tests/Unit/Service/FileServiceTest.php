<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class FileServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

	/**
	 * @test
	 */
	function testReadFilesInFileadmin() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('array', $file->readFilesInFileadmin());
	}

	/**
	 * @test
	 */
	function testReadFilesInFileadminWithoutDeploymentFiles() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertNotContains('/deployment', $file->readFilesInFileadmin());
	}

	/**
	 * @test
	 */
	function testGetNotIndexedFiles() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('array', $file->getNotIndexedFiles());
	}

	/**
	 * @test
	 */
	function testReadFilesInFileadminWithoutProcessedFiles() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertNotContains('/_processed', $file->getNotIndexedFiles());
	}

	/**
	 * @test
	 */
	function testReadFilesInFileadminWithoutTempFiles() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertNotContains('/_temp', $file->getNotIndexedFiles());
	}

	/**
	 * @test
	 */
	function testGenerateUuid() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $file->generateUuid());
	}

	/**
	 * @test
	 */
	function testxmlValidation() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertTrue($file->xmlValidation());
	}

	/**
	 * @test
	 */
	function testGetFileadminPathWithoutTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getFileadminPathWithoutTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetFileadminPathWithTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getFileadminPathWithTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetDeploymentPathWithoutTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentPathWithoutTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetDeploymentPathWithTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentPathWithTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetDeploymentDatabasePathWithoutTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentDatabasePathWithoutTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetDeploymentDatabasePathWithTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentDatabasePathWithTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetDeploymentMediaPathWithoutTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentMediaPathWithoutTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetDeploymentMediaPathWithTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentMediaPathWithTrailingSlash());
	}

	/**
	 * @test
	 */
	function testGetDeploymentResourcePathWithoutTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentResourcePathWithoutTrailingSlash());
	}

	/**
	 * @test
	 */
	function testetDeploymentResourcePathWithTrailingSlashIsString() {
		/** @var \TYPO3\Deployment\Service\FileService $con */
		$file = new \TYPO3\Deployment\Service\FileService();

		$this->assertInternalType('string', $file->getDeploymentResourcePathWithTrailingSlash());
	}

}