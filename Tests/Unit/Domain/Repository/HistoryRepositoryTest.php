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
 * HistoryRepositoryTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class HistoryRepositoryTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * HistoryRepository
     * 
     * @var TYPO3\Deployment\Domain\Repository\HistoryRepository $historyRepository
     */
    protected $historyRepository = NULL;
    
    /**
     * Log data
     * 
     * @var array $logDataArray
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
     * Testmethod
     * 
     * @test
     */
    public function testHistoryRepositoryIsObject(){
        $this->assertInstanceOf('\TYPO3\Deployment\Domain\Repository\HistoryRepository', $this->historyRepository);
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testLogDataIsArray(){
        $this->assertInternalType('array', $this->logDataArray);
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testLogDataArrayContainsOnlyLogDataObjects(){
        $this->assertContainsOnly('\TYPO3\Deployment\Domain\Model\LogData', $this->logDataArray);
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testFindHistoryDataIsNotEmpty(){
        $this->assertNotEmpty($this->historyRepository->findHistoryData($this->logDataArray[0]));
    }
}