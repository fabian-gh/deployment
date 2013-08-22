<?php

/**
 * Deployment-Extension
 * This is an extension to integrate a deployment process for TYPO3 CMS
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Service;

/**
 * DatabaseService
 * Class for temporarily database connection
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class DatabaseService extends AbstractDataService {
    
    /**
     * Hostname
     * 
     * @var string $host
     */
    protected $host = 'localhost';
    
    /**
     * Username
     * 
     * @var string $user
     */
    protected $user = 'root';
    
    /**
     * Password
     * 
     * @var string $pass
     */
    protected $pass = 'root';
    
    /**
     * Databasename
     * 
     * @var string $db
     */
    protected $db = 't3masterdeploy2';

    
    /**
     * Quick access to the test databse
     */
    public static function connectTestDatabaseIfExist() {
        $dbService = new DatabaseService();
        $dbService->connectTestDatabaseIfExistInternal();
    }

    
    /**
     * Reset the connection to the database
     */
    public static function reset() {
        $dbService = new DatabaseService();
        $dbService->resetInternal();
    }

    
    /**
     * Reset the connection to the database
     */
    public function resetInternal() {
        $this->getDatabase()->setDatabaseHost($this->getCurrentDatabaseHost());
        $this->getDatabase()->setDatabaseUsername($this->getCurrentDatabaseUser());
        $this->getDatabase()->setDatabasePassword($this->getCurrentDatabasePassword());
        $this->getDatabase()->setDatabaseName($this->getCurrentDatabaseName());
        $this->getDatabase()->connectDB();
    }

    
    /**
     * Check if connection data exist
     */
    public function connectTestDatabaseIfExistInternal() {
        if($this->host !== '' && $this->user !== '' && $this->db !== ''){
            $this->connect($this->host, $this->user, $this->pass, $this->db);
        }
    }

    
    /**
     * Connect to test database
     * 
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $db
     */
    protected function connect($host, $user, $pass, $db) {
        $this->getDatabase()->setDatabaseHost($host);
        $this->getDatabase()->setDatabaseUsername($user);
        $this->getDatabase()->setDatabasePassword($pass);
        $this->getDatabase()->setDatabaseName($db);
        $this->getDatabase()->connectDB();
    }
    
    /**
     * Returns hostname
     * 
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Sets hostname
     * 
     * @param string $host
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Returns Username
     * 
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Sets username
     * 
     * @param string $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * Returns password
     * 
     * @return string
     */
    public function getPass() {
        return $this->pass;
    }

    /**
     * Sets password
     * 
     * @param string $pass
     */
    public function setPass($pass) {
        $this->pass = $pass;
    }

    /**
     * Returns Databasename
     * 
     * @return string
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * Sets databasename
     * @param string $db
     */
    public function setDb($db) {
        $this->db = $db;
    }
}