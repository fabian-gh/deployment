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

namespace TYPO3\Deployment\Tests\Unit\Scheduler;

/**
 * UuidTaskTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class UuidTaskTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * Uuid task
     * 
     * @var \TYPO3\Deployment\Scheduler\UuidTask $uuidTask
     */
    protected $uuidTask = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->uuidTask = new \TYPO3\Deployment\Scheduler\UuidTask();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCheckIfUuidExistsIsBoolean(){
        $this->assertInternalType('boolean', $this->uuidTask->execute());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCheckIfTaskIsRegistered(){
        $this->assertTrue($this->uuidTask->execute());
    }
}