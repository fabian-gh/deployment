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

namespace TYPO3\Deployment\Tests\Unit\Domain\Model;

/**
 * AbstractModelTest
 * Test class
 * 
 * @package    Deployment
 * @subpackage Tests
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class AbstractModelTest extends \TYPO3\Deployment\Tests\Unit\BaseTestCase {
    
    /**
     * AbstractModel
     * 
     * @var TYPO3\Deployment\Domain\Model\AbstractModel $abstractModel
     */
    protected $abstractModel = NULL;
    
    
    /**
     * Build up the test
     */
    public function __construct(){
        $this->abstractModel = new \TYPO3\Deployment\Domain\Model\AbstractModel();
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetCleanProperties(){
        $this->assertInternalType('array', $this->abstractModel->_getCleanProperties());
    }
    
    /**
     * Testmethod
     * 
     * @test
     */
    public function testGetCleanPropertiesIsNotEmpty(){
        $this->assertEmpty($this->abstractModel->_getCleanProperties());
    }
}