<?php

namespace TYPO3\Deployment\Tests\Unit\Domain\Model;

class AbstractModelTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * AbstractModel
     * 
     * @var TYPO3\Deployment\Domain\Model\AbstractModel
     */
    protected $abstractModel = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->abstractModel = new \TYPO3\Deployment\Domain\Model\AbstractModel();
    }
    
    /**
     * @test
     */
    public function testGetCleanProperties(){
        $this->assertInternalType('array', $this->abstractModel->_getCleanProperties());
    }
    
    /**
     * @test
     */
    public function testGetCleanPropertiesIsNotEmpty(){
        $this->assertEmpty($this->abstractModel->_getCleanProperties());
    }
}