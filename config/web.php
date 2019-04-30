<?php
use \yii\web\Request;
$baseUrl = str_replace('/web', '', (new Request)->getBaseUrl());
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'name' => 'API SERVICE UDON CITY',
    # ตั้งค่าการใช้งานภาษาไทย (Language)
    'language' => 'th', // ตั้งค่าภาษาไทย
    # ตั้งค่า TimeZone ประเทศไทย
    'timeZone' => 'Asia/Bangkok', // ตั้งค่า TimeZone
    //'sourceLanguage' => 'th-TH',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'aTD9H-_NG98tkeRimQlPAaig2XnpFBR8',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'baseUrl' => $baseUrl,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'assetManager' => [
            //'baseUrl' => '/api/assets',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\v1\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
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
                    ]
                ],
            ],
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->format == 'html') {
                    return $response;
                }
                $responseData = $response->data;
                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }
                if ($response->statusCode >= 200 && $response->statusCode <= 299) {
                    $response->data = [
                        'success' => true,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                } else {
                    $response->data = [
                        'success' => false,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                }
                return $response;
            },
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@app/modules/user/views'
                ],
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
            'dateFormat' => 'php:Y-m-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'timeFormat' => 'php:H:i:s',
            'defaultTimeZone' => 'Asia/Bangkok',
            'timeZone' => 'Asia/Bangkok'
        ],
        'nhso' => [
            'class' => 'mongosoft\soapclient\Client',
            'url' => 'http://ucws.nhso.go.th/ucwstokenp1/UCWSTokenP1?WSDL',
            'options' => [
                'cache_wsdl' => WSDL_CACHE_NONE
            ],
        ],
        'db2' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=db;dbname=api_udh;port=3306',
            'username' => 'root',
            'password' => 'root_db',
            'charset' => 'utf8',
        ]
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableUnconfirmedLogin' => true,
            //'enableRegistration' => false,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => true,
            'confirmWithin' => 21600,
            'cost' => 12,
            'admins' => ['admin'],
            'urlPrefix' => 'auth',
            'modelMap' => [
                'User' => 'app\modules\v1\models\User',
                'Profile' => 'app\modules\v1\models\Profile',
                'RegistrationForm' => 'app\modules\v1\models\RegistrationForm',
                'LoginForm' => 'app\modules\v1\models\LoginForm',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
