<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Roles and Contacts',
    'description' => 'Person and Role master data plus a Content Block contact card for club/organization structures.',
    'category' => 'plugin',
    'author' => 'Jonas Wolf',
    'author_email' => 'mail@jonaswolf.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => 1,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.99.99',
            'content_blocks' => '0.7.0-2.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
