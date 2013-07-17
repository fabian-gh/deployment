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
    protected $databaseName;

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
    public function init($mysqlServer, $databaseName, $username, $password){
        $this->setMysqlServer($mysqlServer);
        $this->setDatabaseName($databaseName);
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
     * Check if the db dump exists
     * 
     * @return boolean
     */
    public function checkIfDbDumpExists(){
        $fileArr = array();
        /** @var \TYPO3\Deployment\Service\FileService $fieService */
        $fileService = new FileService();
        
        $path = $fileService->getDeploymentBBDeploymentPathWithTrailingSlash();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $path);
        
        foreach($fileList as $file){
            if(strstr($file, $this->databaseName.'.sql') !== FALSE){
                return true;
            }
        }
    }
    
    
    /**
     * Returns an array with single create and insert commands
     * 
     * @return array
     */
    public function getDumpContent(){
        $fileArr = array();
        $content = array();
        /** @var \TYPO3\Deployment\Service\FileService $fieService */
        $fileService = new FileService();
        
        $path = $fileService->getDeploymentBBDeploymentPathWithTrailingSlash();
        $fileList = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $path);
        
        foreach($fileList as $file){
            $contentStr = file_get_contents($file);
        }
        
        $contentArr = explode('DROP', $contentStr);
        foreach($contentArr as $con){
            if(!empty($con) || $con != ''){
                $content[] = 'DROP'.$con;
            }
        }
        
        foreach($content as $con){
            $replaces = array('= @@character_set_client', '= utf8', '= @saved_cs_client', 'SET @saved_cs_client','SET character_set_client','/*!40101', '*/;');
            $newContent[] = str_replace($replaces, '', $con);
        }
        
        return $newContent;
    }

    
    /**
     * Creates the database dump and inserts into the davelopment/integration database
     */
    public function createDbDump(){
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        /** @var \TYPO3\Deployment\Service\FileService $fieService */
        $fileService = new FileService();
        
        // TODO: Entkommentieren
        //$fileService->deleteXmlFileDirectory();
        //$fileService->deleteDbDumpDirectory();

        $mysqldumpPath = $configurationService->getMysqldumpPath();
        $tablelist = $this->getTableList();
        
        CommandUtility::exec('cd "'.$mysqldumpPath.'"');
        CommandUtility::exec('mysqldump --compact --opt --skip-diable-keys --skip-comments --user='.$this->username.' --password='.$this->password.' --database '.$this->databaseName.' --result-file="'.$fileService->getDeploymentBBDeploymentPathWithTrailingSlash().$this->databaseName.'.sql" --tables '.$tablelist);
        
        if($this->checkIfDbDumpExists()){
            CommandUtility::exec("mysql --user=$this->username --password=$this->password");
            CommandUtility::exec("use database ".TYPO3_db);
            
            foreach($this->getDumpContent() as $command){
                CommandUtility::exec("$command");
            }
            
            CommandUtility::exec("exit");
        }
    }

    
    /**
     * Returns a list of tables without caching tables
     * 
     * @return string
     */
    protected function getTableList(){
        $list = '';
        /** @var \TYPO3\Deployment\Service\ConfigurationService $confService */
        $confService = new ConfigurationService();
        
        $this->getDatabase()->setDatabaseHost($this->mysqlServer);
        $this->getDatabase()->setDatabaseName($this->databaseName);
        $this->getDatabase()->setDatabaseUsername($this->username);
        $this->getDatabase()->setDatabasePassword($this->password);
        $this->getDatabase()->connectDB();
        
        if($this->getDatabase()->isConnected()){
            $tableprop = $this->getDatabase()->admin_get_tables();
            foreach($tableprop as $key => $value) {
                if(strstr($key, 'cache') == FALSE && strstr($key, 'cf_') == FALSE && !in_array($key, $confService->getNotDeployableTables())) {
                    $list .= $key . ' ';
                }
            }
        }
        
        DatabaseService::reset();
        
        return trim($list);
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
    public function getDatabaseName() {
        return $this->databaseName;
    }

    /**
     * @param string $database
     */
    public function setDatabaseName($databaseName) {
        $this->databaseName = $databaseName;
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