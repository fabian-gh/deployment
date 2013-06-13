<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/*$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['TYPO3\Deployment\Scheduler\CopyTask'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Dateien kopieren',
    'description'      => 'Dateien größer der festgelegten Grenze kopieren.'
);*/


/** @var $autoLoader \TYPO3\Deployment\Service\AutoLoaderService */
$autoLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Deployment\\Service\\AutoLoaderService', 'deployment');
$autoLoader
	->loadExtensionLocalConfigurationHooks()
	->loadExtensionLocalConfigurationCommandController();

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters'][] = 'TYPO3\\Deployment\\Property\\TypeConverter\\ArrayConverter';