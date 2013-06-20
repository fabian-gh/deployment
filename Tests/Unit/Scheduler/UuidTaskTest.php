<?php

namespace TYPO3\Deployment\Tests\Unit\Scheduler;

class UuidTaskTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     *
     * @var \TYPO3\Deployment\Scheduler\UuidTask
     */
    protected $uuidTask = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->uuidTask = new \TYPO3\Deployment\Scheduler\UuidTask();
    }
    
    /**
     * @test
     */
    public function testCheckIfUuidExistsIsBoolean(){
        $this->assertInternalType('boolean', $this->uuidTask->execute());
    }
    
    /**
     * @test
     */
    public function testCheckIfTaskIsRegistered(){
        $this->assertTrue($this->uuidTask->execute());
    }
}