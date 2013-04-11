<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


$TCA['sys_file'] = array(
    'columns' => array(),
);

$fields = array(
    'uid',
    'pid',
    'tstamp',
    'type',
    'storage',
    'identifier',
    'mime_type',
    'size',
    'creation_date',
    'modification_date',
    'width',
    'height'
);


foreach ($fields as $field) {
    $TCA['sys_file']['columns'][$field] = array(
        'exclude' => 0,
        'label' => $field,
        'config' => array(
            'type' => 'input',
        ),
    );
}