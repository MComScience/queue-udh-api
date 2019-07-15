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
use app\modules\v1\models\TblCounter;
use app\modules\v1\models\TblCaller;
use app\modules\v1\models\TblFloor;
use app\modules\v1\models\User;
use app\modules\v1\models\TblServiceGroup;
use app\modules\v1\models\TblService;
use app\modules\v1\models\TblPlayStation;
use app\modules\v1\models\TblDisplay;
use app\modules\v1\models\TblDoctor;
use app\helpers\Enum;
use app\components\AppQuery;
use app\components\SoundComponent;
use app\components\ChartBuilder;
use app\filters\AccessRule;

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
                'services' => ['GET'],
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
                'end-hold' => ['POST'],
                'call-selected' => ['POST'],
                'register-examination' => ['POST'],
                'data-waiting-examination' => ['POST'],
                'data-caller-examination' => ['POST'],
                'data-hold-examination' => ['POST'],
                'play-stations' => ['GET'],
                'get-play-station' => ['GET'],
                'update-call-status' => ['POST'],
                'display-list' => ['GET'],
                'get-display' => ['GET'],
                'queue-play-list' => ['GET'],
                'active-play-station' => ['GET'],
                'get-services' => ['GET'],
                'led-options' => ['GET'],
                'check-register-ex' => ['POST'],
                'update-queue' => ['POST'],
                'call-wait-ex' => ['POST'],
                'call-selected-ex' => ['POST'],
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
            'dashboard', 'play-stations', 'get-play-station', 'update-call-status',
            'display-list', 'get-display', 'queue-play-list', 'active-play-station',
            'led-options'
        ];
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => [
                'index', 'view', 'create', 'update', 'delete', 'services', 'register',
                'list-all', 'update-patient', 'data-waiting', 'profile-service-options',
                'call-wait', 'data-wait-by-hn', 'end-wait', 'data-caller', 'recall', 'hold',
                'data-hold', 'end', 'call-hold', 'end-hold', 'call-selected', 'register-examination',
                'data-waiting-examination', 'data-caller-examination', 'data-hold-examination'
            ], //only be applied to
            'ruleConfig' => [
                'class' => AccessRule::className()
            ],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index', 'view', 'create', 'update', 'delete', 'services', 'register',
                        'list-all', 'update-patient', 'data-waiting', 'profile-service-options',
                        'call-wait', 'data-wait-by-hn', 'end-wait', 'data-caller', 'recall', 'hold',
                        'data-hold', 'end', 'call-hold', 'end-hold', 'call-selected', 'register-examination',
                        'data-waiting-examination', 'data-caller-examination', 'data-hold-examination',
                        'get-services', 'check-register-ex', 'update-queue', 'call-wait-ex', 'call-selected-ex'
                    ],
                    'roles' => [
                        User::ROLE_ADMIN,
                        User::ROLE_USER,
                        User::ROLE_KIOSK
                    ]
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
        $modelService = $this->findModelService($params['service']['service_id']); // ค้นหาแผนก
        $this->checkDept($modelService, $params, $logger); // ตรวจสอบข้อมูลแผนก
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $rows = AppQuery::getQueueRegister([ // ค้นหาคิวที่เคยลงทะเบียน
            'service_id' => $modelService['service_id'],
            'hn' => $params['user']['hn'],
            'cid' => $params['user']['cid'],
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        // ถ้าลงทะเบียนแผนกเดิม
        if ($rows && $rows !== null) {
            $logger->info('Select QueueRegister', ['hn' => $params['user']['hn'], 'service_code' => $modelService['service_code'], 'rows' => $rows]);
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
                'service' => $modelService, // ข้อมูลแผนก
                'group' => $modelServiceGroup, // ข้อมูลกลุ่มแผนก
                'imgUrl' => $imgUrl // ลิ้งค์ภาพโปรไฟล์
            ];
        }
        $transaction = TblPatient::getDb()->beginTransaction();
        try {
            $modelQueueService = $this->findModelQueueService($modelServiceGroup['queue_service_id']);
            // ข้อมูลผู้ป่วย
            $modelPatient = new TblPatient();
            $modelPatient->setAttributes($params['user']);
            $modelPatient->appoint = empty($params['user']['appoint']) ? '' : Json::encode($params['user']['appoint']);
            if ($modelPatient->save()) {
                // ข้อมูลคิว
                $modelQueue = new TblQueue();
                $modelQueue->setAttributes([
                    'patient_id' => $modelPatient['patient_id'], // ไอดีผู้ป่วย
                    'service_group_id' => $modelServiceGroup['service_group_id'], // กลุ่มบริการ
                    'service_id' => $modelService['service_id'], // ชื่อบริการ
                    'priority_id' => $params['priority'], // ประเภทคิว
                    'queue_station' => $params['queue_station'], // ออกคิวจากตู้หรือ one stop
                    'case_patient' => $params['case_patient'], // กรณีผู้ป่วย
                    'appoint' => $params['appoint'] === true || $params['appoint'] == 'true' ? 1 : 0, // คิวนัด
                    'queue_status_id' => TblQueue::STATUS_WAIT, // default รอเรียก
                    'issue_card_ex' => 0, //สถานะออกบัตรคิวห้องตรวจ
                ]);

                if ($modelQueue->save()) {
                    $modelStorage = null;
                    if (!empty($params['user']['photo'])) { // save photo from base64
                        $modelStorage = $this->savePhoto($params['user']['photo'], $modelPatient['patient_id']);
                        if ($modelStorage) {
                            $imgUrl = Html::imgUrl($modelStorage['path']); // ลิ้งค์รูปภาพ
                        }
                    }
                    $queue = [
                        'queue_id' => $modelQueue['queue_id'],
                        'queue_no' => $modelQueue['queue_no'],
                        'patient_id' => $modelQueue['patient_id'],
                        'service_group_id' => $modelQueue['service_group_id'],
                        'service_id' => $modelQueue['service_id'],
                        'priority_id' => $modelQueue['priority_id'],
                        'queue_station' => $modelQueue['queue_station'],
                        'case_patient' => $modelQueue['case_patient'],
                        'queue_status_id' => $modelQueue['queue_status_id'],
                        'created_at' => $modelQueue['created_at'],
                        'updated_at' => $modelQueue['updated_at'],
                        'created_by' => $modelQueue['created_by'],
                        'updated_by' => $modelQueue['updated_by'],
                        'name' => $modelQueue->profile ? $modelQueue->profile->name : '',
                        'base_url' => $modelStorage ? $modelStorage['base_url'] : '',
                        'path' => $modelStorage ? $modelStorage['path'] : '',
                        'queue_service_name' => $modelQueueService['queue_service_name'],
                        'fullname' => $modelPatient['fullname'],
                        'maininscl_name' => $modelPatient['maininscl_name'],
                        'cid' => !empty($modelPatient['cid']) ? substr_replace($modelPatient['cid'], "****", 9) : '',
                        'hn' => $modelPatient['hn'],
                        'service_name' => $modelService['service_name'],
                        'service_code' => $modelService['service_code'],
                        'print_time' => Yii::$app->formatter->asDate($modelQueue['created_at'], 'php:Y-m-d H:i:s'),
                        'service_group_name' => $modelServiceGroup['service_group_name'],
                        'issue_card_ex' => $modelQueue['issue_card_ex'],
                        'parent_id' => $modelQueue['parent_id']
                    ];
                    $transaction->commit();
                    $response = \Yii::$app->getResponse();
                    $response->setStatusCode(201);
                    return [
                        'queue' => $queue, // ข้อมูลคิว
                        'patient' => $modelPatient, // ข้อมูลผู้ป่วย
                        'service' => $modelService, // ข้อมูลบริการ
                        'serviceGroup' => $modelServiceGroup,
                        'group' => $modelServiceGroup, // ข้อมูลกลุ่มบริการ
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
    private function checkDept($modelService, $params, $logger)
    {
        if (!$modelService) { // ถ้าไม่พบข้อมูลแผนก
            $logger->error('ไม่พบข้อมูลแผนกในระบบคิว', ['msg' => 'ไม่พบข้อมูลแผนกในระบบคิว', 'dept_code' => $modelService['service_code']]); // save to log file
            Yii::$app->notify->sendMessage('ไม่พบข้อมูลแผนกในระบบคิว! ' . "\n" . Json::encode([
                'hn' => $params['user']['hn'],
                'fullname' => $params['user']['fullname'],
                'service' => $params['service'],
                'userid' => \Yii::$app->user->id
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
        $dirname = Yii::$app->formatter->asDate('now', 'php:Ym');
        $pathUpload = Yii::getAlias('@webroot/uploads/avatar/').$dirname;
        if(!is_dir($pathUpload)) {
            FileHelper::createDirectory($pathUpload);
        }
        $filepath = $pathUpload .'/'. $filename;
        $f = file_put_contents($filepath, $data);
        if ($f && file_exists($filepath)) {
            $modelStorage = new FileStorageItem();
            $modelStorage->base_url = '/uploads';
            $modelStorage->path = '/avatar/' .$dirname.'/'. $filename;
            $modelStorage->type = FileHelper::getMimeType($filepath);
            $modelStorage->size = filesize($filepath);
            $modelStorage->name = $security;
            $modelStorage->ref_id = $patient_id;
            $modelStorage->created_at = Enum::currentDate();
            if ($modelStorage->save()) {
                return $modelStorage;
            }
        } else {
            return null;
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
    public function actionServices($kioskId = null)
    {
        $response = [];
        $modelFloors = TblFloor::find()->all();
        if ($kioskId) {
            $modelKiosk = $this->findModelKiosk($kioskId);
            $serviceGroupIds = !empty($modelKiosk['service_groups']) ? Json::decode($modelKiosk['service_groups']) : [];
            // $serviceGroups = TblServiceGroup::find()->where(['service_group_id' => $serviceGroupIds])->orderBy('service_group_order asc')->all();
            foreach ($modelFloors as $key => $floor) {
                # code...
                $serviceGroups = TblServiceGroup::find()
                    ->where([
                        'service_group_id' => $serviceGroupIds, 
                        'floor_id' => $floor['floor_id'],
                        'queue_service_id' => 1
                    ])
                    ->orderBy('service_group_order asc')
                    ->all();
                $groups = [];
                foreach ($serviceGroups as $k => $serviceGroup) {
                    # code...
                    $services = TblService::find()
                        ->where([
                            'service_group_id' => $serviceGroup['service_group_id']
                        ])
                        ->isActive()
                        ->orderByAsc()
                        ->all();
                    $groups[] = [
                        'group' => $serviceGroup,
                        'services' => $services
                    ];
                }
                if($groups) {
                    $response[] = [
                        'floor' => $floor,
                        'groups' => $groups
                    ];
                }
            }
        } else {
            foreach ($modelFloors as $key => $floor) {
                # code...
                $serviceGroups = TblServiceGroup::find()
                    ->where([
                        'floor_id' => $floor['floor_id'],
                        'queue_service_id' => 1
                    ])
                    ->orderBy('service_group_order asc')
                    ->all();
                $groups = [];
                foreach ($serviceGroups as $k => $serviceGroup) {
                    # code...
                    $services = TblService::find()
                        ->where([
                            'service_group_id' => $serviceGroup['service_group_id']
                        ])
                        ->isActive()
                        ->orderByAsc()
                        ->all();
                    $groups[] = [
                        'group' => $serviceGroup,
                        'services' => $services
                    ];
                }
                if($groups) {
                    $response[] = [
                        'floor' => $floor,
                        'groups' => $groups
                    ];
                }
            }
            /* $response[] = [
                'dept_group' => ['dept_group_id' => time(), 'dept_group_name' => 'แผนกทั้งหมด'],
                'departments' => TblDept::find()->where([
                    'dept_status' => TblDept::STATUS_ACTIVE,
                ])
                ->orderBy('dept_order asc')
                ->all()
            ]; */
        }
        /* foreach ($deptGroups as $deptGroup) {
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
        } */
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
        if (!$patient || empty($q)) {
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
                $countDept = $countDept + $count;
                $dataDrilldown[] = [
                    $department['dept_name'],
                    intval($count)
                ];
            }
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
        $chartFloor = ChartBuilder::getChartFloor($startDate, $endDate);
        return [
            'countAll' => $countAll,
            'chart_floor' => $chartFloor
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
        /* $rows = AppQuery::getAllQueue(['startDate' => $startDate, 'endDate' => $endDate]);
        $response = [];
        foreach ($rows as $row) {
            $response[] = ArrayHelper::merge($row, [
                'base_url' => !empty($row['base_url']) ? Html::imgUrl($row['path']) : ''
            ]);
        } */
        return AppQuery::getAllQueue(['startDate' => $startDate, 'endDate' => $endDate]);
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
        if ($file) {
            unlink(Yii::getAlias('@app/web') . $file['base_url'] . $file['path']);
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
        if ($modelPatient->save()) {
            return [
                'queue' => $modelQueue,
                'patient' => $modelPatient
            ];
        } else {
            throw new HttpException(422, Json::encode($modelPatient->errors));
        }
    }

    // โปรไฟล์เซอร์วิส
    public function actionProfileServiceOptions($serviceId)
    {
        $response = [];
        $profiles = TblProfileService::find()->where([
            'profile_service_status' => TblProfileService::STATUS_ACTIVE,
            'queue_service_id' => $serviceId
        ])->all();
        foreach ($profiles as $key => $profile) {
            $counter = $this->findModelCounter($profile['counter_id']); // เคาน์เตอร์
            $counterServices = TblCounterService::find()->where(['counter_id' => $counter['counter_id']])->all();
            $counterExamination = TblCounter::findOne($profile['examination_counter_id']); // เคาน์เตอร์
            $services = TblService::find()->where(['service_id' => Json::decode($profile['service_id'])])->all();
            $examinations = TblService::find()->where(['service_id' => Json::decode($profile['examination_id'])])->all();
            // $counterServiceOptions = ArrayHelper::map($counterServices, 'counter_service_id', 'counter_service_name');
            $doctors = [];
            if($counterExamination){
                $counterServiceExaminations = TblCounterService::find()
                    ->where(['counter_id' => $counterExamination['counter_id']])
                    ->orderBy('counter_service_no asc')
                    ->all();
                foreach ($counterServiceExaminations as $key => $counterService) {
                    if(!empty($counterService['doctor_id'])) {
                        $doctor = TblDoctor::findOne($counterService['doctor_id']);
                        $doctors[] = [
                            'doctor_id' => $doctor['doctor_id'],
                            'doctor_name' => $doctor->fullname,
                            'counter_service_id' => $counterService['counter_service_id'],
                            'counter_service_name' => $counterService['counter_service_name']
                        ];
                    } else {
                        $doctors[] = [
                            'doctor_id' => 1,
                            'doctor_name' => 'ไม่ระบุแพทย์',
                            'counter_service_id' => $counterService['counter_service_id'],
                            'counter_service_name' => $counterService['counter_service_name']
                        ];
                    }
                }
            }
            $counterServiceOptions = [];
            foreach ($counterServices as $k => $counterService) {
                $counterServiceOptions[$counterService['counter_service_id']] = 
                !empty($counterService['doctor_id']) ? $counterService['counter_service_name'].' '.$counterService->doctor->fullname : $counterService['counter_service_name'];
            }
            $response[] = [
                'profile' => [
                    'counter_id' => $profile['counter_id'],
                    'profile_service_id' => $profile['profile_service_id'],
                    'profile_service_name' => $profile['profile_service_name'],
                    'profile_service_status' => $profile['profile_service_status'],
                    'queue_service_id' => $profile['queue_service_id'],
                    'serviceIds' => $profile['service_id'] ? Json::decode($profile['service_id']) : [],
                    'examinationIds' => $profile['examination_id'] ? Json::decode($profile['examination_id']) : [],
                    'queueServiceName' => $profile->queueService->queue_service_name
                ],
                'counter' => $counter,
                'counterServices' => $counterServices,
                'counterExamination' => $counterExamination,
                'services' => $services,
                'counterServiceOptions' => $counterServiceOptions,
                'examinations' => $examinations,
                'examinationOptions' => ArrayHelper::map($examinations, 'service_id', 'service_name'),
                'doctors' => $doctors
            ];
        }
        return $response;
    }

    // คิวรอเรียก
    public function actionDataWaiting()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataWaiting($params);
        /* $result = [];
        foreach ($data as $key => $queue) {
            $doc_name = '-';
            if($queue['appoint'] == 1) { // ถ้ามีนัด ให้หาชื่อแพทย์
                $appoints = Json::decode(Json::decode($queue['appoints']));
                if(is_array($appoints)) {
                    $mapAppoint = ArrayHelper::map($appoints, 'dept_code', 'doc_name');
                    $doc_name = ArrayHelper::getValue($mapAppoint, (string)$queue['service_code'], '-');
                }
                
            }
            $result[] = ArrayHelper::merge($queue, [
                'doc_name' => $doc_name
            ]);
        } */
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

    // คิวรอเรียกห้องตรวจ
    public function actionDataWaitingExamination()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataWaitingExamination($params);
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        return $data;
    }

    // คิวกำลังเรียก ห้องตรวจ
    public function actionDataCallerExamination()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataCallerExamination($params);
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        return $data;
    }

    // คิวพัก ห้องตรวจ
    public function actionDataHoldExamination()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $data = AppQuery::getDataHoldExamination($params);
        $response = \Yii::$app->getResponse();
        $response->setStatusCode(200);
        return $data;
    }

    // เรียกคิว กำลังรอเรียก
    public function actionCallWait()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        if(TblCaller::findOne(['queue_id' => $modelQueue['queue_id']]) !== null){
            throw new HttpException(422, 'คิวนี้ถูกเรียกไปแล้ว');
        }
        if($modelServiceGroup['queue_service_id'] == 2) { // ถ้าเรียกคิวจากห้องตรวจ ให้ตรวจสอบว่าคิวซักประวัติเสร็จสิ้นหรือยัง
            $modelQueueHistory = $this->findModelQueue($modelQueue['parent_id']); // คิวซักประวัติที่ออกบัตรคิวมาให้
            if($modelQueueHistory['queue_status_id'] != TblQueue::STATUS_END) {
                throw new HttpException(422, 'ไม่สามารถเรียกคิวได้ เนื่องจากสถานะซักประวัติยังไม่เสร็จสิ้น!');
            }
        }
        $modelCall = new TblCaller();
        $modelCall->setAttributes([
            'queue_id' => $params['queue']['queue_id'], // รหัสคิว
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
            'call_time' => Enum::currentDate(), // เวลาเรียก
            'group_key' => $modelCall->getGroupKey(), // กลุ่มคิว
            'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
        if ($modelCall->save() && $modelQueue->save()) {
            $callers = AppQuery::getCallerByGroupkey($modelCall['group_key']);
            $queue = ArrayHelper::merge($params['queue'], [
                'queue_status_id' => TblQueue::STATUS_CALL,
                'counter_id' => $modelCall['counter_id'],
                'counter_service_id' => $modelCall['counter_service_id'],
                'counter_service_name' => $modelCounterService['counter_service_name'],
                'counter_service_no' => $modelCounterService['counter_service_no'],
                'call_time' => $modelCall['call_time']
            ]);
            $params['queue'] = $queue;
            return ArrayHelper::merge($params, [
                'sources' => [
                    'urls' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                    'title' => $queue['hn'],
                    'artist' => $queue['fullname'],
                    'pic' => Url::base(true).'/images/cbimage.jpg'
                ],
                'event_on' => 'tbl_wait',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService,
                'call_groups' => ArrayHelper::getColumn($callers, 'queue_no')
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // เรียกคิวห้องตรวจ
    public function actionCallWaitEx()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelQueueHistory = $this->findModelQueue($modelQueue['parent_id']); // คิวซักประวัติที่ออกบัตรคิวมาให้
        if($modelQueueHistory['queue_status_id'] != TblQueue::STATUS_END) {
            throw new HttpException(422, 'ไม่สามารถเรียกคิวได้ เนื่องจากสถานะซักประวัติยังไม่เสร็จสิ้น!');
        }
        
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        
        $modelCall = TblCaller::findOne(['queue_id' => $modelQueue['queue_id']]);
        $modelCall->setAttributes([
            'queue_id' => $params['queue']['queue_id'], // รหัสคิว
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
            'call_time' => Enum::currentDate(), // เวลาเรียก
            'group_key' => $modelCall->getGroupKey(), // กลุ่มคิว
            'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
        if ($modelCall->save() && $modelQueue->save()) {
            $callers = AppQuery::getCallerByGroupkey($modelCall['group_key']);
            $queue = ArrayHelper::merge($params['queue'], [
                'queue_status_id' => TblQueue::STATUS_CALL,
                'counter_id' => $modelCall['counter_id'],
                'counter_service_id' => $modelCall['counter_service_id'],
                'counter_service_name' => $modelCounterService['counter_service_name'],
                'counter_service_no' => $modelCounterService['counter_service_no'],
                'call_time' => $modelCall['call_time']
            ]);
            $params['queue'] = $queue;
            return ArrayHelper::merge($params, [
                'sources' => [
                    'urls' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                    'title' => $queue['hn'],
                    'artist' => $queue['fullname'],
                    'pic' => Url::base(true).'/images/cbimage.jpg'
                ],
                'event_on' => 'tbl_wait',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService,
                'call_groups' => ArrayHelper::getColumn($callers, 'queue_no')
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // เรียกคิวที่เลือก
    public function actionCallSelected()
    {
        $response = [];
        $params = \Yii::$app->getRequest()->getBodyParams();
        $queues = $params['queues'];
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        $groupKey = \Yii::$app->security->generateRandomString();
        $group_queue_no = [];
        foreach ($queues as $key => $queue) {
            $group_queue_no[] = $queue['queue_no'];
        }
        $transaction = TblCaller::getDb()->beginTransaction();
        try {
            foreach ($queues as $key => $queue) {
                # code...
                $modelQueue = $this->findModelQueue($queue['queue_id']);
                $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
                $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
                if(TblCaller::findOne(['queue_id' => $modelQueue['queue_id']]) !== null){
                    continue;
                }
                if($modelServiceGroup['queue_service_id'] == 2) { // ถ้าเรียกคิวจากห้องตรวจ ให้ตรวจสอบว่าคิวซักประวัติเสร็จสิ้นหรือยัง
                    $modelQueueHistory = $this->findModelQueue($modelQueue['parent_id']); // คิวซักประวัติที่ออกบัตรคิวมาให้
                    if($modelQueueHistory['queue_status_id'] != TblQueue::STATUS_END) {
                        $transaction->rollBack();
                        throw new HttpException(422, 'ไม่สามารถเรียกคิวได้ เนื่องจากสถานะซักประวัติยังไม่เสร็จสิ้น!');
                    }
                }
                $modelCall = new TblCaller();
                $modelCall->setAttributes([
                    'queue_id' => $queue['queue_id'], // รหัสคิว
                    'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
                    'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
                    'call_time' => Enum::currentDate(), // เวลาเรียก
                    'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
                    'group_key' => $groupKey, // กลุ่มคิว
                    'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
                ]);
                $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
                if ($modelCall->save() && $modelQueue->save()) {
                    $queue = ArrayHelper::merge($queue, [
                        'queue_status_id' => TblQueue::STATUS_CALL,
                        'counter_id' => $modelCall['counter_id'],
                        'counter_service_id' => $modelCall['counter_service_id'],
                        'counter_service_name' => $modelCounterService['counter_service_name'],
                        'counter_service_no' => $modelCounterService['counter_service_no'],
                        'call_time' => $modelCall['call_time']
                    ]);
                    $response[] = [
                        'counter' => $params['counter'],
                        'counter_service' => $params['counter_service'],
                        'profile_service' => $params['profileService'],
                        'queue' => $queue,
                        'sources' => [
                            'urls' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                            'title' => $queue['hn'],
                            'artist' => $queue['fullname'],
                            'pic' => Url::base(true).'/images/cbimage.jpg'
                        ],
                        'event_on' => 'tbl_wait',
                        'caller' => $modelCall,
                        'group' => $modelServiceGroup,
                        'service' => $modelService,
                        'call_groups' => $group_queue_no
                    ];
                } else {
                    $transaction->rollBack();
                    throw new HttpException(422, Json::encode($modelCall->errors));
                }
            }
            // ...other DB operations...
            $transaction->commit();
            return $response;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    // เรียกคิวห้องตรวจที่เลือก
    public function actionCallSelectedEx()
    {
        $response = [];
        $params = \Yii::$app->getRequest()->getBodyParams();
        $queues = $params['queues'];
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        $groupKey = \Yii::$app->security->generateRandomString();
        $group_queue_no = [];
        foreach ($queues as $key => $queue) {
            $group_queue_no[] = $queue['queue_no'];
        }
        $transaction = TblCaller::getDb()->beginTransaction();
        try {
            foreach ($queues as $key => $queue) {
                # code...
                $modelQueue = $this->findModelQueue($queue['queue_id']);
                $modelQueueHistory = $this->findModelQueue($modelQueue['parent_id']); // คิวซักประวัติที่ออกบัตรคิวมาให้
                if($modelQueueHistory['queue_status_id'] != TblQueue::STATUS_END) {
                    $transaction->rollBack();
                    throw new HttpException(422, 'ไม่สามารถเรียกคิวได้ เนื่องจากสถานะซักประวัติยังไม่เสร็จสิ้น!');
                }
                $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
                $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
                $modelCall = TblCaller::findOne(['queue_id' => $modelQueue['queue_id']]);
                $modelCall->setAttributes([
                    'queue_id' => $queue['queue_id'], // รหัสคิว
                    'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
                    'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
                    'call_time' => Enum::currentDate(), // เวลาเรียก
                    'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
                    'group_key' => $groupKey, // กลุ่มคิว
                    'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
                ]);
                $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
                if ($modelCall->save() && $modelQueue->save()) {
                    $queue = ArrayHelper::merge($queue, [
                        'queue_status_id' => TblQueue::STATUS_CALL,
                        'counter_id' => $modelCall['counter_id'],
                        'counter_service_id' => $modelCall['counter_service_id'],
                        'counter_service_name' => $modelCounterService['counter_service_name'],
                        'counter_service_no' => $modelCounterService['counter_service_no'],
                        'call_time' => $modelCall['call_time']
                    ]);
                    $response[] = [
                        'counter' => $params['counter'],
                        'counter_service' => $params['counter_service'],
                        'profile_service' => $params['profileService'],
                        'queue' => $queue,
                        'sources' => [
                            'urls' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                            'title' => $queue['hn'],
                            'artist' => $queue['fullname'],
                            'pic' => Url::base(true).'/images/cbimage.jpg'
                        ],
                        'event_on' => 'tbl_wait',
                        'caller' => $modelCall,
                        'group' => $modelServiceGroup,
                        'service' => $modelService,
                        'call_groups' => $group_queue_no
                    ];
                } else {
                    $transaction->rollBack();
                    throw new HttpException(422, Json::encode($modelCall->errors));
                }
            }
            // ...other DB operations...
            $transaction->commit();
            return $response;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    // เสร็จสิ้นคิว กำลังรอเรียก
    public function actionEndWait()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue']['queue_id']);
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        if($modelQueue['queue_status_id'] == 4){
            throw new HttpException(422, 'คิวนี้เสร็จสิ้นไปแล้ว');
        }
        $modelCall = new TblCaller();
        $modelCall->setAttributes([
            'queue_id' => $params['queue']['queue_id'], // รหัสคิว
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'call_time' => Enum::currentDate(), // เวลาเรียก
            'end_time' => Enum::currentDate(), // เวลาเสร็จสิ้น
            'group_key' => $modelCall->getGroupKey(), // กลุ่มคิว
            'caller_status' => TblCaller::STATUS_CALL_END // 1
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_END;

        $is_issue_ex = false; // สถานะว่าออกบัตรคิวห้องตรวจแล้วหรือยัง
        if($modelServiceGroup['queue_service_id'] == 1) {// ถ้าเป็นคิวซักประวัติ
            $count = AppQuery::checkRegisterEx($params['queue']);
            $is_issue_ex = $count > 0;
        }

        if ($modelCall->save() && $modelQueue->save()) {
            $queue = ArrayHelper::merge($params['queue'], [
                'queue_status_id' => TblQueue::STATUS_END
            ]);
            $params['queue'] = $queue;
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_wait',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService,
                'is_issue_ex' => $is_issue_ex
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
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
            'call_time' => Enum::currentDate(), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
        if ($modelCall->save() && $modelQueue->save()) {
            $callers = AppQuery::getCallerByGroupkey($modelCall['group_key']);
            $params['queue']['queue_status_id'] = $modelQueue['queue_status_id'];
            $params['queue']['counter_id'] = $modelCall['counter_id'];
            $params['queue']['counter_service_id'] = $modelCall['counter_service_id'];
            $params['queue']['counter_service_name'] = $modelCounterService['counter_service_name'];
            $params['queue']['counter_service_no'] = $modelCounterService['counter_service_no'];
            $params['queue']['call_time'] = $modelCall['call_time'];
            $params['counter_service']['key'] = $modelCounterService['counter_service_id'];
            $params['counter_service']['value'] = $modelCounterService['counter_service_name'];
            return ArrayHelper::merge($params, [
                'sources' => [
                    'urls' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                    'title' => $params['queue']['hn'],
                    'artist' => $params['queue']['fullname'],
                    'pic' => Url::base(true).'/images/cbimage.jpg'
                ],
                'event_on' => 'tbl_caller',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService,
                'call_groups' => ArrayHelper::getColumn($callers, 'queue_no')
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
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'hold_time' => Enum::currentDate(), // เวลาพักคิว
            'caller_status' => TblCaller::STATUS_CALL_END // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_HOLD;
        if ($modelCall->save() && $modelQueue->save()) {
            $params['queue']['queue_status_id'] = $modelQueue['queue_status_id'];
            $params['queue']['hold_time'] = $modelCall['hold_time'];
            $params['queue']['counter_id'] = $modelCall['counter_id'];
            $params['queue']['counter_service_id'] = $modelCall['counter_service_id'];
            $params['queue']['counter_service_name'] = $modelCounterService['counter_service_name'];
            $params['queue']['counter_service_no'] = $modelCounterService['counter_service_no'];
            $params['counter_service']['key'] = $modelCounterService['counter_service_id'];
            $params['counter_service']['value'] = $modelCounterService['counter_service_name'];
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_caller',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService
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
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'end_time' => Enum::currentDate(), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL_END // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_END;

        $is_issue_ex = false; // สถานะว่าออกบัตรคิวห้องตรวจแล้วหรือยัง
        if($modelServiceGroup['queue_service_id'] == 1) {// ถ้าเป็นคิวซักประวัติ
            $count = AppQuery::checkRegisterEx($params['queue']);
            $is_issue_ex = $count > 0;
        }

        if ($modelCall->save() && $modelQueue->save()) {
            $params['queue']['queue_status_id'] = $modelQueue['queue_status_id'];
            $params['queue']['counter_id'] = $modelCall['counter_id'];
            $params['queue']['counter_service_id'] = $modelCall['counter_service_id'];
            $params['queue']['counter_service_name'] = $modelCounterService['counter_service_name'];
            $params['queue']['counter_service_no'] = $modelCounterService['counter_service_no'];
            $params['counter_service']['key'] = $modelCounterService['counter_service_id'];
            $params['counter_service']['value'] = $modelCounterService['counter_service_name'];
            $params['queue']['end_time'] = $modelCall['end_time'];
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_caller',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService,
                'is_issue_ex' => $is_issue_ex
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
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
            'call_time' => Enum::currentDate(), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_CALL;
        if ($modelCall->save() && $modelQueue->save()) {
            $callers = AppQuery::getCallerByGroupkey($modelCall['group_key']);
            $params['queue']['queue_status_id'] = $modelQueue['queue_status_id'];
            $params['queue']['call_time'] = $modelCall['call_time'];
            $params['queue']['counter_id'] = $modelCall['counter_id'];
            $params['queue']['counter_service_id'] = $modelCall['counter_service_id'];
            $params['queue']['counter_service_name'] = $modelCounterService['counter_service_name'];
            $params['queue']['counter_service_no'] = $modelCounterService['counter_service_no'];
            $params['counter_service']['key'] = $modelCounterService['counter_service_id'];
            $params['counter_service']['value'] = $modelCounterService['counter_service_name'];
            return ArrayHelper::merge($params, [
                'sources' => [
                    'urls' => $this->getSourceMediaFiles($modelQueue['queue_no'], $params['counter_service']['key']),
                    'title' => $params['queue']['hn'],
                    'artist' => $params['queue']['fullname'],
                    'pic' => Url::base(true).'/images/cbimage.jpg'
                ],
                'event_on' => 'tbl_hold',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService,
                'call_groups' => ArrayHelper::getColumn($callers, 'queue_no')
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
        $modelService = $this->findModelService($modelQueue['service_id']); // บริการ
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelCall = $this->findModelCaller($params['queue']['caller_id']);
        $modelCounterService = $this->findModelCounterService($params['counter_service']['key']);
        $modelCall->setAttributes([
            'counter_id' => $params['counter']['counter_id'], // รหัสเคาน์เตอร์
            'counter_service_id' => $params['counter_service']['key'], // รหัสช่องบริการ
            'end_time' => Enum::currentDate(), // เวลาเรียก
            'caller_status' => TblCaller::STATUS_CALL_END // สถานะกำลังเรียก 0
        ]);
        $modelQueue->queue_status_id = TblQueue::STATUS_END;

        $is_issue_ex = false; // สถานะว่าออกบัตรคิวห้องตรวจแล้วหรือยัง
        if($modelServiceGroup['queue_service_id'] == 1) {// ถ้าเป็นคิวซักประวัติ
            $count = AppQuery::checkRegisterEx($params['queue']);
            $is_issue_ex = $count > 0;
        }

        if ($modelCall->save() && $modelQueue->save()) {
            $params['queue']['queue_status_id'] = $modelQueue['queue_status_id'];
            $params['queue']['end_time'] = $modelCall['end_time'];
            $params['queue']['call_time'] = $modelCall['call_time'];
            $params['queue']['counter_id'] = $modelCall['counter_id'];
            $params['queue']['counter_service_id'] = $modelCall['counter_service_id'];
            $params['queue']['counter_service_name'] = $modelCounterService['counter_service_name'];
            $params['queue']['counter_service_no'] = $modelCounterService['counter_service_no'];
            $params['counter_service']['key'] = $modelCounterService['counter_service_id'];
            $params['counter_service']['value'] = $modelCounterService['counter_service_name'];
            return ArrayHelper::merge($params, [
                'event_on' => 'tbl_hold',
                'caller' => $modelCall,
                'group' => $modelServiceGroup,
                'service' => $modelService,
                'is_issue_ex' => $is_issue_ex
            ]);
        } else {
            throw new HttpException(422, Json::encode($modelCall->errors));
        }
    }

    // ไฟล์เสียงเรียก
    protected function getSourceMediaFiles($number, $counter_service_id)
    {
        /* $cache = \Yii::$app->cache;
        $source = $cache->get('S_'.$number);
        if ($source === false) { */
        $component = \Yii::createObject([
            'class' => SoundComponent::className(),
            'number' => $number,
            'counter_service_id' => $counter_service_id
        ]);
        $source = $component->getSource();
        // $cache->set('S_'.$number, $source, 3600*3);
        return $source;
        /* } else {
            return $source;
        } */
    }

    // ออกบัตรคิวห้องตรวจ
    public function actionRegisterExamination()
    {
        $logger = Yii::$app->logger->getLogger();
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $params = \Yii::$app->getRequest()->getBodyParams();
        $oldQueue = $this->findModelQueue($params['queue']['queue_id']); // ข้อมูลคิวซักประวัติ
        $oldPatient = $this->findModelPatient($oldQueue['patient_id']); // ข้อมูลผู้ป่วยจากคิวซักประวัติ
        $modelService = $this->findModelService($params['examinationId']); // ห้องตรวจที่ออกบัตรคิว
        $modelServiceGroup = $this->findModelServiceGroup($modelService['service_group_id']); // กลุ่มบริการ
        $modelStorage = FileStorageItem::findOne(['ref_id' => $oldQueue['patient_id']]); // ไฟล์ภาพ
        $modelQueueService = $this->findModelQueueService($modelServiceGroup['queue_service_id']); // ประเภทคิวบริการ
        $counterService = $this->findModelCounterService($params['counter_service_id']);
        $imgUrl = '';
        if ($modelStorage) { // ถ้ามีรูปภาพเดิมจากคิวซักประวัติ
            $imgUrl = Html::imgUrl($modelStorage['path']); // ลิ้งค์รูปภาพ
        }

        $rows = (new \yii\db\Query())
            ->select(['tbl_queue.*'])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_queue.service_group_id')
            ->where([
                'tbl_queue.service_group_id' => $modelServiceGroup['service_group_id'],
                'tbl_queue.service_id' => $modelService['service_id'],
                'tbl_patient.hn' => $oldPatient['hn'],
                'tbl_service_group.queue_service_id' => 2
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->all();

        if($rows && $rows !== null) {
            throw new HttpException(422, 'คิวนี้ออกคิวห้องตรวจไปแล้ว!');
        }

        $transaction = TblPatient::getDb()->beginTransaction();
        try {
            // ข้อมูลผู้ป่วย

            $modelPatient = new TblPatient();
            $modelPatient->hn = $oldPatient['hn'];
            $modelPatient->cid = $oldPatient['cid'];
            $modelPatient->title = $oldPatient['title'];
            $modelPatient->firstname = $oldPatient['firstname'];
            $modelPatient->lastname = $oldPatient['lastname'];
            $modelPatient->fullname = $oldPatient['fullname'];
            $modelPatient->birth_date = $oldPatient['birth_date'];
            $modelPatient->age = $oldPatient['age'];
            $modelPatient->blood_group = $oldPatient['blood_group'];
            $modelPatient->nation = $oldPatient['nation'];
            $modelPatient->address = $oldPatient['address'];
            $modelPatient->occ = $oldPatient['occ'];
            $modelPatient->appoint = $oldPatient['appoint'];
            $modelPatient->maininscl_name = $oldPatient['maininscl_name'];
            $modelPatient->subinscl_name = $oldPatient['subinscl_name'];

            $oldQueue->issue_card_ex = 1; // สถานะออกบัตรคิวห้องตรวจ

            if($modelPatient->save() && $oldQueue->save()) {
                // ถ้ามีรูปภาพเดิมจากคิวซักประวัติ ให้สร้าง record ใหม่
                if($modelStorage){
                    $modelStorage->id = null;
                    $modelStorage->isNewRecord = true;
                    $modelStorage->ref_id = $modelPatient['patient_id'];
                    $modelStorage->created_at = Enum::currentDate();
                    $modelStorage->save(false);
                }
                // ข้อมูลคิว
                $modelQueue = new TblQueue();
                $modelQueue->service_id = $modelService['service_id'];
                $modelQueue->service_group_id = $modelServiceGroup['service_group_id'];
                $modelQueue->setAttributes([
                    'patient_id' => $modelPatient['patient_id'], // ไอดีผู้ป่วย
                    //'service_group_id' => $modelServiceGroup['service_group_id'], // กลุ่มบริการ
                    //'service_id' => $modelService['service_id'], // ชื่อบริการ
                    'priority_id' => $oldQueue['priority_id'], // ประเภทคิว
                    'queue_station' => $oldQueue['queue_station'], // ออกคิวจากตู้หรือ one stop
                    'case_patient' => $oldQueue['case_patient'], // กรณีผู้ป่วย
                    'appoint' => $oldQueue['appoint'], // คิวนัด
                    'parent_id' => $oldQueue['queue_id'], // ออกคิวจาก
                    'doctor_id' => $counterService['doctor_id'], // แพทย์
                    'queue_status_id' => TblQueue::STATUS_WAIT, // default รอเรียก
                    'issue_card_ex' => 0
                ]);
                if ($modelQueue->save()) {
                    $modelCounterService = $this->findModelCounterService($params['counter_service_id']);
                    $modelCall = new TblCaller();
                    $modelCall->setAttributes([
                        'queue_id' => $modelQueue['queue_id'], // รหัสคิว
                        'counter_id' => $modelCounterService['counter_id'], // รหัสเคาน์เตอร์
                        'counter_service_id' => $modelCounterService['counter_service_id'], // รหัสช่องบริการ
                        'profile_service_id' => $params['profileService']['profile_service_id'], // โปรไฟล์เซอร์วิส
                        // 'call_time' => Enum::currentDate(), // เวลาเรียก
                        'group_key' => $modelCall->getGroupKey(), // กลุ่มคิว
                        'caller_status' => TblCaller::STATUS_CALL_END // เรียกเสร็จ
                    ]);

                    if(!$modelCall->save()) {
                        $transaction->rollBack();
                        throw new HttpException(422, Json::encode($modelCall->errors));
                    }

                    $doctor = TblDoctor::findOne($modelQueue['doctor_id']);
                    $queue = [
                        'queue_id' => $modelQueue['queue_id'],
                        'queue_no' => $modelQueue['queue_no'],
                        'patient_id' => $modelQueue['patient_id'],
                        'service_group_id' => $modelQueue['service_group_id'],
                        'service_id' => $modelQueue['service_id'],
                        'priority_id' => $modelQueue['priority_id'],
                        'queue_station' => $modelQueue['queue_station'],
                        'case_patient' => $modelQueue['case_patient'],
                        'appoint' => $modelQueue['appoint'],
                        'queue_status_id' => $modelQueue['queue_status_id'],
                        'cid' => !empty($modelPatient['cid']) ? substr_replace($modelPatient['cid'], "****", 9) : '',
                        'fullname' => $modelPatient['fullname'],
                        'hn' => $modelPatient['hn'],
                        'maininscl_name' => $modelPatient['maininscl_name'],
                        'print_time' => Yii::$app->formatter->asDate($modelQueue['created_at'], 'php:Y-m-d H:i:s'),
                        'service_group_name' => $modelServiceGroup['service_group_name'],
                        'service_name' => $modelService['service_name'],
                        'created_at' => $modelQueue['created_at'],
                        'updated_at' => $modelQueue['updated_at'],
                        'created_by' => $modelQueue['created_by'],
                        'updated_by' => $modelQueue['updated_by'],
                        'name' => $modelQueue->profile ? $modelQueue->profile->name : '',
                        'base_url' => $modelStorage ? $modelStorage['base_url'] : '',
                        'path' => $modelStorage ? $modelStorage['path'] : '',
                        'queue_service_name' => $modelQueueService['queue_service_name'],
                        'doctor_name' => $doctor ? $doctor->fullname : '-',
                        'issue_card_ex' => $modelQueue['issue_card_ex'],
                        'parent_id' => $modelQueue['parent_id'],
                        'counter_id' => $modelCall['counter_id'],
                        'counter_service_id' => $modelCall['counter_service_id'],
                        'counter_service_name' => $modelCounterService['counter_service_name'],
                        'counter_service_no' => $modelCounterService['counter_service_no'],
                        'doctor_id' => $modelQueue['doctor_id'] // แพทย์
                    ];
                    $params['queue']['issue_card_ex'] = '1';
                    $transaction->commit();
                    $response = \Yii::$app->getResponse();
                    $response->setStatusCode(201);
                    return [
                        'queue' => $queue, // ข้อมูลคิว
                        'oldQueue' => $params['queue'],
                        'patient' => $modelPatient, // ข้อมูลผู้ป่วย
                        'service' => $modelService, // ข้อมูลบริการ
                        'serviceGroup' => $modelServiceGroup,
                        'group' => $modelServiceGroup, // ข้อมูลกลุ่มบริการ
                        'imgUrl' => $imgUrl // ลิ้งค์ภาพโปรไฟล์
                    ];
                } else {
                    $transaction->rollBack();
                    $logdata = [
                        'msg' => $modelQueue->errors,
                        'data' => $params
                    ];
                    $logger->error('ออกบัตรคิวห้องตรวจ', $logdata); // save to log file
                    Yii::$app->notify->sendMessage('ออกบัตรคิวห้องตรวจไม่สำเร็จ! ' . "\n" . Json::encode([
                        'hn' => $params['queue']['hn'],
                        'fullname' => $params['queue']['fullname'],
                        'old_queue_id' => $params['queue']['queue_id'],
                    ])); // send to line notify
                    throw new HttpException(422, Json::encode($modelQueue->errors));
                }
            } else {
                $transaction->rollBack();
                $logdata = [
                    'msg' => $modelPatient->errors,
                    'data' => $params
                ];
                $logger->error('ออกบัตรคิวห้องตรวจ', $logdata); // save to log file
                Yii::$app->notify->sendMessage('ออกบัตรคิวห้องตรวจไม่สำเร็จ! ' . "\n" . Json::encode([
                    'hn' => $params['queue']['hn'],
                    'fullname' => $params['queue']['fullname'],
                    'old_queue_id' => $params['queue']['queue_id'],
                ]));
                throw new HttpException(422, Json::encode($modelPatient->errors));
            }
        } catch(\Exception $e) {
            $transaction->rollBack();
            $logger->error('ออกบัตรคิวห้องตรวจ', ['msg' => $e]);
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            $logger->error('ออกบัตรคิวห้องตรวจ', ['msg' => $e]);
            throw $e;
        }
    }

    public function actionCheckRegisterEx()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $count = AppQuery::checkRegisterEx($params['queue']);
        if($count > 0) {
            return [
                'status' => true
            ];
        } else {
            return [
                'status' => false
            ];
        }
    }

    // โปรแกรมเสียงเรียก
    public function actionPlayStations()
    {
        $playStations = TblPlayStation::find()
            ->where([
                'play_station_status' => TblPlayStation::STATUS_ACTIVE
            ])
            ->all();
        $response = [];
        foreach ($playStations as $key => $playStation) {
            # code...
            $counterIds = !empty($playStation['counter_id']) ? unserialize($playStation['counter_id']) : [];
            $counterServiceIds = !empty($playStation['counter_service_id']) ? unserialize($playStation['counter_service_id']) : [];
            $counters = TblCounter::find()
            ->where([
                'counter_id' => $counterIds
            ])
            ->all();
            $playStation->counter_id = $counterIds;
            $playStation->counter_service_id = $counterServiceIds;
            $counterServices = TblCounterService::find()
                ->where([
                    'counter_service_id' => $counterServiceIds
                ])
                ->all();
            $response[] = [
                'counterIds' => $counterIds,
                'counterServiceIds' => $counterServiceIds,
                'counters' => $counters,
                'counterServices' => $counterServices,
                'station' => $playStation
            ];
        }
        return $response;
    }

    public function actionGetPlayStation($id)
    {
        $playStation = $this->findModelPlayStation($id);
        $playStation->last_active_date = Enum::currentDate();
        $playStation->save();
        $counterIds = !empty($playStation['counter_id']) ? unserialize($playStation['counter_id']) : [];
        $counterServiceIds = !empty($playStation['counter_service_id']) ? unserialize($playStation['counter_service_id']) : [];
        $counters = TblCounter::find()
        ->where([
            'counter_id' => $counterIds
        ])
        ->all();
        $playStation->counter_id = $counterIds;
        $playStation->counter_service_id = $counterServiceIds;
        $counterServices = TblCounterService::find()
            ->where([
                'counter_service_id' => $counterServiceIds
            ])
            ->all();
        return [
            'counterIds' => $counterIds,
            'counterServiceIds' => $counterServiceIds,
            'counters' => $counters,
            'counterServices' => $counterServices,
            'station' => $playStation
        ];
    }

    // อัพเดทสถานะเรียกคิว
    public function actionUpdateCallStatus()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        try {
            Yii::$app->db->createCommand()->update('tbl_caller', [
                'caller_status' => TblCaller::STATUS_CALL_END, // เรียกเสร็จ
                'updated_at' => Enum::currentDate()
            ],
            [
                'caller_id' => $params['caller_id']
            ])->execute();
            $modelCall = $this->findModelCaller($params['caller_id']);
            return [
                'message' => 'Success',
                'data' => $modelCall
            ];
        } catch (\Throwable $th) {
            //throw $th;
            throw new HttpException(422, Json::encode($th));
        }
    }

    // จอแสดงผล
    public function actionDisplayList()
    {
        $displays = TblDisplay::find()->where(['display_status' => TblDisplay::STATUS_ACTIVE])->all();
        $response = [];
        foreach ($displays as $key => $display) {
            $counterIds = empty($display['counter_id']) ? [] : unserialize($display['counter_id']);
            $serviceIds = empty($display['service_id']) ? [] : unserialize($display['service_id']);
            $counters = TblCounter::find()->where(['counter_id' => $counterIds])->all();
            $services = (new \yii\db\Query())
                ->select([
                    'tbl_service.*', 
                    'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name',
                    'tbl_service_group.*'
                ])
                ->from('tbl_service')
                ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
                ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
                ->where([
                    'tbl_service.service_status' => 1,
                    'tbl_service.service_id' => $serviceIds
                ])
                ->all();
            # code...
            $response[] = [
                'display_id' => $display['display_id'],
                'display_name' => $display['display_name'],
                'display_css' => $display['display_css'],
                'page_length' => $display['page_length'],
                'counterIds' => $counterIds,
                'serviceIds' => $serviceIds,
                'counters' => $counters
            ];
        }
        return  $response;
    }

    public function actionGetDisplay($id)
    {
        $display = $this->findModelDisplay($id);
        $counterIds = empty($display['counter_id']) ? [] : unserialize($display['counter_id']);
        $serviceIds = empty($display['service_id']) ? [] : unserialize($display['service_id']);
        $counters = TblCounter::find()->where(['counter_id' => $counterIds])->all();
        $services = (new \yii\db\Query())
            ->select([
                'tbl_service.*', 
                'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name',
                'tbl_service_group.*'
            ])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_service.service_status' => 1,
                'tbl_service.service_id' => $serviceIds
            ])
            ->all();
        return [
            'display_id' => $display['display_id'],
            'display_name' => $display['display_name'],
            'display_css' => $display['display_css'],
            'page_length' => $display['page_length'],
            'counterIds' => $counterIds,
            'serviceIds' => $serviceIds,
            'counters' => $counters
        ];
    }

    public function actionQueuePlayList()
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $rows = (new \yii\db\Query())
            ->select([
                'tbl_caller.caller_id',
                'tbl_caller.queue_id',
                'tbl_caller.counter_id',
                'tbl_caller.counter_service_id',
                'tbl_caller.profile_service_id',
                'tbl_caller.call_time',
                'tbl_caller.hold_time',
                'tbl_caller.end_time',
                'tbl_caller.caller_status',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.priority_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.queue_status_id',
                'tbl_queue.appoint',
                'tbl_queue.parent_id',
                'tbl_queue.created_at',
                'tbl_queue.updated_at',
                'tbl_queue.created_by',
                'tbl_queue.updated_by',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_service_group.queue_service_id'
            ])
            ->from('tbl_caller')
            ->innerJoin('tbl_queue', 'tbl_queue.queue_id = tbl_caller.queue_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->where([
                'tbl_caller.caller_status' => TblCaller::STATUS_CALL,
                'tbl_queue.queue_status_id' => TblQueue::STATUS_CALL
            ])
            ->andWhere(['between', 'tbl_caller.created_at', $startDate, $endDate])
            ->groupBy('tbl_caller.caller_id')
            ->orderBy('tbl_caller.call_time ASC')
            ->all();
        $response = [];
        foreach ($rows as $key => $row) {
            $modelQueue = $this->findModelQueue($row['queue_id']);
            $modelPatient = $this->findModelPatient($modelQueue['patient_id']);
            $modelCall = $this->findModelCaller($row['caller_id']);
            $modelCounterService = $this->findModelCounterService($row['counter_service_id']);
            $modelCounter = $this->findModelCounter($row['counter_id']);
            $modelProfileService = $this->findModelProfileService($row['profile_service_id']);
            $modelStorage = FileStorageItem::find()->findByRefId($modelPatient['patient_id']);
            $modelQueueService = $this->findModelQueueService($row['queue_service_id']);
            $modelService = $this->findModelService($modelQueue['service_id']);
            $sources = [
                'urls' => $this->getSourceMediaFiles($modelQueue['queue_no'], $row['counter_service_id']),
                'title' => $modelPatient['hn'],
                'artist' => $modelPatient['fullname'],
                'pic' => Url::base(true).'/images/cbimage.jpg'
            ];
            $callers = AppQuery::getCallerByGroupkey($modelCall['group_key']);
            $response[] = [
                'caller' => $modelCall,
                'counter' => $modelCounter,
                'counter_service' => [
                    'key' => $modelCounterService['counter_service_id'],
                    'value' => $modelCounterService['counter_service_name']
                ],
                'profileService' =>  [
                    'counter_id' => $modelProfileService['counter_id'],
                    'profile_service_id' => $modelProfileService['profile_service_id'],
                    'profile_service_name' => $modelProfileService['profile_service_name'],
                    'profile_service_status' => $modelProfileService['profile_service_status'],
                    'queue_service_id' => $modelProfileService['queue_service_id'],
                    'serviceIds' => $modelProfileService['service_id'] ? Json::decode($modelProfileService['service_id']) : [],
                    'examinationIds' => $modelProfileService['examination_id'] ? Json::decode($modelProfileService['examination_id']) : [],
                    'queueServiceName' => $modelProfileService->queueService->queue_service_name
                ],
                'queue' => [
                    'queue_id' => $modelQueue['queue_id'],
                    'queue_no' => $modelQueue['queue_no'],
                    'patient_id' => $modelQueue['patient_id'],
                    'service_group_id' => $modelQueue['service_group_id'],
                    'service_id' => $modelQueue['service_id'],
                    'priority_id' => $modelQueue['priority_id'],
                    'queue_station' => $modelQueue['queue_station'],
                    'case_patient' => $modelQueue['case_patient'],
                    'queue_status_id' => $modelQueue['queue_status_id'],
                    'created_at' => $modelQueue['created_at'],
                    'updated_at' => $modelQueue['updated_at'],
                    'created_by' => $modelQueue['created_by'],
                    'updated_by' => $modelQueue['updated_by'],
                    'name' => $modelQueue->profile ? $modelQueue->profile->name : '',
                    'base_url' => $modelStorage ? $modelStorage['base_url'] : '',
                    'path' => $modelStorage ? $modelStorage['path'] : '',
                    'queue_service_name' => $modelQueueService['queue_service_name'],
                    'fullname' => $modelPatient['fullname'],
                    'maininscl_name' => $modelPatient['maininscl_name'],
                    'cid' => !empty($modelPatient['cid']) ? substr_replace($modelPatient['cid'], "****", 9) : '',
                    'hn' => $modelPatient['hn'],
                    'service_name' => $modelService['service_name'],
                    'service_code' => $modelService['service_code'],
                    'print_time' => Yii::$app->formatter->asDate($modelQueue['created_at'], 'php:Y-m-d H:i:s'),
                    'service_group_name' => $row['service_group_name'],
                    'counter_service_name' => $modelCounterService['counter_service_name'],
                    'counter_service_id' => $modelCounterService['counter_service_id'],
                ],
                'sources' => $sources,
                'call_groups' => ArrayHelper::getColumn($callers, 'queue_no')
            ];
        }
        return $response;
    }

    // ตรวจสอบสถานะโปรแกรมเสียงเรียก ว่าถูกเปิดใช้งานในวันปัจจุบันหรือยัง
    public function actionActivePlayStation($id)
    {
        $model = $this->findModelPlayStation($id);
        if($model['last_active_date'] == Yii::$app->formatter->asDate('now', 'php:Y-m-d')) {
            return [
                'status' => 'active'
            ];
        } else {
            $model->last_active_date = Yii::$app->formatter->asDate('now', 'php:Y-m-d');
            $model->save();
            return [
                'status' => 'deactive'
            ];
        }
    }

    // รายชื่อบริการ
    public function actionGetServices()
    {
        return TblService::find()->where('service_code <> :service_code', [':service_code' => ''])->orderBy('service_order asc')->all();
    }

    public function actionLedOptions()
    {
        return AppQuery::getLedOptions();
    }

    public function actionUpdateQueue()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $modelQueue = $this->findModelQueue($params['queue_id']);
        $modelService = $this->findModelService($params['service_id']);
        if($modelQueue['service_id'] != $params['service_id']) { // ถ้าไม่ใช่แผนกเดิม
            $modelQueue->service_id = $params['service_id'];
            $modelQueue->queue_no = $modelQueue->generateNumber();
            $params['queue_no'] = $modelQueue->queue_no;
        }
        $modelQueue->load($params, '');
        $modelQueue->service_group_id = $modelService['service_group_id'];
        if($modelQueue->validate() && $modelQueue->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $modelQueue
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($modelQueue->errors));
        }
    }
}