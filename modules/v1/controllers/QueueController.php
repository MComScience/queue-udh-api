<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\web\Response;
use app\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use app\modules\v1\models\TblQueue;
use yii\web\HttpException;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class QueueController extends ActiveController
{
    public $modelClass = 'app\modules\v1\models\TblQueue';

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
                'register' => ['post'],
                'data-print' => ['get']
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
        $behaviors['authenticator']['except'] = ['options', 'register', 'data-print'];
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

    public function actionRegister()
    {
        $request = Yii::$app->request;
        $model = new TblQueue();
        $user = $request->post('user');
        $department = $request->post('department');
        $model->setAttributes([
            'queue_hn' => $user['hn'],
            'fullname' => $user['fullname'],
            'department_code' => $department['dept_code'],
            'user_info' => Json::encode($user),
        ]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if($model->validate() && $model->save()) {
            return $model;
        } else {
            // Validation error
            throw new HttpException(400, Json::encode($model->errors));
        }
    }

    public function actionDataPrint($id)
    {
        $model = TblQueue::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist');
        }
        return [
            'queue' => $model,
            'department' => $model->department
        ];
    }
}