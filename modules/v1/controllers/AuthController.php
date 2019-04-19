<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\web\Response;
use app\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use app\modules\v1\models\LoginForm;
use yii\web\HttpException;
use yii\helpers\Json;
use app\modules\v1\models\RegistrationForm;
use dektrium\user\models\RecoveryForm;
use app\modules\v1\models\User;

class AuthController extends ActiveController
{
    public $modelClass = 'app\modules\v1\models\User';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
	{
	    $actions = parent::actions();

	    // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create'], $actions['update']);
        
        $actions['index']['users'] = [$this, 'users'];

	    return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'login' => ['post'],
                'register' => ['post'],
                'logout' => ['post'],
                'forgot' => ['post'],
            ],
        ];
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Max-Age' => 3600,
            ],
        ];
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options','login', 'register', 'logout', 'forgot'];
        // setup access
        $behaviors['access'] = [
	        'class' => AccessControl::className(),
	        'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
	        'rules' => [
		        [
			        'allow' => true,
			        'actions' => ['index', 'view', 'create', 'update', 'delete'],
			        'roles' => ['@'],
		        ],
	        ],
        ];
        return $behaviors;
    }

    public function actionLogin(){
        $model = \Yii::createObject(LoginForm::className());
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->login()) {
            $user = $model->getUser();
            $user->generateAccessTokenAfterUpdatingClientInfo(true);

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $responseData = [
                'token' => $user->access_token,
                //'expires_in'  =>  28800,
            ];

            return $responseData;
        } else {
            // Validation error
            throw new HttpException(400, Json::encode($model->errors));
        }
    }

    public function actionLogout()
    {
        $responseData = [
            'status' => 'OK',
        ];
        return $responseData;
    }

    public function actionRegister()
    {
        /** @var RegistrationForm $model */
        $model = \Yii::createObject(RegistrationForm::className());

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->register()) {
            $responseData = [
                'message' => \Yii::t('user', 'Your account has been created'),
                'user'  =>  $model,
            ];
            return $responseData;
        } else {
            // Validation error
            throw new HttpException(400, Json::encode($model->errors));
        }
    }

    public function actionForgot()
    {
        if(!User::findAll(['email' => Yii::$app->request->post('email')])){
            throw new HttpException(400, 'ไม่พบอีเมลในระบบ');
        }
        /** @var RecoveryForm $model */
        $model = \Yii::createObject([
            'class'    => RecoveryForm::className(),
            'scenario' => RecoveryForm::SCENARIO_REQUEST,
        ]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->sendRecoveryMessage()) {
            return [
                'message'  => \Yii::t('user', 'Recovery message sent'),
            ];
        } else {
            // Validation error
            throw new HttpException(400, Json::encode($model->errors));
        }
    }
}