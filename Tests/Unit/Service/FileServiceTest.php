<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class FileServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     *
     * @var \TYPO3\Deployment\Service\FileService
     */
    protected $fileService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->fileService = new \TYPO3\Deployment\Service\FileService();
    }
    
    /**
     * @test
     */
    public function testReadFilesInFileadminIsArray() {
        $this->assertInternalType('array', $this->fileService->readFilesInFileadmin());
    }
    
    /**
     * @test
     */
    public function testReadFilesInFileadminIsNotEmpty(){
        $this->assertNotEmpty($this->fileService->readFilesInFileadmin());
    }

    /**
     * @test
     */
    function testReadFilesInFileadminWithoutDeploymentFiles() {
        $this->assertNotContains('/deployment', $this->fileService->readFilesInFileadmin());
    }
    
    /**
     * @test
     */
    function testReadFilesInFileadminWithoutProcessedFiles() {
        $this->assertNotContains('/_processed', $this->fileService->getNotIndexedFiles());
    }
    
    /**
     * @test
     */
    function testReadFilesInFileadminWithoutTempFiles() {
        $this->assertNotContains('/_temp', $this->fileService->getNotIndexedFiles());
    }
    
    /**
     * @test
     */
    public function testGetNotIndexedFilesIsArray() {
        $this->assertInternalType('array', $this->fileService->getNotIndexedFiles());
    }
    
    /**
     * @test
     */
    public function testGetNotIndexedFilesIsNotEmpty() {
        $this->assertNotEmpty($this->fileService->getNotIndexedFiles());
    }
    
    /**
     * @test
     */
    function testGenerateUuid() {
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $this->fileService->generateUuid());
    }

    /**
     * @test
     */
    function testGetFileadminPathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getFileadminPathWithoutTrailingSlash());
    }

    /**
     * @test
     */
    function testGetFileadminPathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getFileadminPathWithTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentPathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentPathWithoutTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentPathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentPathWithTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentDatabasePathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentDatabasePathWithoutTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentDatabasePathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentDatabasePathWithTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentMediaPathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentMediaPathWithoutTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentMediaPathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentMediaPathWithTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentResourcePathWithoutTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentResourcePathWithoutTrailingSlash());
    }

    /**
     * @test
     */
    function testGetDeploymentResourcePathWithTrailingSlashIsString() {
        $this->assertInternalType('string', $this->fileService->getDeploymentResourcePathWithTrailingSlash());
    }
}