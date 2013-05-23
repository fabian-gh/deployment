<?php
/**
 * $EM_CONF
 *
 * @category   Extension
 * @package    Deployment
 * @author     Fabian Martinovic <fabian.martinovic@t-online.de>
 */


$EM_CONF[$_EXTKEY] = array(
	'title' => 'Deployment',
	'description' => 'Mit dieser Extension wird es möglich sowohl Dateien als auch Daten zu deployen.',
	'category' => '',
	'shy' => 0,
	'version' => '2.0.0',
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'loadOrder' => '',
	'module' => '',
	'priority' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => 'fileadmin/deployment, fileadmin/deployment/database, fileadmin/deployment/media, fileadmin/deployment/resource',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Fabian Martinovic',
	'author_email' => 'fabian.martinovic@t-online.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>