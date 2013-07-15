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

use \TYPO3\Deployment\Service\FileService;
use \TYPO3\Deployment\Service\DatabaseService;
use \TYPO3\CMS\Core\Utility\CommandUtility;
use \TYPO3\CMS\Core\Database\DatabaseConnection;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BoundlessBackdeploymentService
 * Class for creating databse dump and resource copying
 *
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */
class BoundlessBackdeploymentService extends AbstractDataService {

    /**
     * @var string
     */
    protected $mysqlServer;
    
    /**
     * @var string
     */
    protected $database;
    
    /**
     * @var string
     */
    protected $username;
    
    /**
     * @var string
     */
    protected $password;
    
    
    /**
     * Init the class
     * 
     * @param string $mysqlServer
     * @param string $username
     * @param string $password
     */
    public function init($mysqlServer, $database, $username, $password){
        $this->setMysqlServer($mysqlServer);
        $this->setDatabase($database);
        $this->setUsername($username);
        $this->setPassword($password);
    }
    
    
    /**
     * Check if a path is defined and not empty
     * 
     * @return boolean
     */
    public function checkIfMysqldumpPathIsNotEmpty(){
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        $path = $configurationService->getMysqldumpPath();
        
        if(!empty($path) && $path != ''){
            return true;
        }
        return false;
    }
    
    
    /**
     * Creates the database dump
     */
    public function createDbDump(){
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        /** @var \TYPO3\Deployment\Service\FileService $fieService */
        $fileService = new FileService();
        
        $mysqldumpPath = $configurationService->getMysqldumpPath();
        $tablelist = $this->getTableList();
        //DebuggerUtility::var_dump('cd "'.$mysqldumpPath.'"');
        //DebuggerUtility::var_dump('mysqldump --opt --skip-disable-keys --user='.$this->username.' --password='.$this->password.' --database '.$this->database.' --result-file="'.$fileService->getDeploymentBBDeploymentPathWithTrailingSlash().$this->database.'.sql" --tables '.$tablelist);die();
        CommandUtility::exec('cd "'.$mysqldumpPath.'"');
        CommandUtility::exec('mysqldump --opt --skip-disable-keys --user='.$this->username.' --password='.$this->password.' --database '.$this->database.' --result-file="'.$fileService->getDeploymentBBDeploymentPathWithTrailingSlash().$this->database.'.sql" --tables '.$tablelist);
    }
    
    
    /**
     * Returns a list of tables without caching tables
     * 
     * @return string
     */
    protected function getTableList(){
        $list = '';
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
        $databaseConnection = new DatabaseConnection();
        
        $databaseConnection->setDatabaseHost($this->mysqlServer);
        $databaseConnection->setDatabaseName($this->database);
        $databaseConnection->setDatabaseUsername($this->username);
        $databaseConnection->setDatabasePassword($this->password);
        $databaseConnection->connectDB();
        
        $tableprop = $databaseConnection->admin_get_tables();
        foreach($tableprop as $key => $value){
            if(strstr($key, 'cache') == FALSE && strstr($key, 'cf_') == FALSE){
                $list .= $key.' ';
            }
        }
        
        return $list;
    }
    
    
    // ========================= Getter & Setter ===============================
    
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