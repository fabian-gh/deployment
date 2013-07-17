<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class BoundlessBackdeploymentServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     *
     * @var \TYPO3\Deployment\Service\BoundlessBackdeploymentService
     */
    protected $bbdService = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->bbdService = new \TYPO3\Deployment\Service\BoundlessBackdeploymentService();
        $this->bbdService->init('string', 'string', 'string', 'string');
    }
    
    /**
     * @test
     */
    public function testMySqlServerIsString() {
        $this->assertInternalType('string', $this->bbdService->getMysqlServer());
    }
    
    /**
     * @test
     */
    public function testMySqlServerIsNotNull() {
        $this->assertNotNull($this->bbdService->getMysqlServer());
    }
    
    /**
     * @test
     */
    public function testMySqlServerIsNotEmpty() {
        $this->assertNotEmpty($this->bbdService->getMysqlServer());
    }
    
    /**
     * @test
     */
    public function testDatabasenameIsString() {
        $this->assertInternalType('string', $this->bbdService->getDatabaseName());
    }
    
    /**
     * @test
     */
    public function testDatabasenameIsNotNull() {
        $this->assertNotNull($this->bbdService->getDatabaseName());
    }
    
    /**
     * @test
     */
    public function testDatabasenameIsNotEmpty(){
        $this->assertNotEmpty($this->bbdService->getDatabaseName());
    }
    
    /**
     * @test
     */
    public function testUsernameIsString() {
        $this->assertInternalType('string', $this->bbdService->getUsername());
    }
    
    /**
     * @test
     */
    public function testUsersenameIsNotNull() {
        $this->assertNotNull($this->bbdService->getUsername());
    }
    
    /**
     * @test
     */
    public function testUsernameIsNotEmpty(){
        $this->assertNotEmpty($this->bbdService->getUsername());
    }
    
    /**
     * @test
     */
    public function testPasswordIsString() {
        $this->assertInternalType('string', $this->bbdService->getPassword());
    }
    
    /**
     * @test
     */
    public function testPasswordIsNotNull() {
        $this->assertNotNull($this->bbdService->getPassword());
    }
    
    /**
     * @test
     */
    public function testIfMysqlDumpPathIsNotEmpty() {
        $this->assertNotEmpty($this->bbdService->checkIfMysqldumpPathIsNotEmpty());
    }
    
    /**
     * @test
     */
    public function testIfMysqlDumpPathIsNotNull() {
        $this->assertNotNull($this->bbdService->checkIfMysqldumpPathIsNotEmpty());
    }
    
    /**
     * @test
     */
    public function testIfMysqlDumpPathIsTrue() {
        $this->assertTrue($this->bbdService->checkIfMysqldumpPathIsNotEmpty());
    }
    
    /**
     * @test
     */
    public function testIfDbDumpIsNull() {
        // Null means that a dump exists
        $this->assertNull($this->bbdService->checkIfDbDumpExists());
    }  
}