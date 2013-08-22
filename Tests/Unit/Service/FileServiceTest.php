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
 * FileServiceTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class FileServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * FileService
     * 
     * @var \TYPO3\Deployment\Service\FileService $fileService
     */
    protected $fileService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->fileService = new \TYPO3\Deployment\Service\FileService();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadFilesInFileadminIsArray() {
        $this->assertInternalType('array', $this->fileService->readFilesInFileadmin());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testReadFilesInFileadminIsNotEmpty(){
        $this->assertNotEmpty($this->fileService->readFilesInFileadmin());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testReadFilesInFileadminWithoutDeploymentFiles() {
        $this->assertNotContains('/deployment', $this->fileService->readFilesInFileadmin());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    function testReadFilesInFileadminWithoutProcessedFiles() {
        $this->assertNotContains('/_processed', $this->fileService->getNotIndexedFiles());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    function testReadFilesInFileadminWithoutTempFiles() {
        $this->assertNotContains('/_temp', $this->fileService->getNotIndexedFiles());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetNotIndexedFilesIsArray() {
        $this->assertInternalType('array', $this->fileService->getNotIndexedFiles());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetNotIndexedFilesIsNotEmpty() {
        $this->assertNotEmpty($this->fileService->getNotIndexedFiles());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    function testGenerateUuid() {
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $this->fileService->generateUuid());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetFileadminPathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getFileadminPathWithoutTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetFileadminPathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getFileadminPathWithTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentPathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentPathWithoutTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentPathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentPathWithTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentDatabasePathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentDatabasePathWithoutTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentDatabasePathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentDatabasePathWithTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentMediaPathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentMediaPathWithoutTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentMediaPathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentMediaPathWithTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentBbdeploymentPathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentBBDeploymentPathWithoutTrailingSlash());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    function testGetDeploymentBbdeploymentPathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentBBDeploymentPathWithTrailingSlash());
    }
}