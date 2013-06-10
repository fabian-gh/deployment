<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

class CopyServiceTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
    
    /**
     * @test 
     */
    function testCheckIfCommandControllerIsRegistered(){
        /** @var TYPO3\Deployment\Service\ConpyService $con */
        $copy = new TYPO3\Deployment\Service\CopyService();
        
        $this->assertTrue($copy->checkIfCommandControllerIsRegistered());
    }
    
    /**
     * @test 
     */
    function testCheckIfCliUserIsRegistered(){
        /** @var TYPO3\Deployment\Service\ConpyService $con */
        $copy = new TYPO3\Deployment\Service\CopyService();
        
        $this->assertTrue($copy->checkIfCliUserIsRegistered());
    }
    
    /**
     * @test 
     */
    function testGetTaskUidNotEmpty(){
        /** @var TYPO3\Deployment\Service\ConpyService $con */
        $copy = new TYPO3\Deployment\Service\CopyService();
        
        $this->assertNotEmpty($copy->getTaskUid());
    }
    
    /**
     * @test 
     */
    function testGetDisable(){
        /** @var TYPO3\Deployment\Service\ConpyService $con */
        $copy = new TYPO3\Deployment\Service\CopyService();
        
        $this->assertEquals(1, $copy->getDisable());
    }
    
    /**
     * @test 
     */
    function testGetDisableNotEmpty(){
        /** @var TYPO3\Deployment\Service\ConpyService $con */
        $copy = new TYPO3\Deployment\Service\CopyService();
        
        $this->assertNotEmpty($copy->getDisable());
    }
    
    /**
     * @test 
     */
    function testAllPrecausionsSet(){
        /** @var TYPO3\Deployment\Service\ConpyService $con */
        $copy = new TYPO3\Deployment\Service\CopyService();
        
        $this->assertTrue($copy->allPrecautionsSet());
    }
    
    /**
     * @test 
     */
    function testGetCliPath(){
        /** @var TYPO3\Deployment\Service\ConpyService $con */
        $copy = new TYPO3\Deployment\Service\CopyService();
        
        $this->assertEquals(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT').GeneralUtility::getIndpEnv('TYPO3_SITE_PATH').'typo3/cli_dispatch.phpsh', $copy->getCliPath());
    }
    
}