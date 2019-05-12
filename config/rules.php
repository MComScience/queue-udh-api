<?php
return [
    '' => 'site/index',
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/auth',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'POST login' => 'login',
            'OPTIONS login' => 'options',
            'POST logout' => 'logout',
            'OPTIONS logout' => 'options',
            'POST register' => 'register',
            'OPTIONS register' => 'options',
            'POST forgot' => 'forgot',
            'OPTIONS forgot' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/user',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'GET profile' => 'profile',
            'POST profile' => 'profile',
            'OPTIONS profile' => 'options',
            'POST account' => 'account',
            'OPTIONS account' => 'options',
            'POST upload-avatar' => 'upload-avatar',
            'OPTIONS upload-avatar' => 'options',
            'GET index' => 'index',
            'OPTIONS index' => 'options',
            'GET update-user' => 'update-user',
            'POST update-user' => 'update-user',
            'OPTIONS update-user' => 'options',
            'DELETE delete-user' => 'delete-user',
            'OPTIONS delete-user' => 'options',
            'GET pt-right' => 'pt-right',
            'OPTIONS pt-right' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/queue',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'POST register' => 'register',
            'OPTIONS register' => 'options',
            'GET data-print' => 'data-print',
            'OPTIONS data-print' => 'options',
            'GET kiosk-list' => 'kiosk-list',
            'OPTIONS kiosk-list' => 'options',
            'GET departments' => 'departments',
            'OPTIONS departments' => 'options',
            'GET priority' => 'priority',
            'OPTIONS priority' => 'options',
            'GET patient-register' => 'patient-register',
            'OPTIONS patient-register' => 'options',
        ]
    ],
];