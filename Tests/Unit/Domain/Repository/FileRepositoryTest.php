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

namespace TYPO3\Deployment\Tests\Unit\Domain\Repository;

/**
 * FileRepositoryTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class FileRepositoryTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * FileRepository
     * 
     * @var \TYPO3\Deployment\Domain\Repository\FileRepository $fileRepository
     */
    protected $fileRepository = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct() {
        $this->fileRepository = new \TYPO3\Deployment\Domain\Repository\FileRepository();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testFindYoungerThenIsQueryResultInterface(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\QueryResultInterface', $this->fileRepository->findYoungerThen($timestamp));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testFindYoungerThenIsNotNull(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotNull($this->fileRepository->findYoungerThen($timestamp));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testFindYoungerThenIsNotEmpty(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotEmpty($this->fileRepository->findYoungerThen($timestamp));
    }
}