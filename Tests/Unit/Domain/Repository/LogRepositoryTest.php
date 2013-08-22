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
 * LogRepositoryTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class LogRepositoryTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * LogRepository
     * 
     * @var TYPO3\Deployment\Domain\Repository\LogRepository $logRepository
     */
    protected $logRepository = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->logRepository = new \TYPO3\Deployment\Domain\Repository\LogRepository();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testFindYoungerThenIsQueryResult(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult', $this->logRepository->findYoungerThen($timestamp));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testFindYoungerThenNotNull(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotNull($this->logRepository->findYoungerThen($timestamp));
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testFindYoungerThenContainsLogs(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertContainsOnly('\TYPO3\Deployment\Domain\Model\Log', $this->logRepository->findYoungerThen($timestamp));
    }
}