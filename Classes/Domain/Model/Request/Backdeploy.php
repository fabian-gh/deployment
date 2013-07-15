<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Domain\Model\Request;

use \TYPO3\Deployment\Domain\Model\AbstractModel;

/**
 * Backdeploy
 * Class for back deployment requests which is appended to the form
 *
 * @package    Deployment
 * @subpackage Domain\Model\Request
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class Backdeploy extends AbstractModel{

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $mysqlServer;
    
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $database;
    
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $username;
    
    /**
     * @var string
     * @validate NotEmpty
     */
    protected $password;
    
    /**
     * @return string
     */
    public function getMysqlServer() {
        return $this->mysqlServer;
    }

    /**
     * @param string $mysqlServer
     */
    public function setMysqlServer($mysqlServer) {
        $this->mysqlServer = $mysqlServer;
    }
    
    /**
     * @return string
     */
    public function getDatabase() {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase($database) {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }
}