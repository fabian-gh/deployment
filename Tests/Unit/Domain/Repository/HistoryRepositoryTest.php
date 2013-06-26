<?php

namespace TYPO3\Deployment\Tests\Unit\Domain\Repository;

class HistoryRepositoryTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * HistoryRepository
     * 
     * @var TYPO3\Deployment\Domain\Repository\HistoryRepository
     */
    protected $historyRepository = NULL;
    
    /**
     *
     * @var array
     */
    protected $logDataArray = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->historyRepository = new \TYPO3\Deployment\Domain\Repository\HistoryRepository();
        $this->createLogData();
    }
    
    /**
     * Create a LogData Object for testing
     */
    protected function createLogData(){
        /** @var \TYPO3\Deployment\Service\RegistryService $reg */
        $reg = new \TYPO3\Deployment\Service\RegistryService();
        /** @var \TYPO3\Deployment\Domain\Repository\LogRepository $logRepository */
        $logRepository = new \TYPO3\Deployment\Domain\Repository\LogRepository();
        /** @var \TYPO3\Deployment\Service\XmlDatabaseService $xmlDatabaseService */
        $xmlDatabaseService = new \TYPO3\Deployment\Service\XmlDatabaseService();
        
        $logEntries = $logRepository->findYoungerThen($reg->getLastDeploy());
        $this->logDataArray = $xmlDatabaseService->unserializeLogData($logEntries);
    }
    
    /**
     * @test
     */
    public function testHistoryRepositoryIsObject(){
        $this->assertInstanceOf('\TYPO3\Deployment\Domain\Repository\HistoryRepository', $this->historyRepository);
    }
    
    /**
     * @test
     */
    public function testLogDataIsArray(){
        $this->assertInternalType('array', $this->logDataArray);
    }
    
    /**
     * @test
     */
    public function testLogDataArrayContainsOnlyLogDataObjects(){
        $this->assertContainsOnly('\TYPO3\Deployment\Domain\Model\LogData', $this->logDataArray);
    }
    
    /**
     * @test
     */
    public function testFindHistoryDataIsNotEmpty(){
        $this->assertNotEmpty($this->historyRepository->findHistoryData($this->logDataArray[0]));
    }
}