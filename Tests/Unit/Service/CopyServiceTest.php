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

namespace TYPO3\Deployment\Tests\Unit\Service;

/**
 * CopyServiceTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class CopyServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * CopyService
     * 
     * @var \TYPO3\Deployment\Service\CopyService|NULL $copyService
     */
    protected $copyService = NULL;
    
    
    /**
     * build up the test
     */
    public function __construct(){
        $this->copyService = new \TYPO3\Deployment\Service\CopyService();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testCheckIfCommandControllerIsRegistered() {
        $this->assertTrue($this->copyService->checkIfCommandControllerIsRegistered());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testCheckIfCliUserIsRegistered() {
        $this->assertTrue($this->copyService->checkIfCliUserIsRegistered());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetTaskUidIsNotNull() {
        $this->copyService->checkIfCommandControllerIsRegistered();
        $this->assertNotNull($this->copyService->getTaskUid());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDisableIsInt() {
        $this->copyService->checkIfCommandControllerIsRegistered();
        $this->assertInternalType('int', $this->copyService->getDisable());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetDisableNotNull() {
        $this->copyService->checkIfCommandControllerIsRegistered();
        $this->assertNotNull($this->copyService->getDisable());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testAllPrecausionsSet() {
        $this->assertTrue($this->copyService->allPrecautionsSet());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetCliPathIsString() {
        $this->assertInternalType('string', $this->copyService->getCliPath());
    }

    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetCliPath() {
        $this->assertEquals(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'typo3/cli_dispatch.phpsh', $this->copyService->getCliPath());
    }

}