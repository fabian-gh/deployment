<?php
namespace TYPO3\Deployment\Domain\Repository;

class LogRepositoryTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * LogRepository
     * 
     * @var TYPO3\Deployment\Domain\Repository\LogRepository
     */
    protected $logRepository = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->logRepository = new \TYPO3\Deployment\Domain\Repository\LogRepository();
    }
    
    /**
     * @test
     */
    public function testFindYoungerThenIsQueryResult(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertInstanceOf('\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult', $this->logRepository->findYoungerThen($timestamp));
    }
    
    /**
     * @test
     */
    public function testFindYoungerThenNotNull(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertNotNull($this->logRepository->findYoungerThen($timestamp));
    }
    
    /**
     * @test
     */
    public function testFindYoungerThenContainsLogs(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        $timestamp = $reg->getLastDeploy();
        $this->assertContainsOnly('\TYPO3\Deployment\Domain\Model\Log', $this->logRepository->findYoungerThen($timestamp));
    }
}