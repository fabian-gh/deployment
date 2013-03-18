<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


$TCA['sys_log'] = array(
    'columns' => array(),
);

$fields = array(
    'uid',
    'pid',
    'action',
    'tablename',
    'details',
    'tstamp'
);


foreach ($fields as $field) {
    $TCA['sys_log']['columns'][$field] = array(
        'exclude' => 0,
        'label' => $field,
        'config' => array(
            'type' => 'input',
        ),
    );
}

