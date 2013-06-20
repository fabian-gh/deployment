<?php

namespace TYPO3\Deployment\Tests\Unit\Domain\Repository;

class FileRepositoryTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * FileRepository
     * 
     * @var \TYPO3\Deployment\Domain\Repository\FileRepository
     */
    protected $fileRepository = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct() {
        $this->fileRepository = new \TYPO3\Deployment\Domain\Repository\FileRepository();
    }
    
    /**
     * @test
     */
    public function testFindYoungerThenIsQueryResultInterface(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\QueryResultInterface', $this->fileRepository->findYoungerThen($timestamp));
    }
    
    /**
     * @test
     */
    public function testFindYoungerThenIsNotNull(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotNull($this->fileRepository->findYoungerThen($timestamp));
    }
    
    /**
     * @test
     */
    public function testFindYoungerThenIsNotEmpty(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotEmpty($this->fileRepository->findYoungerThen($timestamp));
    }
}