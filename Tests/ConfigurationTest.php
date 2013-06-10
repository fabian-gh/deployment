<?php

class ConfigurationServiceTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
    
    /**
     * @test 
     */
    function testCheckTableEntries(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        $con->checkTableEntries();
    }
    
    /**
     * @test 
     */
    function testFilterEntriesAreEmpty(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QueryInterface $queryResult */
        $res = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\QueryInterface');
        
        $this->assertEmpty($con->filterEntries($res));
    }
    
    /**
     * @test 
     */
    function testGetDeploymentTablesHasKeyZero(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertArrayHasKey(0, $con->getDeploymentTables());
    }
    
    /**
     * @test 
     */
    function testGetDeleteStateIsOne(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertEquals(1, $con->getDeleteState());
    }
    
    /**
     * @test 
     */
    function testGetDeleteStateIsZero(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertEquals(0, $con->getDeleteState());
    }
    
    /**
     * @test 
     */
    function testGetPullServerIsEmpty(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertEquals('', $con->getPullserver());
    }
    
    /**
     * @test 
     */
    function testGetPullServerIsNotEmpty(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertNotEmpty($con->getPullserver());
    }
    
    /**
     * @test 
     */
    function testGetUsernameIsEmpty(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertEquals('', $con->getUsername());
    }
    
    /**
     * @test 
     */
    function testGetUsernameIsNotEmpty(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertNotEmpty($con->getUsername());
    }
    
    /**
     * @test 
     */
    function testGetPasswordIsEmpty(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertEquals('', $con->getPassword());
    }
    
    /**
     * @test 
     */
    function testGetPasswordIsNotEmpty(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        
        $this->assertNotEmpty($con->getPassword());
    }
}