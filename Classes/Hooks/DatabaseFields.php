<?php

/**
 * DatabaseFields
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */

namespace TYPO3\Deployment\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Install\tx_em_Install;
use TYPO3\CMS\Install\CheckTheDatabaseHookInterface;

/**
 * @todo       General class information
 *
 * @package    Hdnet
 * @subpackage ...
 * @author     Tim Lochmüller <tim.lochmueller@hdnet.de>
 * @hook TYPO3_CONF_VARS|SC_OPTIONS|ext/install/mod/class.tx_install.php|checkTheDatabase
 */
class DatabaseFields implements CheckTheDatabaseHookInterface {

    /**
     * template
     * @var string
     */
    protected $sqlUuidTemplate = '

CREATE TABLE ###TABLE### (
	uuid text(40) DEFAULT \'0\' NOT NULL
);

';

    /**
     * Hook that allows to dynamically extend the table definitions on a per extension base
     * for e.g. custom caches. The hook implementation may return table create strings that
     * will be respected by the install tool.
     *
     * @param string                                $extKey             : Extension key
     * @param array                                 $loadedExtConf      : The extension's configuration from $GLOBALS['TYPO3_LOADED_EXT']
     * @param string                                $extensionSqlContent: The content of the extensions ext_tables.sql
     * @param \TYPO3\CMS\Install\Sql\SchemaMigrator $instSqlObj         : Instance of the installer sql object
     * @param tx_em_Install                         $parent             : The calling parent object
     *
     * @return string Either empty string or table create strings
     */
    public function appendExtensionTableDefinitions($extKey, array $loadedExtConf, $extensionSqlContent, \TYPO3\CMS\Install\Sql\SchemaMigrator $instSqlObj, \TYPO3\CMS\Install\Installer $parent) {
        
    }

    /**
     * Hook that allows to dynamically extend the table definitions for the whole system
     * for e.g. custom caches. The hook implementation may return table create strings that
     * will be respected by the install tool.
     *
     * @param string                                $allSqlContent: The content of all relevant sql files
     * @param \TYPO3\CMS\Install\Sql\SchemaMigrator $instSqlObj   : Instance of the installer sql object
     * @param tx_em_Install                         $parent       : The calling parent object
     *
     * @return string Either empty string or table create strings
     */
    public function appendGlobalTableDefinitions($allSqlContent, \TYPO3\CMS\Install\Sql\SchemaMigrator $instSqlObj, \TYPO3\CMS\Install\Installer $parent) {
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deployment']);
        $tables = GeneralUtility::trimExplode(',', $configuration['deploymentTables'], TRUE);
        
        $return = '';
        foreach ($tables as $table) {
            $return .= str_replace('###TABLE###', $table, $this->sqlUuidTemplate);
        }

        return $return;
    }
}
