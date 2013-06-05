<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabian Martinovic <fabian.martinovic(at)t-online.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * DatabaseFields
 *
 * @category   Extension
 * @package    Deployment
 * @subpackage Service
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 */

namespace TYPO3\Deployment\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Install\tx_em_Install;
use TYPO3\CMS\Install\CheckTheDatabaseHookInterface;
use TYPO3\Deployment\Service\ConfigurationService;

/**
 * @todo       General class information
 *
 * @package    Deployment
 * @subpackage ...
 * @author     Fabian Martinovic <fabian.martinovic(at)t-online.de>
 * @hook TYPO3_CONF_VARS|SC_OPTIONS|ext/install/mod/class.tx_install.php|checkTheDatabase
 */
class DatabaseFields implements CheckTheDatabaseHookInterface {

    /**
     * template
     * @var string
     */
    protected $sqlUuidTemplate = '

CREATE TABLE ###TABLE### (
	uuid text(40) NOT NULL
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
        /** @var \TYPO3\Deployment\Service\ConfigurationService $configuration */
        $configuration = new ConfigurationService();
        $tables = $configuration->getDeploymentTables();
        
        $return = '';
        foreach ($tables as $table) {
            $return .= str_replace('###TABLE###', $table, $this->sqlUuidTemplate);
        }
        
        return $return;
    }
}
