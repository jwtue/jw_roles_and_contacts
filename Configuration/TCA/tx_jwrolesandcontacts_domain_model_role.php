<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'title',
        'iconfile' => 'EXT:jw_roles_and_contacts/Resources/Public/Icons/tx_jwrolesandcontacts_domain_model_role.svg',
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_language.hidden',
            'config' => ['type' => 'check'],
        ],
        'title' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'person' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.person',
            'description' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.person.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_jwrolesandcontacts_domain_model_person',
                'foreign_table_where' => 'ORDER BY tx_jwrolesandcontacts_domain_model_person.name ASC',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'address' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.address',
            'config' => [
                'type' => 'text',
                'rows' => 3,
                'cols' => 30,
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.email',
            'config' => [
                'type' => 'email',
            ],
        ],
        'phone' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.phone',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
        'mobile' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.mobile',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
        'fax' => [
            'label' => 'LLL:EXT:jw_roles_and_contacts/Resources/Private/Language/locallang_db.xlf:tx_jwrolesandcontacts_domain_model_role.fax',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => 'title, person, address, --linebreak--, email, phone, mobile, fax',
        ],
    ],
];
