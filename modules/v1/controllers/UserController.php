<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
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
use yii\data\ActiveDataProvider;
use app\filters\AccessRule;

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
                QueryParamAuth::className()
            ],

        ];
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['get', 'post'],
                'delete' => ['delete'],
                'profile' => ['get', 'post'],
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
        $behaviors['authenticator']['except'] = ['options', 'upload-avatar'];
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'pt-right', 'profile', 'account', 'delete-user'], //only be applied to
            'ruleConfig' => [
                'class' => AccessRule::className()
            ],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['view', 'update', 'profile', 'account'],
                    'roles' => [
                        User::ROLE_ADMIN,
                        USER::ROLE_USER,
                        User::ROLE_KIOSK,
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'create', 'delete', 'delete-user'],
                    'roles' => [User::ROLE_ADMIN]
                ],
                [
                    'allow' => true,
                    'actions' => ['pt-right'],
                    'roles' => [
                        User::ROLE_ADMIN,
                        User::ROLE_KIOSK,
                        USER::ROLE_USER
                    ]
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => [
                'pageSize' => false,
            ],
        ]);
    }

    public function actionProfile()
    {
        $request = Yii::$app->request;
        $model = Profile::find()->where(['user_id' => \Yii::$app->user->identity->getId()])->one();
        $account = \Yii::createObject(SettingsForm::className());
        if ($request->isGet) {
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
            if ($avatar) {
                $decode = Json::decode($avatar);
                $decode[0]['url'] = Url::base(true) . '/uploads/avatar/' . $decode[0]['name'];
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
                throw new HttpException(422, Json::encode($model->errors));
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
            throw new HttpException(422, Json::encode($model->errors));
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

        if ($request->isGet) {
            return [
                'user' => $user,
                'profile' => $profile
            ];
        } else {
            $user->load(\Yii::$app->getRequest()->getBodyParams(), '');
            if ($request->post('role')) {
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
                throw new HttpException(422, ArrayHelper::merge(ActiveForm::validate($profile), ActiveForm::validate($user)));
            }
        }
    }

    public function actionUploadAvatar()
    {
        $request = Yii::$app->request;
        $files = UploadedFile::getInstanceByName('avatar');
        $path = 'uploads/avatar/' . $files->baseName . '.' . $files->extension;
        if ($files->saveAs('uploads/avatar/' . $files->baseName . '.' . $files->extension)) {
            $manager = ImageManagerStatic::make($path)->fit(215, 215);
            FileHelper::unlink($path);
            if ($manager->save($path)) {
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
        $logger = Yii::$app->logger->getLogger();
        if ($id == \Yii::$app->user->getId()) {
            $logger->info('Delete User', ['msg' => \Yii::t('user', 'You can not remove your own account'), 'id' => $id]);
            throw new HttpException(422, \Yii::t('user', 'You can not remove your own account'));
        } else {
            $logger->info('Delete User', ['msg' => \Yii::t('user', 'User has been deleted'), 'id' => $id]);
            $model = $this->findModel($id);
            $model->delete();
            return [
                'message' => \Yii::t('user', 'User has been deleted')
            ];
        }
    }

    public function actionPtRight($cid)
    {
        $logger = Yii::$app->logger->getLogger();
        /* $json = <<<JSON
    {
        "birthdate": "24860301",
        "cardid": "ท7730356386",
        "count_select": "0",
        "expdate": "NoExp",
        "fname": "ทองล้วน",
        "hmain": "10671",
        "hmain_name": "รพ.อุดรธานี",
        "hmain_op": "10671",
        "hmain_op_name": "รพ.อุดรธานี",
        "hsub": "13906",
        "hsub_name": "รพ.สต.บ้านหนองตะไก้ หมู่ที่ 04 ตำบลหนองไผ่",
        "lname": "สำราญทอง",
        "maininscl": "WEL",
        "maininscl_main": "U",
        "maininscl_name": "สิทธิหลักประกันสุขภาพแห่งชาติ (ยกเว้นการร่วมจ่ายค่าบริการ 30 บาท)",
        "mastercup_id": "41012100002",
        "nation": "099",
        "paid_model": "1",
        "person_id": "3410101798122",
        "primary_amphur_name": "เมืองอุดรธานี",
        "primary_moo": "06",
        "primary_mooban_name": "หนองนาเจริญ",
        "primary_province_name": "อุดรธานี",
        "primary_tumbon_name": "หนองไผ่",
        "primaryprovince": "4100",
        "purchaseprovince": "4100",
        "purchaseprovince_name": "อุดรธานี",
        "sex": "1",
        "startdate": "25481030",
        "status": "004",
        "subinscl": "77",
        "subinscl_name": "ผู้มีอายุเกิน 60 ปีบริบูรณ์",
        "title": "003",
        "title_name": "นาย",
        "ws_data_source": "NHSO",
        "ws_date_request": "2019-04-24T20:41:43+07:00",
        "ws_status": "NHSO-000001",
        "ws_status_desc": "ok",
        "wsid": "WS000007635974206",
        "wsid_batch": "WSB00001203212584"
    }
JSON;
        return Json::decode($json); */
        if(empty($cid) || strlen($cid) < 13) {
            throw new HttpException(422, 'ไม่พบข้อมูลสิทธิการรักษา');
        }
        $client = Yii::$app->nhso;
        $sql = "SELECT * FROM nhso_token ORDER BY updated_at DESC";
        $userToken = \Yii::$app->db2->createCommand($sql)->queryOne();
        $params = array(
            'user_person_id' => $userToken ? $userToken['token_cid'] : '',
            'smctoken' => $userToken ? $userToken['token_key'] : '',
            'person_id' => $cid
        );
        $res = $client->searchCurrentByPID($params);
        $res = (array)$res;
        $data = (array)$res['return'];
        if (!$data) {
            $logger->info('pt-right', ['msg' => 'RESPONSE FAILED', 'cid' => $cid, 'data' => $data]);
            throw new HttpException(422, 'RESPONSE FAILED');
        } else if (isset($data['ws_status']) && $data['ws_status'] == 'NHSO-00003') {
            $logger->info('pt-right', ['msg' => isset($data['ws_status_desc']) ? $data['ws_status_desc'] : 'TOKEN EXPIRE', 'cid' => $cid, 'data' => $data]);
            throw new HttpException(422, isset($data['ws_status_desc']) ? $data['ws_status_desc'] : 'TOKEN EXPIRE');
        } else if (isset($data['fname']) && empty($data['fname'])) {
            $logger->info('pt-right', ['msg' => 'NOT FOUND IN NHSO', 'cid' => $cid, 'data' => $data]);
            throw new HttpException(422, 'NOT FOUND IN NHSO');
        } elseif(!isset($data['maininscl']) || !isset($data['maininscl_name'])) {
            $logger->info('pt-right', ['msg' => 'ไม่พบข้อมูลสิทธิการรักษา', 'cid' => $cid, 'data' => $data]);
            throw new HttpException(422, 'ไม่พบข้อมูลสิทธิการรักษา');
        }
        return $data;
    }
}