<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\web\Response;
use app\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use app\modules\v1\models\User;
use app\modules\v1\models\Profile;
use dektrium\user\models\SettingsForm;
use yii\web\UploadedFile;
use yii\helpers\Url;
use Intervention\Image\ImageManagerStatic;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

class UserController extends ActiveController
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
        //unset($actions['delete'], $actions['create'], $actions['update']);
        
        //$actions['index']['users'] = [$this, 'users'];

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
                'update' => ['get', 'post'],
                'delete' => ['delete'],
                'profile' => ['get','post'],
                'upload-avatar' => ['post'],
                'account' => ['post'],
                'update-user' => ['get', 'post'],
                'delete-user' => ['delete'],
                'pt-right' => ['get'],
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
        $behaviors['authenticator']['except'] = ['options','upload-avatar', 'pt-right'];
        // setup access
        $behaviors['access'] = [
	        'class' => AccessControl::className(),
	        'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
	        'rules' => [
		        [
			        'allow' => true,
			        'actions' => ['index', 'view', 'create', 'update', 'delete', 'profile', 'account', 'delete-user'],
			        'roles' => ['@'],
		        ],
	        ],
        ];
        return $behaviors;
    }

    public function actionProfile()
    {
        $request = Yii::$app->request;
        $model = Profile::find()->where(['user_id' => \Yii::$app->user->identity->getId()])->one();
        $account = \Yii::createObject(SettingsForm::className());
        if($request->isGet){
            return [
                'profile' => $model,
                'account' => $account,
                'user' => $model->user
            ];
        } else {

            if ($model == null) {
                $model = \Yii::createObject(Profile::className());
                $model->link('user', \Yii::$app->user->identity);
            }
            $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
            $avatar = $request->post('avatar');
            if($avatar){
                $decode = Json::decode($avatar);
                $decode[0]['url'] = Url::base(true).'/uploads/avatar/'.$decode[0]['name'];
                $avatar = Json::encode($decode);
            }
            $model->user->avatar = $avatar;
            if ($model->validate() && $model->save() && $model->user->save()) {
                return [
                    'message' => \Yii::t('user', 'Your profile has been updated'),
                    'user' => $model
                ];
            } else {
                // Validation error
                throw new HttpException(400, Json::encode($model->errors));
            }
        }
    }

    public function actionAccount()
    {
        /** @var SettingsForm $model */
        $model = \Yii::createObject(SettingsForm::className());
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            return [
                'message' => \Yii::t('user', 'Your account details have been updated'),
                'user' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(400, Json::encode($model->errors));
        }
    }

    public function actionUpdateUser($id)
    {
        $request = Yii::$app->request;
        $user = $this->findModel($id);
        $user->scenario = 'update';
        $profile = $user->profile;

        if ($profile == null) {
            $profile = \Yii::createObject(Profile::className());
            $profile->link('user', $user);
        }

        if($request->isGet){
            return [
                'user' => $user,
                'profile' => $profile
            ];
        } else {
            $user->load(\Yii::$app->getRequest()->getBodyParams(), '');
            if(!empty($request->post('role'))){
                $user->role = ($request->post('role') == 'Admin') ? 20 : 10;
            }
            $profile->load(\Yii::$app->getRequest()->getBodyParams(), '');
            if ($user->validate() && $user->save() && $profile->validate() && $profile->save()) {
                return [
                    'message' => \Yii::t('user', 'Account details have been updated'),
                    'user' => $user,
                    'profile' => $profile
                ];
            } else {
                // Validation error
                throw new HttpException(400, ArrayHelper::merge(ActiveForm::validate($profile), ActiveForm::validate($user)));
            }
        }
    }

    public function actionUploadAvatar()
    {
        $request = Yii::$app->request;
        $files = UploadedFile::getInstanceByName('avatar');
        $path = 'uploads/avatar/' . $files->baseName . '.' . $files->extension;
        if($files->saveAs('uploads/avatar/' . $files->baseName . '.' . $files->extension)){
            $manager = ImageManagerStatic::make($path)->fit(215, 215);
            FileHelper::unlink($path);
            if($manager->save($path)){
                return $files;
            }
        }
    }

    protected function findModel($id)
    {
        
        $user = User::findOne($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested page does not exist');
        }
        return $user;
    }

    public function actionDeleteUser($id)
    {
        if ($id == \Yii::$app->user->getId()) {
            throw new HttpException(400, \Yii::t('user', 'You can not remove your own account'));
        } else {
            $model = $this->findModel($id);
            $model->delete();
            return [
                'message' => \Yii::t('user', 'User has been deleted')
            ];
        }
    }

    public function actionPtRight($cid)
    {
        $client = Yii::$app->nhso;
        $sql = "SELECT * FROM nhso_token ORDER BY updated_at DESC";
        $userToken = \Yii::$app->db2->createCommand($sql)->queryOne();
        $params = array(
            'user_person_id' => $userToken ? $userToken['token_cid'] : '',
            'smctoken' => $userToken ? $userToken['token_key'] : '',
            'person_id' => $cid
        );
        $res = $client->searchCurrentByPID($params);
        $data = $res['return'];
        if (!$data) {
            $data = ['status-system' => 'error', 'message' => 'RESPONSE FAILED'];
        } else if ($data['ws_status'] == 'NHSO-00003') {
            $data = ['status-system' => 'error', 'message' => (isset($data['ws_status_desc']) ? $data['ws_status_desc'] : 'TOKEN EXPIRE')];
        } else if (empty($data['fname'])) {
            $data = ['status-system' => 'error', 'message' => 'NOT FOUND IN NHSO'];
        }
        return $data;
    }
}