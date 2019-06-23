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
            'GET services' => 'services',
            'OPTIONS services' => 'options',
            'GET priority' => 'priority',
            'OPTIONS priority' => 'options',
            'GET patient-register' => 'patient-register',
            'OPTIONS patient-register' => 'options',
            'GET dashboard' => 'dashboard',
            'OPTIONS dashboard' => 'options',
            'GET list-all' => 'list-all',
            'OPTIONS list-all' => 'options',
            'DELETE delete' => 'delete',
            'OPTIONS delete' => 'options',
            'POST update-patient' => 'update-patient',
            'OPTIONS update-patient' => 'options',
            'POST data-waiting' => 'data-waiting',
            'OPTIONS data-waiting' => 'options',
            'POST profile-service-options' => 'profile-service-options',
            'OPTIONS profile-service-options' => 'options',
            'POST call-wait' => 'call-wait',
            'OPTIONS call-wait' => 'options',
            'POST data-wait-by-hn' => 'data-wait-by-hn',
            'OPTIONS data-wait-by-hn' => 'options',
            'POST end-wait' => 'end-wait',
            'OPTIONS end-wait' => 'options',
            'POST data-caller' => 'data-caller',
            'OPTIONS data-caller' => 'options',
            'POST recall' => 'recall',
            'OPTIONS recall' => 'options',
            'POST hold' => 'hold',
            'OPTIONS hold' => 'options',
            'POST data-hold' => 'data-hold',
            'OPTIONS data-hold' => 'options',
            'POST end' => 'end',
            'OPTIONS end' => 'options',
            'POST call-hold' => 'call-hold',
            'OPTIONS call-hold' => 'options',
            'POST end-hold' => 'end-hold',
            'OPTIONS end-hold' => 'options',
            'POST call-selected' => 'call-selected',
            'OPTIONS call-selected' => 'options',
            'POST register-examination' => 'register-examination',
            'OPTIONS register-examination' => 'options',
            'POST data-waiting-examination' => 'data-waiting-examination',
            'OPTIONS data-waiting-examination' => 'options',
            'POST data-caller-examination' => 'data-caller-examination',
            'OPTIONS data-caller-examination' => 'options',
            'POST data-hold-examination' => 'data-hold-examination',
            'OPTIONS data-hold-examination' => 'options',
            'GET play-stations' => 'play-stations',
            'OPTIONS play-stations' => 'options',
            'GET get-play-station' => 'get-play-station',
            'OPTIONS get-play-station' => 'options',
            'POST update-call-status' => 'update-call-status',
            'OPTIONS update-call-status' => 'options',
            'GET display-list' => 'display-list',
            'OPTIONS display-list' => 'options',
            'GET get-display' => 'get-display',
            'OPTIONS get-display' => 'options',
            'GET queue-play-list' => 'queue-play-list',
            'OPTIONS queue-play-list' => 'options',
            'GET active-play-station' => 'active-play-station',
            'OPTIONS active-play-station' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/dashboard',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'GET count-services' => 'count-services',
            'OPTIONS count-services' => 'options',
            'GET pie-chart' => 'pie-chart',
            'OPTIONS pie-chart' => 'options',
            'GET column-chart' => 'column-chart',
            'OPTIONS column-chart' => 'options',
            'GET floor-chart' => 'floor-chart',
            'OPTIONS floor-chart' => 'options',
            'GET column-dept-chart' => 'column-dept-chart',
            'OPTIONS column-dept-chart' => 'options',
            'GET kiosk-chart' => 'kiosk-chart',
            'OPTIONS kiosk-chart' => 'options',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/settings',
        'pluralize' => false,
        'tokens' => [
            '{id}' => '<id:\d+>',
        ],
        'extraPatterns' => [
            'OPTIONS {id}' => 'options',
            'GET floor-list' => 'floor-list',
            'OPTIONS floor-list' => 'options',
            'PUT create-floor' => 'create-floor',
            'POST create-floor' => 'create-floor',
            'OPTIONS create-floor' => 'options',
            'GET update-floor' => 'update-floor',
            'POST update-floor' => 'update-floor',
            'OPTIONS update-floor' => 'options',
            'DELETE delete-floor' => 'delete-floor',
            'OPTIONS delete-floor' => 'options',
            'GET service-group-list' => 'service-group-list',
            'OPTIONS service-group-list' => 'options',
            'GET service-group-options' => 'service-group-options',
            'OPTIONS service-group-options' => 'options',
            'GET create-service-group' => 'create-service-group',
            'OPTIONS create-service-group' => 'options',
            'POST update-service-group' => 'update-service-group',
            'OPTIONS update-service-group' => 'options',
            'DELETE delete-service-group' => 'delete-service-group',
            'OPTIONS delete-service-group' => 'options',
            'POST save-service-group-order' => 'save-service-group-order',
            'OPTIONS save-service-group-order' => 'options',
            'GET service-list' => 'service-list',
            'OPTIONS service-list' => 'options',
            'GET service-options' => 'service-options',
            'OPTIONS service-options' => 'options',
            'POST create-service' => 'create-service',
            'OPTIONS create-service' => 'options',
            'POST update-service' => 'update-service',
            'OPTIONS update-service' => 'options',
            'DELETE delete-service' => 'delete-service',
            'OPTIONS delete-service' => 'options',
            'POST save-service-order' => 'save-service-order',
            'OPTIONS save-service-order' => 'options',
            'GET kiosk-list' => 'kiosk-list',
            'OPTIONS kiosk-list' => 'options',
            'GET kiosk-options' => 'kiosk-options',
            'OPTIONS kiosk-options' => 'options',
            'POST create-kiosk' => 'create-kiosk',
            'OPTIONS create-kiosk' => 'options',
            'POST update-kiosk' => 'update-kiosk',
            'OPTIONS update-kiosk' => 'options',
            'DELETE delete-kiosk' => 'delete-kiosk',
            'OPTIONS delete-kiosk' => 'options',
            'GET card-list' => 'card-list',
            'OPTIONS card-list' => 'options',
            'GET create-card' => 'create-card',
            'OPTIONS create-card' => 'options',
            'GET update-card' => 'update-card',
            'POST update-card' => 'update-card',
            'OPTIONS update-card' => 'options',
            'DELETE delete-card' => 'delete-card',
            'OPTIONS delete-card' => 'options',
            'GET profile-service-list' => 'profile-service-list',
            'OPTIONS profile-service-list' => 'options',
            'GET profile-service-options' => 'profile-service-options',
            'OPTIONS profile-service-options' => 'options',
            'PUT create-profile-service' => 'create-profile-service',
            'POST create-profile-service' => 'create-profile-service',
            'OPTIONS create-profile-service' => 'options',
            'POST update-profile-service' => 'update-profile-service',
            'OPTIONS update-profile-service' => 'options',
            'DELETE delete-profile-service' => 'delete-profile-service',
            'OPTIONS delete-profile-service' => 'options',
            'GET counter-list' => 'counter-list',
            'OPTIONS counter-list' => 'options',
            'PUT create-counter' => 'create-counter',
            'POST create-counter' => 'create-counter',
            'OPTIONS create-counter' => 'options',
            'POST update-counter' => 'update-counter',
            'OPTIONS update-counter' => 'options',
            'DELETE delete-counter' => 'delete-counter',
            'OPTIONS delete-counter' => 'options',
            'GET counter-service-list' => 'counter-service-list',
            'OPTIONS counter-service-list' => 'options',
            'PUT create-counter-service' => 'create-counter-service',
            'POST create-counter-service' => 'create-counter-service',
            'OPTIONS create-counter-service' => 'options',
            'POST update-counter-service' => 'update-counter-service',
            'OPTIONS update-counter-service' => 'options',
            'DELETE delete-counter-service' => 'delete-counter-service',
            'OPTIONS delete-counter-service' => 'options',
            'GET counter-service-options' => 'counter-service-options',
            'OPTIONS counter-service-options' => 'options',
            'GET play-station-list' => 'play-station-list',
            'OPTIONS play-station-list' => 'options',
            'PUT create-play-station' => 'create-play-station',
            'POST create-play-station' => 'create-play-station',
            'OPTIONS create-play-station' => 'options',
            'POST update-play-station' => 'update-play-station',
            'OPTIONS update-play-station' => 'options',
            'DELETE delete-play-station' => 'delete-play-station',
            'OPTIONS delete-play-station' => 'options',
            'GET play-station-options' => 'play-station-options',
            'OPTIONS play-station-options' => 'options',
            'GET display-list' => 'display-list',
            'OPTIONS display-list' => 'options',
            'GET display-options' => 'display-options',
            'OPTIONS display-options' => 'options',
            'PUT create-display' => 'create-display',
            'POST create-display' => 'create-display',
            'OPTIONS create-display' => 'options',
            'GET update-display' => 'update-display',
            'POST update-display' => 'update-display',
            'OPTIONS update-display' => 'options',
            'DELETE delete-display' => 'delete-display',
            'OPTIONS delete-display' => 'options',
            'GET auto-number-list' => 'auto-number-list',
            'OPTIONS auto-number-list' => 'options',
            'POST update-auto-number' => 'update-auto-number',
            'OPTIONS update-auto-number' => 'options',
        ]
    ],
];