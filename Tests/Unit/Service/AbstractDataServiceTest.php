<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class AbstractDataServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * AbstractDataService
     *
     * @var \TYPO3\Deployment\Service\
     */
    protected $abstractDataService = NULL;

    /**
     * Build up the test
     */
    public function __construct() {
        $this->abstractDataService = new \TYPO3\Deployment\Service\AbstractDataService();
    }
    
    /**
     * @test
     */
    public function testGetUuidByUidIsNotEmpty(){
        $this->assertNotEmpty($this->abstractDataService->getUuidByUid(1, pages));
    }
    
    /**
     * @test
     */
    public function testGetUuidByUidIsString(){
        $this->assertInternalType('string', $this->abstractDataService->getUuidByUid(1, pages));
    }
    
    /**
     * @test
     */
    public function testGetUidByUuidIsNotNull(){
        $this->assertNotNull($this->abstractDataService->getUidByUuid(1, pages));
    }
    
    /**
     * @test
     */
    public function testGetUidByUuidIsInt(){
        $this->assertInternalType('int', (int)$this->abstractDataService->getUidByUuid($this->abstractDataService->getUuidByUid(1, pages), pages));
    }
    
    /**
     * @test
     */
    public function testGetPidByUuidIsNotNull(){
        $this->assertNotNull($this->abstractDataService->getPidByUuid(1, pages));
    }
    
    /**
     * @test
     */
    public function testGetPidByUuidIsInt(){
        $this->assertInternalType('int', (int)$this->abstractDataService->getPidByUuid($this->abstractDataService->getUuidByUid(1, pages), pages));
    }
    
    /**
     * @test
     */
    public function testControlResultIsNotNull(){
        $this->assertNotEmpty($this->abstractDataService->getControlResult('title', 'pages', $this->abstractDataService->getUuidByUid(1, pages)));
    }
    
    /**
     * @test
     */
    public function testControlResultIsString(){
        $this->assertInternalType('string', $this->abstractDataService->getControlResult('title', 'pages', $this->abstractDataService->getUuidByUid(1, pages)));
    }
}