<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


$TCA['sys_history'] = array(
    'columns' => array(),
);

$fields = array(
    'sys_log_uid',
    'history_data',
    'fieldlist',
    'recuid',
    'tablename',
    'tstamp'
);


foreach ($fields as $field) {
    $TCA['sys_history']['columns'][$field] = array(
        'exclude' => 0,
        'label' => $field,
        'config' => array(
            'type' => 'input',
        ),
    );
}