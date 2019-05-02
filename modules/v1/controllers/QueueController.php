<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\TblDept;
use app\modules\v1\models\TblPatient;
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
use app\modules\v1\models\TblKiosk;
use app\modules\v1\traits\ModelTrait;
use app\modules\v1\models\TblDeptGroup;

class QueueController extends ActiveController
{
    use ModelTrait;

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
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT'],
                'delete' => ['DELETE'],
                'register' => ['POST'],
                'data-print' => ['GET'],
                'kiosk-list' => ['GET'],
                'departments' => ['GET']
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
        $behaviors['authenticator']['except'] = ['options', 'data-print', 'kiosk-list'];
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'departments', 'register'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    // ลงทะเบียนคิว
    public function actionRegister()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $patient = TblPatient::findOne(['cid' => $params['user']['cid']]);
        $modelPatient = $patient ? $patient : new TblPatient();
        $modelDept = TblDept::findOne(['dept_id' => $params['department']['dept_code']]);
        if (!$modelDept) {
            throw new HttpException(422, 'ไม่พบข้อมูลแผนกในระบบคิว');
        }
        $modelDeptGroup = $this->findModelDeptGroup($modelDept['dept_group_id']);
        $priority = $params['queue_type'] === 1 ? 1 : $params['priority'];
        $transaction = TblPatient::getDb()->beginTransaction();
        try {
            $modelPatient->setAttributes($params['user']);
            $modelPatient->appoint = empty($params['user']['appoint']) ? '' : Json::encode($params['user']['appoint']);
            if ($modelPatient->save()) {
                $modelQueue = new TblQueue();
                $modelQueue->patient_id = $modelPatient['patient_id'];
                $modelQueue->dept_group_id = $modelDeptGroup['dept_group_id'];
                $modelQueue->dept_id = $modelDept['dept_id'];
                $modelQueue->priority_id = $priority;
                $modelQueue->queue_type = $params['queue_type'];
                $modelQueue->queue_status_id = 1; // default รอเรียก
                if ($modelQueue->save()) {
                    $transaction->commit();
                    $response = \Yii::$app->getResponse();
                    $response->setStatusCode(201);
                    return [
                        'queue' => $modelQueue,
                        'patient' => $modelPatient,
                        'dept' => $modelDept,
                        'dept_group' => $modelDeptGroup
                    ];
                } else {
                    $transaction->rollBack();
                    TblPatient::findOne($modelPatient['patient_id'])->delete();
                    throw new HttpException(422, Json::encode($modelQueue->errors));
                }
            } else {
                $transaction->rollBack();
                throw new HttpException(422, Json::encode($modelPatient->errors));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        /* $model = new TblQueue();
        $user = $request->post('user');
        $department = $request->post('department');
        $model->setAttributes([
            'queue_hn' => $user['hn'],
            'fullname' => $user['fullname'],
            'department_code' => $department['dept_code'],
            'user_info' => Json::encode($user),
        ]);
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return $model;
        } else {
            // Validation error
            throw new HttpException(400, Json::encode($model->errors));
        } */
    }

    // ข้อมูลการพิมพ์บัตรคิว
    public function actionDataPrint($id)
    {
        $model = $this->findModelQueue($id);
        $modelPatient = $this->findModelPatient($model['patient_id']);
        return [
            'queue' => $model, // ข้อมูลคิว
            'department' => $model->dept, // ข้อมูลแผนก
            'patient' => $modelPatient
        ];
    }

    // รายการตู้กดบัตรคิว
    public function actionKioskList()
    {
        return TblKiosk::find()->all();
    }

    // ข้อมูลแผนก
    public function actionDepartments()
    {
        $deptGroups = TblDeptGroup::find()->all();
        $response = [];
        $response[] = [
            'dept_group' => ['dept_group_id' => time(), 'dept_group_name' => 'แผนกทั้งหมด'],
            'departments' => TblDept::find()->where([
                'dept_status' => TblDept::STATUS_ACTIVE
            ])->all()
        ];
        foreach ($deptGroups as $deptGroup) {
            $departments = TblDept::find()->where([
                'dept_group_id' => $deptGroup['dept_group_id'],
                'dept_status' => TblDept::STATUS_ACTIVE
            ])->all();
            $response[] = [
                'dept_group' => $deptGroup,
                'departments' => $departments
            ];
        }
        return $response;
    }
}