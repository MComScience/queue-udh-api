<?php

use \yii\web\Request;

$baseUrl = str_replace('/web', '', (new Request)->getBaseUrl());
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$urlRules = require __DIR__ . '/rules.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@Mpdf' => '@app/lib/mpdf/src',
        '@common' => '@app/common',
    ],
    'name' => 'QUEUE UDON HOSPITAL',
    # ตั้งค่าการใช้งานภาษาไทย (Language)
    'language' => 'th', // ตั้งค่าภาษาไทย
    # ตั้งค่า TimeZone ประเทศไทย
    'timeZone' => 'Asia/Bangkok', // ตั้งค่า TimeZone
    //'sourceLanguage' => 'th-TH',
    'defaultRoute' => 'v1/',
    'controllerMap' => [
        'file-manager-elfinder' => [
            'class' => '\mihaildev\elfinder\Controller',
            'access' => ['@'],
            'disabledCommands' => ['netmount'],
            'roots' => [
                [
                    'baseUrl' => '@web',
                    'basePath' => '@webroot',
                    'path' => '/',
                    'access' => ['read' => 'Admin', 'write' => 'Admin']
                ]
            ]
        ]
    ],
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
            'class' => 'yii\rbac\PhpManager',
        ],
        'cache' => [
            // 'class' => 'yii\caching\FileCache',
            'class' => 'yii\redis\Cache',
            'redis' => 'redis'
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
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
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'andamandev888@gmail.com',
                'password' => 'b8888888',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
                /* [
                    'class' => 'yii\log\EmailTarget',
                    'mailer' => 'mailer',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                        'from' => ['andamandev888@gmail.com'],
                        'to' => ['mcomsciencermu@gmail.com'],
                        'subject' => 'Log error ระบบคิว รพ อุดรธานี',
                    ],
                ], */
            ],
        ],
        'db' => $db,
        'db_mssql' => [
            'class' => 'yii\db\Connection',
            //'dsn' => 'sqlsrv:Server=192.168.0.1;Database=UDON2',
            'dsn' => 'sqlsrv:Server=M_COMSCIENCE-NB\SQLEXPRESS;Database=udh_test',
            'username' => 'sa',
            'password' => 'b8888888',
            'charset' => 'utf8',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => $urlRules
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->format !== \yii\web\Response::FORMAT_JSON) {
                    return $response;
                }
                $responseData = $response->data;
                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }
                if ($response->statusCode >= 200 && $response->statusCode <= 299) {
                    if (isset($responseData['output'])) {
                        $response->data = $responseData;
                    } else {
                        $response->data = [
                            'success' => true,
                            'status' => $response->statusCode,
                            'data' => $responseData,
                        ];
                    }
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
            'class' => 'mcomscience\soapclient\Client',
            'url' => 'http://ucws.nhso.go.th/ucwstokenp1/UCWSTokenP1?WSDL',
            'options' => [
                'cache_wsdl' => WSDL_CACHE_NONE
            ],
        ],
        'db2' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=api_udh;port=3307',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'logger' => [
            'class' => 'app\components\Logger',
            'log_name' => 'api-log'
        ],
        'notify' => [
            'class' => 'app\components\LineNotify'
        ],
        'glide' => [
            'class' => 'trntv\glide\components\Glide',
            'sourcePath' => '@app/web/uploads',
            'cachePath' => '@runtime/glide',
            'signKey' => false // "false" if you do not want to use HTTP signatures
        ],
        'keyStorage' => [
            'class' => '\common\components\keystorage\KeyStorage',
        ],
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
            'controllerMap' => [
                'admin' => 'app\modules\user\controllers\AdminController'
            ],
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module',
            'downloadAction' => '/user/admin/export',
        ],
        'settings' => [
            'class' => 'app\modules\settings\Module',
        ],
        'file' => [
            'class' => 'app\modules\file\Module',
        ],
        'system' => [
            'class' => 'common\modules\system\Module',
        ],
        'audit' => [
            'class' => 'bedezign\yii2\audit\Audit',
            'accessRoles' => ['@'],
            'userIdentifierCallback' => ['app\modules\v1\models\User'],
            'ignoreActions' => ['audit/*', 'debug/*', 'site/*'],
            'maxAge' => 7
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    /*$config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];*/

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
