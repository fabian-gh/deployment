<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


$TCA['sys_file'] = array(
    'columns' => array(),
);

$fields = array(
    'uid'
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