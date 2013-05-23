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
    'crdate',
    'type',
    'storage',
    'identifier',
    'extension',
    'mimeType',
    'name',
    'title',
    'sha1',
    'size',
    'creationDate',
    'modificationDate',
    'width',
    'height',
    'description',
    'alternative',
    'uuid'
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