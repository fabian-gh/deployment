<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


$TCA['sys_file_reference'] = array(
    'columns' => array(),
);

$fields = array(
    'uid',
    'pid',
    'tstamp',
    'crdate',
    'uid_local',
    'uid_foreign',
    'tablenames',
    'fieldname',
    'table_local',
    'title',
    'description',
    'alternative',
    'link'
);


foreach ($fields as $field) {
    $TCA['sys_file_reference']['columns'][$field] = array(
        'exclude' => 0,
        'label' => $field,
        'config' => array(
            'type' => 'input',
        ),
    );
}