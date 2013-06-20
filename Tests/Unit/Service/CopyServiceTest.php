<?php

namespace TYPO3\Deployment\Tests\Unit\Service;

class CopyServiceTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {

    /**
     * CopyService
     * 
     * @var \TYPO3\Deployment\Service\CopyService|NULL
     */
    protected $copyService = NULL;
    
    
    /**
     * build up the test
     */
    public function __construct(){
        $this->copyService = new \TYPO3\Deployment\Service\CopyService();
    }
    
    /**
     * @test
     */
    public function testCheckIfCommandControllerIsRegistered() {
        $this->assertTrue($this->copyService->checkIfCommandControllerIsRegistered());
    }

    /**
     * @test
     */
    public function testCheckIfCliUserIsRegistered() {
        $this->assertTrue($this->copyService->checkIfCliUserIsRegistered());
    }

    /**
     * @test
     */
    public function testGetTaskUidNotEmpty() {
        $this->assertNotEmpty($this->copyService->getTaskUid());
    }

    /**
     * @test
     */
    public function testGetDisable() {
        $this->assertEquals(1, $this->copyService->getDisable());
    }

    /**
     * @test
     */
    public function testGetDisableNotEmpty() {
        $this->assertNotEmpty($this->copyService->getDisable());
    }

    /**
     * @test
     */
    public function testAllPrecausionsSet() {
        $this->assertTrue($this->copyService->allPrecautionsSet());
    }

    /**
     * @test
     */
    public function testGetCliPathIsString() {
        $this->assertInternalType('string', $this->copyService->getCliPath());
    }

    /**
     * @test
     */
    public function testGetCliPath() {
        $this->assertEquals(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . 'typo3/cli_dispatch.phpsh', $this->copyService->getCliPath());
    }

}