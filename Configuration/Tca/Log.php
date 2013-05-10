<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


$TCA['sys_log'] = array(
    'columns' => array(),
);

$fields = array(
    'tstamp',
    'log_data',
    'action'
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

