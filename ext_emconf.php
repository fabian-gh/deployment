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
	'description' => 'With this extension you are able to deploy database entries from one to another system',
	'category' => '',
	'shy' => 0,
	'version' => '3.0.0',
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'loadOrder' => '',
	'module' => '',
	'priority' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => 'fileadmin/deployment, fileadmin/deployment/database, fileadmin/deployment/media, fileadmin/deployment/bbdeploy',
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