<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'name',
        'iconfile' => 'EXT:jw_roles_and_contacts/Resources/Public/Icons/tx_jwrolesandcontacts_domain_model_person.svg',
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_language.hidden',
            'config' => ['type' => 'check'],
        ],
        'name' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'image' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person.image',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
                'maxitems' => 1,
            ],
        ],
        'address' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person.address',
            'config' => [
                'type' => 'text',
                'rows' => 3,
                'cols' => 30,
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person.email',
            'config' => [
                'type' => 'email',
            ],
        ],
        'phone' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person.phone',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
        'mobile' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person.mobile',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
        'fax' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_person.fax',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => 'name, image, address, --linebreak--, email, phone, mobile, fax',
        ],
    ],
];
