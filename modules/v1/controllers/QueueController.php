<?php

namespace app\modules\v1\controllers;

use app\modules\v1\models\FileStorageItem;
use app\modules\v1\models\TblDept;
use app\modules\v1\models\TblPatient;
use app\modules\v1\models\TblPriority;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\web\Response;
use app\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
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
                QueryParamAuth::className(),
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
                'departments' => ['GET'],
                'priority' => ['GET'],
                'patient-register' => ['GET'],
                'dashboard' => ['GET']
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
        $behaviors['authenticator']['except'] = [
            'options', 'data-print', 'kiosk-list', 'priority', 'patient-register',
            'dashboard'
        ];
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index', 'view', 'create', 'update', 'delete', 'departments', 'register',

                    ],
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
        $modelDept = TblDept::findOne(['dept_id' => $params['department']['dept_code']]);
        if (!$modelDept) {
            throw new HttpException(422, 'ไม่พบข้อมูลแผนกในระบบคิว');
        }
        $startDate = Yii::$app->formatter->asDate('now', 'php:Y-m-d 00:00:00');
        $endDate = Yii::$app->formatter->asDate('now', 'php:Y-m-d 23:59:59');
        $rows = (new \yii\db\Query())
            ->select(['tbl_queue.*'])
            ->from('tbl_queue')
            ->where([
                'tbl_queue.dept_id' => $modelDept['dept_id'],
                'tbl_patient.cid' => $params['user']['cid']
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->one();
        $modelDeptGroup = $this->findModelDeptGroup($modelDept['dept_group_id']);
        if ($rows && $rows !== null) {
            $modelPatient = $this->findModelPatient($rows['patient_id']);
            $modelPatient->setAttributes($params['user']);
            $modelPatient->save();
            return [
                'queue' => $rows,
                'patient' => $modelPatient,
                'dept' => $modelDept,
                'dept_group' => $modelDeptGroup
            ];
        }
        //$priority = $params['queue_type'] === 1 ? 1 : $params['priority'];
        $transaction = TblPatient::getDb()->beginTransaction();
        try {
            $modelPatient = new TblPatient();
            $modelPatient->setAttributes($params['user']);
            $modelPatient->appoint = empty($params['user']['appoint']) ? '' : Json::encode($params['user']['appoint']);
            if ($modelPatient->save()) {
                $modelQueue = new TblQueue();
                $modelQueue->patient_id = $modelPatient['patient_id'];
                $modelQueue->dept_group_id = $modelDeptGroup['dept_group_id'];
                $modelQueue->dept_id = $modelDept['dept_id'];
                $modelQueue->priority_id = $params['priority'];
                $modelQueue->queue_type = $params['queue_type'];
                $modelQueue->queue_status_id = 1; // default รอเรียก

                if ($modelQueue->save()) {
                    if ($params['user']['photo']) {
                        $this->savePhoto($params['user']['photo'], $modelPatient['patient_id']);
                    }
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

    private function savePhoto($photo, $patient_id)
    {
        $img = str_replace('data:image/png;base64,', '', $photo);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $security = Yii::$app->security->generateRandomString();
        $filename = implode('.', [
            $security,
            'png'
        ]);
        $path = Yii::getAlias('@webroot/uploads/avatar/') . $filename;
        $f = file_put_contents($path, $data);
        if ($f) {
            $modelStorage = new FileStorageItem();
            $modelStorage->base_url = '/uploads';
            $modelStorage->path = '/avatar/' . $filename;
            $modelStorage->type = FileHelper::getMimeType($path);
            $modelStorage->size = filesize($path);
            $modelStorage->name = $security;
            $modelStorage->ref_id = $patient_id;
            $modelStorage->created_at = new Expression('NOW()');
            $modelStorage->save(false);
        }
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
        $response = [];
        $kiosks = TblKiosk::find()->all();
        foreach ($kiosks as $kiosk) {
            $response[] = [
                'kiosk' => $kiosk,
                'auth_key' => $kiosk->user->auth_key
            ];
        }
        return $response;
    }

    // ข้อมูลแผนก
    public function actionDepartments($kioskId = null)
    {
        $response = [];
        if ($kioskId) {
            $modelKiosk = $this->findModelKiosk($kioskId);
            $depts = !empty($modelKiosk['departments']) ? Json::decode($modelKiosk['departments']) : [];
            $deptGroups = TblDeptGroup::find()->where(['dept_group_id' => $depts])->all();
            $response[] = [
                'dept_group' => ['dept_group_id' => time(), 'dept_group_name' => 'แผนกทั้งหมด'],
                'departments' => TblDept::find()->where([
                    'dept_status' => TblDept::STATUS_ACTIVE,
                    'dept_group_id' => $depts
                ])->all()
            ];
        } else {
            $deptGroups = TblDeptGroup::find()->all();
            $response[] = [
                'dept_group' => ['dept_group_id' => time(), 'dept_group_name' => 'แผนกทั้งหมด'],
                'departments' => TblDept::find()->where([
                    'dept_status' => TblDept::STATUS_ACTIVE,
                ])->all()
            ];
        }
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

    // ประเภทคิว
    public function actionPriority()
    {
        return ArrayHelper::map(TblPriority::find()->all(), 'priority_id', 'priority_name');
    }

    public function actionPatientRegister($q)
    {
        $startDate = Yii::$app->formatter->asDate('now', 'php:Y-m-d 00:00:00');
        $endDate = Yii::$app->formatter->asDate('now', 'php:Y-m-d 23:59:59');
        // hn
        if (strlen($q) < 13) {
            $patient = TblPatient::find()
                ->where(['hn' => $q])
                ->andWhere(['between', 'created_at', $startDate, $endDate])
                ->orderBy('patient_id desc')
                ->one();
        } else { // cid
            $patient = TblPatient::find()
                ->where(['cid' => $q])
                ->andWhere(['between', 'created_at', $startDate, $endDate])
                ->orderBy('patient_id desc')
                ->one();
        }
        if (!$patient) {
            return [
                'message' => 'ไม่พบข้อมูลผู้ป่วย'
            ];
        }
        $queues = (new \yii\db\Query())
            ->select(['tbl_queue.*', 'tbl_dept.*'])
            ->from('tbl_queue')
            ->where(['patient_id' => $patient['patient_id']])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->innerJoin('tbl_dept', 'tbl_dept.dept_id = tbl_queue.dept_id')
            ->all();
        if (count($queues) == 0 && !$queues){
            return [
                'message' => 'ไม่พบข้อมูลคิว'
            ];
        }else{
            return [
                'message' => 'success',
                'queues' => $queues
            ];
        }
    }

    public function actionDashboard($q = '')
    {
        $itemDepts = [];
        $seriesPie = [];
        $seriesColumn = [];
        $seriesDrilldown = [];
        $deptGroups = TblDeptGroup::find()->all();
        // วันที่เริ่ม
        $startDate = Yii::$app->formatter->asDate('now', 'php:Y-m-d 00:00:00');
        // วันที่สิ้นสุด
        $endDate = Yii::$app->formatter->asDate('now', 'php:Y-m-d 23:59:59');
        // ถ้ามีการค้นหาจากวันที่
        if(!empty($q)) {
            // วันที่เริ่ม
            $startDate = $q . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $q . ' 23:59:59';
        }
        $countAll = TblQueue::find()->andWhere(['between', 'created_at', $startDate, $endDate])->count();
        $itemDepts[] = [
            'dept_group_name' => 'ทั้งหมด',
            'items' => [
                [
                    'dept_name' => 'รวมจำนวนทั้งหมด',
                    'count' => $countAll
                ]
            ]
        ];
        // นับคิวแต่ละแผนก ** เฉพาะแผนกที่ Active
        foreach ($deptGroups as $deptGroup) {
            $deptArr = [];
            $countDept = 0;
            $dataDrilldown = [];
            // แผนก
            $departments = TblDept::find()->where(['dept_status' => 1, 'dept_group_id' => $deptGroup['dept_group_id']])->all();
            foreach ($departments as $k => $department) {
                $count = TblQueue::find()->where([
                    'dept_id' => $department['dept_id'],
                ])->andWhere(['between', 'created_at', $startDate, $endDate])->count();
                $deptArr[] = [
                    'dept_name' => $department['dept_name'],
                    'count' => $count
                ];
                $y = $countAll > 0 ? (float) number_format(($count/$countAll) * 100, 2) : 0;
                $seriesPie[] = [
                    'name' => $department['dept_name'],
                    'y' => $y,
                ];
                $countDept = $countDept + $count;
                $dataDrilldown[] = [
                    $department['dept_name'],
                    intval($count)
                ];
            }
            $itemDepts[] = [
                'dept_group_name' => $deptGroup['dept_group_name'],
                'items' => $deptArr
            ];
            $seriesColumn[] = [
                'name' => $deptGroup['dept_group_name'],
                'y' => $countDept,
                'drilldown' => $deptGroup['dept_group_name']
            ];
            $seriesDrilldown[] = [
                'name' => $deptGroup['dept_group_name'],
                'id' => $deptGroup['dept_group_name'],
                'data' => $dataDrilldown
            ];
        }
        return [
            'itemDepts' => $itemDepts, // จำนวนคิวแต่ละแผนก
            'countAll' => $countAll,
            'seriesPie' => $seriesPie,
            'seriesColumn' => $seriesColumn,
            'seriesDrilldown' => $seriesDrilldown
        ];
    }
}