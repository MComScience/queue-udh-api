<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use app\helpers\Html;
use yii\helpers\Url;
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
use app\modules\v1\models\FileStorageItem;
use app\modules\v1\models\TblDept;
use app\modules\v1\models\TblPatient;
use app\modules\v1\models\TblPriority;
use app\modules\v1\models\TblProfileService;
use app\modules\v1\models\TblCounterService;
use app\modules\v1\models\TblCaller;
use app\helpers\Enum;
use app\components\AppQuery;
use app\components\SoundComponent;

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
                'dashboard' => ['GET'],
                'list-all' => ['GET'],
                'update-patient' => ['POST'],
                'data-waiting' => ['POST'],
                'profile-service-options' => ['GET'],
                'call-wait' => ['POST'],
                'data-wait-by-hn' => ['POST'],
                'end-wait' => ['POST'],
                'data-caller' => ['POST'],
                'recall' => ['POST'],
                'hold' => ['POST'],
                'data-hold' => ['POST'],
                'end' => ['POST'],
                'call-hold' => ['POST'],
                'end-hold' => ['POST']
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
                        'list-all', 'update-patient', 'data-waiting', 'profile-service-options',
                        'call-wait','data-wait-by-hn', 'end-wait', 'data-caller', 'recall', 'hold',
                        'data-hold', 'end', 'call-hold', 'end-hold'
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
        $logger = Yii::$app->logger->getLogger();
        $params = \Yii::$app->getRequest()->getBodyParams();
        $imgUrl = '';
        $modelDept = TblDept::find()->findByDeptCode($params['department']['dept_code']); // ค้นหาแผนก
        $this->checkDept($modelDept, $params, $logger); // ตรวจสอบข้อมูลแผนก
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $rows = AppQuery::getQueueRegister([ // ค้นหาคิวที่เคยลงทะเบียน
            'dept_id' => $modelDept['dept_id'],
            'hn' => $params['user']['hn'],
            'cid' => $params['user']['cid'],
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        $modelDeptGroup = $this->findModelDeptGroup($modelDept['dept_group_id']); // กลุ่มแผนก
        // ถ้าลงทะเบียนแผนกเดิม
        if ($rows && $rows !== null) {
            $modelPatient = $this->findModelPatient($rows['patient_id']); // ข้อมูลผู้ป่วย
            $modelPatient->setAttributes($params['user']);
            $modelPatient->appoint = empty($params['user']['appoint']) ? '' : Json::encode($params['user']['appoint']); // นัดหมาย
            $modelPatient->save();

            $modelStorage = FileStorageItem::find()->findByRefId($rows['patient_id']); // ค้นหารูปภาพ
            if ($modelStorage) { // ถ้าเจอรูปภาพ
                $imgUrl = Html::imgUrl($modelStorage['path']); // ลิ้งค์รูปภาพ
            }
            return [
                'queue' => $rows, // ข้อมูลคิว
                'patient' => $modelPatient, // ข้อมูลผู้ป่วย
                'dept' => $modelDept, // ข้อมูลแผนก
                'dept_group' => $modelDeptGroup, // ข้อมูลกลุ่มแผนก
                'imgUrl' => $imgUrl // ลิ้งค์ภาพโปรไฟล์
            ];
        }
        $transaction = TblPatient::getDb()->beginTransaction();
        try {
            // ข้อมูลผู้ป่วย
            $modelPatient = new TblPatient();
            $modelPatient->setAttributes($params['user']);
            $modelPatient->appoint = empty($params['user']['appoint']) ? '' : Json::encode($params['user']['appoint']);
            if ($modelPatient->save()) {
                // ข้อมูลคิว
                $modelQueue = new TblQueue();
                $modelQueue->setAttributes([
                    'patient_id' => $modelPatient['patient_id'], // ไอดีผู้ป่วย
                    'dept_group_id' => $modelDeptGroup['dept_group_id'], // กลุ่มแผนก
                    'dept_id' => $modelDept['dept_id'], // แผนก
                    'priority_id' => $params['priority'], // ประเภทคิว
                    'queue_station' => $params['queue_station'], // ออกคิวจากตู้หรือ one stop
                    'case_patient' => $params['case_patient'], // กรณีผู้ป่วย
                    'queue_status_id' => TblQueue::STATUS_WAIT, // default รอเรียก
                ]);

                if ($modelQueue->save()) {
                    if (!empty($params['user']['photo'])) { // save photo from base64
                        $modelStorage = $this->savePhoto($params['user']['photo'], $modelPatient['patient_id']);
                        if($modelStorage){
                            $imgUrl = Html::imgUrl($modelStorage['path']); // ลิ้งค์รูปภาพ
                        }
                    }
                    $transaction->commit();
                    $response = \Yii::$app->getResponse();
                    $response->setStatusCode(201);
                    return [
                        'queue' => $modelQueue, // ข้อมูลคิว
                        'patient' => $modelPatient, // ข้อมูลผู้ป่วย
                        'dept' => $modelDept, // ข้อมูลแผนก
                        'dept_group' => $modelDeptGroup, // ข้อมูลกลุ่มแผนก
                        'imgUrl' => $imgUrl // ลิ้งค์ภาพโปรไฟล์
                    ];
                } else {
                    $transaction->rollBack();
                    $logdata = [
                        'msg' => $modelQueue->errors,
                        'data' => $params
                    ];
                    $logger->error('Register Queue', $logdata); // save to log file
                    Yii::$app->notify->sendMessage('ลงทะเบียนคิวไม่สำเร็จ! ' . "\n" . Json::encode($params['user'])); // send to line notify
                    // TblPatient::findOne($modelPatient['patient_id'])->delete();
                    throw new HttpException(422, Json::encode($modelQueue->errors));
                }
            } else {
                $transaction->rollBack();
                $logdata = [
                    'msg' => $modelPatient->errors,
                    'data' => $params
                ];
                $logger->error('Register Queue', $logdata); // save to log file
                Yii::$app->notify->sendMessage('ลงทะเบียนคิวไม่สำเร็จ! ' . "\n" . Json::encode($params['user'])); // send to line notify
                throw new HttpException(422, Json::encode($modelPatient->errors));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $logger->error('Register Queue', ['msg' => $e]);
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $logger->error('Register Queue', ['msg' => $e]);
            throw $e;
        }
    }

    // ตรวจสอบข้อมูลแผนก
    private function checkDept($model, $params, $logger)
    {
        if (!$model) { // ถ้าไม่พบข้อมูลแผนก
            $logger->error('Register Queue', ['msg' => 'ไม่พบข้อมูลแผนกในระบบคิว', 'dept_code' => $params['department']['dept_code']]); // save to log file
            Yii::$app->notify->sendMessage('ไม่พบข้อมูลแผนกในระบบคิว! ' . "\n" . Json::encode([
                'hn' => $params['user']['hn'], 
                'fullname' => $params['user']['fullname'], 
                'dept' => $params['department']
            ])); // send to line notify
            throw new HttpException(422, 'ไม่พบข้อมูลแผนกในระบบคิว');
        }
    }

    private function savePhoto($photo, $patient_id)
    {
        $img = str_replace('data:image/png;base64,', '', $photo);
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $security = Yii::$app->security->generateRandomString();
        $filename = implode('.', [
            $security,
            'jpg'
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
            $modelStorage->created_at = Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s');
            if ($modelStorage->save()) {
                return $modelStorage;
            }
        } else {
            return '';
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
            'patient' => $modelPatient // ข้อมูลผู้ป่วย
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
            $deptGroups = TblDeptGroup::find()->where(['dept_group_id' => $depts])->orderBy('dept_group_order asc')->all();
            /* $response[] = [
                'dept_group' => ['dept_group_id' => time(), 'dept_group_name' => 'แผนกทั้งหมด'],
                'departments' => TblDept::find()->where([
                    'dept_status' => TblDept::STATUS_ACTIVE,
                    'dept_group_id' => $depts
                ])
                ->orderBy('dept_order asc')
                ->all()
            ]; */
        } else {
            $deptGroups = TblDeptGroup::find()->orderBy('dept_group_order asc')->all();
            /* $response[] = [
                'dept_group' => ['dept_group_id' => time(), 'dept_group_name' => 'แผนกทั้งหมด'],
                'departments' => TblDept::find()->where([
                    'dept_status' => TblDept::STATUS_ACTIVE,
                ])
                ->orderBy('dept_order asc')
                ->all()
            ]; */
        }
        foreach ($deptGroups as $deptGroup) {
            $departments = TblDept::find()->where([
                'dept_group_id' => $deptGroup['dept_group_id'],
                'dept_status' => TblDept::STATUS_ACTIVE
            ])
            ->orderBy('dept_order asc')
            ->all();
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

    // เช็คคิวที่ลงทะเบียน
    public function actionPatientRegister($q)
    {
        // hn
        if (strlen($q) < 13) {
            $patient = AppQuery::getPatientByHn($q);
        } else { // cid
            $patient = AppQuery::getPatientByCid($q);
        }
        if (!$patient) {
            return [
                'message' => 'ไม่พบข้อมูลคิว'
            ];
        }
        $queues = AppQuery::getPatientRegister($patient);
        if (count($queues) == 0 && !$queues) {
            return [
                'message' => 'ไม่พบข้อมูลคิว'
            ];
        } else {
            return [
                'message' => 'success',
                'queues' => $queues
            ];
        }
    }

    // ข้อมูลแดชบอร์ด
    public function actionDashboard($q = '')
    {
        $itemDepts = [];
        $seriesPie = [];
        $seriesColumn = [];
        $seriesDrilldown = [];
        $deptGroups = TblDeptGroup::find()->all();
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        // ถ้ามีการค้นหาจากวันที่
        if (!empty($q)) {
            // วันที่เริ่ม
            $startDate = $q . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $q . ' 23:59:59';
        }
        $countAll = TblQueue::find()->andWhere(['between', 'created_at', $startDate, $endDate])->count(); // จำนวนทั้งหมด
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
                $y = $countAll > 0 ? (float)number_format(($count / $countAll) * 100, 2) : 0;
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

    public function actionListAll($q = '')
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        // ถ้ามีการค้นหาจากวันที่
        if (!empty($q)) {
            // วันที่เริ่ม
            $startDate = $q . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $q . ' 23:59:59';
        }
        $rows = AppQuery::getAllQueue(['startDate' => $startDate, 'endDate' => $endDate]);
        $response = [];
        foreach ($rows as $row) {
            $response[] = ArrayHelper::merge($row, [
                'base_url' => !empty($row['base_url']) ? Html::imgUrl($row['path']) : ''
            ]);
        }
        return $response;
    }

    // ลบข้อมูลคิว
    public function actionDelete($id)
    {
        $logger = Yii::$app->logger->getLogger();
        $model = $this->findModelQueue($id);
        $patient = $this->findModelPatient($model['patient_id']);
        $file = FileStorageItem::findOne(['ref_id' => $model['patient_id']]);
        $model->delete();
        $patient->delete();
        if($file){
            FileHelper::unlink(Yii::getAlias('@web').$file['base_url'].$file['path']);
            $file->delete();
        }
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        $logger->info('Deleted Queue', ['hn' => $patient['hn'], 'queue_id' => $id, 'delete_by' => Yii::$app->user->id]); 
        return ['message' => 'ลบรายการสำเร็จ!'];
    }

    public function actionUpdatePatient()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queueId']);
        $modelPatient = $this->findModelPatient($modelQueue['patient_id']);
        $modelPatient->setAttributes($params['patient']);
        if($modelPatient->save()) {
            return [
                'queue' => $modelQueue,
                'patient' => $modelPatient
            ];
        } else {
            throw new HttpException(422, Json::encode($modelPatient->errors));
        }
    }

    // โปรไฟล์เซอร์วิส
    public function actionProfileServiceOptions()
    {
        $response = [];
        $profiles = TblProfileService::find()->where(['profile_service_status' => TblProfileService::STATUS_ACTIVE])->all();
        foreach ($profiles as $key => $profile) {
            $counter = $this->findModelCounter($profile['counter_id']); // เคาน์เตอร์
            $depts = TblDept::find()->where(['dept_id' => Json::decode($profile['dept_id'])])->all();
            $counterServices = ArrayHelper::map(TblCounterService::find()->where(['counter_id' => $profile['counter_id']])->asArray()->all(), 'counter_service_id', 'counter_service_name');
            $counterServiceOptions = [];
            foreach ($counterServices as $k => $value) {
                $counterServiceOptions[] = [
                    'key' => $k,
                    'value' => $value
                ];
            }
            $response[] = [
                'profile' => ArrayHelper::merge($profile, [
                    'dept_id' => Json::decode($profile['dept_id'])
                ]),
                'counter' => $counter,
                'depts' => $depts,
                'counterServiceOptions' => $counterServiceOptions
            ];
        }
        return $response;
    }

    // คิวรอเรียก
    public function actionDataWaiting()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataWaiting($params);
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        return $data;
    }

    // คิวรอเรียก
    public function actionDataWaitByHn()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataWaitByHn($params);
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        return $data;
    }

    // คิวกำลังเรียก
    public function actionDataCaller()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataCaller($params);
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        return $data;
    }

    // คิวพัก
    public function actionDataHold()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataHold($params);
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        return $data;
    }

    // เรียกคิว กำลังรอเรียก
    public function actionCallWait()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelCall = new TblCaller();
        $modelCall->setAttributes([
            'queue_id' => $params['queue']['queue_id'], // รหัสคิว
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'call_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
        if($modelCall->save() && $modelQueue->save()){
            return ArrayHelper::merge($params, [
                'sources' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                'event_on' => 'tbl_wait',
                'caller' => $modelCall
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // เสร็จสิ้นคิว กำลังรอเรียก
    public function actionEndWait()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelCall = new TblCaller();
        $modelCall->setAttributes([
            'queue_id' => $params['queue']['queue_id'], // รหัสคิว
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'call_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเรียก
            'end_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเสร็จสิ้น
            'caller_status' => TblCaller::STATUS_CALL_END // 1
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_END;
        if($modelCall->save() && $modelQueue->save()){
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_wait',
                'caller' => $modelCall
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // เรียกคิวซ้ำ
    public function actionRecall()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'call_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
        if($modelCall->save() && $modelQueue->save()){
            return ArrayHelper::merge($params, [
                'sources' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                'event_on' => 'tbl_caller',
                'caller' => $modelCall
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // พักคิว
    public function actionHold()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'hold_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL_END // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_HOLD;
        if($modelCall->save() && $modelQueue->save()){
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_caller',
                'caller' => $modelCall
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    //เสร็จสิ้นคิว
    public function actionEnd()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'end_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL_END // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_END;
        if($modelCall->save() && $modelQueue->save()){
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_caller',
                'caller' => $modelCall
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // เรียกคิวจากรายการพักคิว
    public function actionCallHold()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'call_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
        if($modelCall->save() && $modelQueue->save()){
            return ArrayHelper::merge($params, [
                'sources' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                'event_on' => 'tbl_hold',
                'caller' => $modelCall
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // เสร็จสิ้นคิว จากรายการพักคิว
    public function actionEndHold()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'end_time' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL_END // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_END;
        if($modelCall->save() && $modelQueue->save()){
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_hold',
                'caller' => $modelCall
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // ไฟล์เสียงเรียก
    protected function getSourceMediaFiles($number, $counter_service_id)
    {
        $component = \Yii::createObject([
            'class' => SoundComponent::className(),
            'number' => $number,
            'counter_service_id' => $counter_service_id,
        ]);
        return $component->getSource();
    }
}