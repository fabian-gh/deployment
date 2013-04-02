<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


/** @var $autoLoader \TYPO3\Deployment\Service\AutoLoaderService */
$autoLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Deployment\\Service\\AutoLoaderService', 'deployment');
$autoLoader
	->loadExtensionLocalConfigurationSlots()
	->loadExtensionLocalConfigurationXclasses()
	->loadExtensionLocalConfigurationHooks()
	->loadExtensionLocalConfigurationCommandController();



/**
 * xClass -
 * -- nur einmalig benutzbar, da mehrere Klassen die das Original erweitern, im Konflikt zueinander stehen
 * -- Bedingung (nicht mehr der komische include in der letztern Zeile) sondern nur noch, dass das Zielobjekt über die TYPO3 ojeckt factory erzeugt wird
 * --- überall
 *
 * Hook
 * --- beliebig oft benutzbar (array)
 * --- nicht standadisiert!!!
 * --- nur an vorgesheenden Stellen
 *
 *  Signal/Slot pattern
 * --- beliebig oft benutzbar
 * --- standadisiert
 * --- nur an vorgesheenden Stellen
 *
 * signal(__CLASS__, __METHOD__, array(...));
 *
 * jeder:
 * slot('Classname', 'methoden', __CLASS__, __MEDTHOD__);
 *
 *
 *
 *
 *
 *
 *
 */
