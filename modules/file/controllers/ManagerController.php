<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 13/5/2562
 * Time: 13:55
 */

namespace app\modules\file\controllers;

use alexantr\elfinder\ConnectorAction;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class ManagerController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /** @var string */
    //public $layout = '//clear';

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'connector' => [
                'class' => ConnectorAction::class,
                'options' => [
                    'disabledCommands' => ['netmount'],
                    'connectOptions' => [
                        'filter'
                    ],
                    'roots' => [
                        [
                            'driver' => 'LocalFileSystem',
                            'path' => Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR,
                            'URL' => Yii::getAlias('@web'),
                            'uploadDeny' => [
                                'text/x-php', 'text/php', 'application/x-php', 'application/php'
                            ],
                            'accessControl' => 'access',
                            'attributes' => [
                                [
                                    'pattern' => '!^/assets!',
                                    'hidden' => true
                                ],
                                [
                                    'pattern' => '!^/index.php!',
                                    'hidden' => true
                                ],
                                [
                                    'pattern' => '!^/index-test.php!',
                                    'hidden' => true
                                ]
                            ],
                        ],
                    ],
                ],
            ],
            'connector2' => [
                'class' => ConnectorAction::class,
                'options' => [
                    'disabledCommands' => ['netmount'],
                    'connectOptions' => [
                        'filter'
                    ],
                    'roots' => [
                        [
                            'driver' => 'LocalFileSystem',
                            'path' => Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR,
                            //'URL' => Yii::getAlias('@runtime'),
                            'uploadDeny' => [
                                'text/x-php', 'text/php', 'application/x-php', 'application/php'
                            ],
                            'accessControl' => 'access',
                            'attributes' => [
                                [
                                    'pattern' => '!^/assets!',
                                    'hidden' => true
                                ],
                                [
                                    'pattern' => '!^/index.php!',
                                    'hidden' => true
                                ],
                                [
                                    'pattern' => '!^/index-test.php!',
                                    'hidden' => true
                                ]
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRuntime()
    {
        return $this->render('runtime');
    }
}
