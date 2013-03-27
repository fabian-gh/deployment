<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule('TYPO3.' . $_EXTKEY, 'tools', 'DeploymentAdmin', '', array(
                                                                                                                      'Deployment' => 'index,deploy,createDeploy,list',
                                                                                                                 ), array(
                                                                                                                         'access' => 'user,group',
                                                                                                                         'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.png',
                                                                                                                         'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod_deployment.xml'
                                                                                                                    ));



$TCA['sys_log'] = array(
    'ctrl' => array(
            'title'             => 'Log',
            'adminOnly'         => 1,
            'rootLevel'         => 1,
            'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/Tca/Log.php'
    ),
);

$TCA['sys_history'] = array (
    'ctrl' => array(
        'title'             => 'History',
        'adminOnly'         => 1,
        'rootLevel'         => 1,
        'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/Tca/History.php'
    )
);

?>
