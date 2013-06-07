<?php

class ConfigurationTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
    
    /**
     * @test 
     */
    function areTableEntriesGonnaBeChecked(){
        /** @var TYPO3\Deployment\Service\ConfigurationService $con */
        $con = new TYPO3\Deployment\Service\ConfigurationService();
        $con->checkTableEntries();
    }
}