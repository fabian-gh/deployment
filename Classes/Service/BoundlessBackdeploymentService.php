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
use \TYPO3\CMS\Core\Core\Bootstrap;
use \TYPO3\CMS\Core\Cache\Cache;
use \TYPO3\CMS\Core\Category\CategoryRegistry;

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
    protected $resourceServer;
    
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
    public function init($resourceServer, $mysqlServer, $databaseName, $username, $password){
        $this->setResourceServer($resourceServer);
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
     * Creates the database dump and inserts into the davelopment/integration 
     * database. Also executes the file checker
     */
    public function executeBoundlessBackdeployment(){
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        /** @var \TYPO3\Deployment\Service\FileService $fieService */
        $fileService = new FileService(); 
        
        $fileService->fileChecker($this->resourceServer);die();
        
        // TODO: Entkommentieren
        // delete old files
        //$fileService->deleteXmlFileDirectory();
        //$fileService->deleteDbDumpDirectory();

        $mysqldumpPath = $configurationService->getMysqldumpPath();
        $tablelist = $this->getTableList();
        
        CommandUtility::exec('cd "'.$mysqldumpPath.'"');
        CommandUtility::exec('mysqldump --compact --opt --skip-diable-keys --skip-comments --user='.$this->username.' --password='.$this->password.' --database '.$this->databaseName.' --result-file="'.$fileService->getDeploymentBBDeploymentPathWithTrailingSlash().$this->databaseName.'.sql" --tables '.$tablelist);
        
        if($this->checkIfDbDumpExists()){
            CommandUtility::exec("mysql --user=$this->username --password=$this->password");
            CommandUtility::exec("use database ".TYPO3_db);
            
            // execute command from databse dump
            foreach($this->getDumpContent() as $command){
                CommandUtility::exec("$command");
            }
            
            // execute command from databse compare
            $addChangeArray = $this->getDatabaseIntegrity();
            foreach($addChangeArray as $aCArr){
                foreach($aCArr as $change){
                    CommandUtility::exec("$change");
                }
            }
            
            CommandUtility::exec("exit");
            
            //$fileService->fileChecker();
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
    
    
    /**
     * Get database integrity information
     *
     * @return array
     * @throws \UnexpectedValueException
     * @see typo3/sysext/install/Classes/Installer.php
     */
    protected function getDatabaseIntegrity() {
        /** @var \TYPO3\CMS\Install\Sql\SchemaMigrator $shema */
        $shema = GeneralUtility::makeInstance('TYPO3\\CMS\\Install\\Sql\\SchemaMigrator');
        /** @var \TYPO3\Deployment\Service\DummyInstaller $installer */
        $installer = GeneralUtility::makeInstance('TYPO3\\Deployment\\Service\\DummyInstaller');

        $hookObjects = array();
        // Load TCA first
        Bootstrap::getInstance()->loadExtensionTables(FALSE);

        // check hooks
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install/mod/class.tx_install.php']['checkTheDatabase'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install/mod/class.tx_install.php']['checkTheDatabase'] as $classData) {
                $hookObject = GeneralUtility::getUserObj($classData);
                if (!$hookObject instanceof \TYPO3\CMS\Install\CheckTheDatabaseHookInterface) {
                    throw new \UnexpectedValueException('$hookObject must implement interface TYPO3\\CMS\\Install\\CheckTheDatabaseHookInterface', 1315554770);
                }
                $hookObjects[] = $hookObject;
            }
        }
        
        // load information from tables.sql
        $tblFileContent = GeneralUtility::getUrl(PATH_t3lib . 'stddb/tables.sql');
        foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $extKey => $loadedExtConf) {
            if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql']) {
                $extensionSqlContent = GeneralUtility::getUrl($loadedExtConf['ext_tables.sql']);
                $tblFileContent .= LF . LF . LF . LF . $extensionSqlContent;
                
                foreach ($hookObjects as $hookObject) {
                    $appendableTableDefinitions = $hookObject->appendExtensionTableDefinitions($extKey, $loadedExtConf, $extensionSqlContent, $shema, $installer);
                    if ($appendableTableDefinitions) {
                        $tblFileContent .= $appendableTableDefinitions;
                        break;
                    }
                }
            }
        }
        
        foreach ($hookObjects as $hookObject) {
            $appendableTableDefinitions = $hookObject->appendGlobalTableDefinitions($tblFileContent, $shema, $installer);
            if ($appendableTableDefinitions) {
                $tblFileContent .= $appendableTableDefinitions;
                break;
            }
        }
        
        // Add SQL content coming from the caching framework
        $tblFileContent .= Cache::getDatabaseTableDefinitions();
        // Add SQL content coming from the category registry
        $tblFileContent .= CategoryRegistry::getInstance()->getDatabaseTableDefinitions();
        if (!$tblFileContent) {
            return array();
        }

        $fileContent = implode(LF, $shema->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
        
        $FDfile = $shema->getFieldDefinitions_fileContent($fileContent);
        $FDdb = $shema->getFieldDefinitions_database();
        $diff = $shema->getDatabaseExtra($FDfile, $FDdb);
        $update_statements = $shema->getUpdateSuggestions($diff);
        $diff = $shema->getDatabaseExtra($FDdb, $FDfile);
        $remove_statements = $shema->getUpdateSuggestions($diff, 'remove');

        $all = array_merge_recursive($update_statements, $remove_statements);

        $remove = array(
            'change_currentValue',
            'tables_count'
        );
        
        foreach ($remove as $r) {
            if (isset($all[$r])) {
                unset($all[$r]);
            }
        }
        
        return $all;
    }
    

    // ========================= Getter & Setter ===============================

    /**
     * @return string
     */
    public function getResourceServer() {
        return $this->resourceServer;
    }

    /**
     * @param string $resourceServer
     */
    public function setResourceServer($resourceServer) {
        if(substr($resourceServer, -1) == '/'){
            $this->resourceServer = substr($resourceServer, 0, -1);
        } else {
            $this->resourceServer = $resourceServer;
        }   
    }

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