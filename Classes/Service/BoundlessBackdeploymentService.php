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
     * Check if a path is defined and not empty
     * 
     * @return boolean
     */
    public function checkIfMysqldumpPathIsNotEmpty(){
        $path = $this->getMysqlBinariesPath();

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
            if(strstr($file, TYPO3_db.'.sql') !== FALSE){
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
     * Creates the database dump and save it
     */
    public function executeDumpCreation(){
        /** @var \TYPO3\Deployment\Service\FileService $fieService */
        $fileService = new FileService(); 
        
        // TODO: Entkommentieren
        // delete old files
        //$fileService->deleteXmlFileDirectory();
        //$fileService->deleteDbDumpDirectory();
        
        $tablelist = $this->getTableList();
        
        CommandUtility::exec('cd "'.$this->getMysqlBinariesPath().'"');
        CommandUtility::exec('sudo mysqldump --compact --opt --skip-disable-keys --skip-comments --user='.$this->getCurrentDatabaseUser().' --password='.$this->getCurrentDatabasePassword().' --database '.$this->getCurrentDatabaseName().' --result-file="'.$fileService->getDeploymentBBDeploymentPathWithTrailingSlash().$this->getCurrentDatabaseName().'.sql" --tables '.$tablelist);
    }
    
    
    /**
     * Execute inserting of data from the database dump
     */
    public function executeDumpInsertion(){
        /** @var \TYPO3\Deployment\Service\FileService $fieService */
        $fileService = new FileService(); 
        
        if($this->checkIfDbDumpExists()){
            // execute command from database compare and write to file for future inserting
            foreach($this->getDatabaseIntegrity() as $aCArr){
                foreach($aCArr as $change){
                    file_put_contents($fileService->getDeploymentBBDeploymentPathWithTrailingSlash().'changes.sql', $change.LF, FILE_APPEND);
                }
            }
            
            CommandUtility::exec('cd "'.$this->getMysqlBinariesPath().'"');
            CommandUtility::exec('mysql --user='.$this->getCurrentDatabaseUser().' --password='.$this->getCurrentDatabasePassword().' '.$this->getCurrentDatabaseName().' <'.$fileService->getDeploymentBBDeploymentPathWithTrailingSlash().$this->getCurrentDatabaseName().'.sql');
            CommandUtility::exec('mysql --user='.$this->getCurrentDatabaseUser().' --password='.$this->getCurrentDatabasePassword().' '.$this->getCurrentDatabaseName().' <'.$fileService->getDeploymentBBDeploymentPathWithTrailingSlash().'changes.sql');
            
            // TODO: Entkommentieren
            // check if file exists
            //$fileService->fileChecker($this->resourceServer);
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
        
        if($this->getDatabase()->isConnected()){
            $tableprop = $this->getDatabase()->admin_get_tables();
            foreach($tableprop as $key => $value) {
                if(strstr($key, 'cache') == FALSE && 
                    strstr($key, 'cf_') == FALSE && 
                    strstr($key, 'be_users') == FALSE &&
                    strstr($key, 'fe_users') == FALSE &&
                    !in_array($key, $confService->getNotDeployableTables())) {
                    $list .= $key . ' ';
                }
            }
        }
        
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
        $diff1 = $shema->getDatabaseExtra($FDdb, $FDfile);
        $remove_statements = $shema->getUpdateSuggestions($diff1, 'remove');

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
    
    
    /**
     * Returns the mysqldump path
     * 
     * @return string
     */
    protected function getMysqlBinariesPath(){
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configurationService */
        $configurationService = new ConfigurationService();
        
        return $configurationService->getMysqlBinariesPath();
    }
}