<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\filters\auth\CompositeAuth;
use app\filters\auth\HttpBearerAuth;
use yii\web\Response;
use app\filters\AccessRule;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\helpers\ArrayHelper;
// models
use app\modules\v1\models\TblFloor;
use app\modules\v1\traits\ModelTrait;
use app\modules\v1\models\User;
use app\components\AppQuery;
use app\modules\v1\models\TblQueueService;
use app\modules\v1\models\TblServiceGroup;
use app\modules\v1\models\TblService;
use app\modules\v1\models\TblKiosk;
use app\modules\v1\models\TblCard;
use app\modules\v1\models\TblProfileService;
use app\modules\v1\models\TblCounter;
use app\modules\v1\models\TblCounterService;
use app\modules\v1\models\TblSound;
use app\modules\v1\models\TblPlayStation;
use app\modules\v1\models\TblDisplay;

class SettingsController extends ActiveController
{
    use ModelTrait;

    public $modelClass = '';

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
                'floor-list' => ['GET'],
                'create-floor' => ['PUT', 'POST'],
                'update-floor' => ['POST','GET'],
                'delete-floor' => ['DELETE'],
                'service-group-list' => ['GET'],
                'service-group-options' => ['GET'],
                'create-service-group' => ['POST'],
                'update-service-group' => ['POST'],
                'delete-service-group' => ['DELETE'],
                'save-service-group-order' => ['POST'],
                'service-list' => ['GET'],
                'service-options' => ['GET'],
                'create-service' => ['POST'],
                'update-service' => ['POST'],
                'delete-service' => ['DELETE'],
                'save-service-order' => ['POST'],
                'kiosk-list' => ['GET'],
                'kiosk-options' => ['GET'],
                'create-kiosk' => ['POST'],
                'update-kiosk' => ['POST'],
                'delete-kiosk' => ['DELETE'],
                'card-list' => ['GET'],
                'create-card' => ['POST'],
                'update-card' => ['POST', 'GET'],
                'delete-card' => ['DELETE'],
                'profile-service-list' => ['GET'],
                'profile-service-options' => ['GET'],
                'create-profile-service' => ['PUT', 'POST'],
                'update-profile-service' => ['GET', 'POST'],
                'delete-profile-service' => ['DELETE'],
                'counter-list' => ['GET'],
                'create-counter' => ['PUT', 'POST'],
                'update-counter' => ['POST'],
                'delete-counter' => ['DELETE'],
                'counter-service-list' => ['GET'],
                'create-counter-service' => ['PUT', 'POST'],
                'update-counter-service' => ['POST'],
                'delete-counter-service' => ['DELETE'],
                'counter-service-options' => ['GET'],
                'play-station-list' => ['GET'],
                'create-play-station' => ['PUT', 'POST'],
                'update-play-station' => ['POST'],
                'delete-play-station' => ['DELETE'],
                'play-station-options' => ['GET'],
                'display-list' => ['GET'],
                'display-options' => ['GET'],
                'create-display' => ['PUT', 'POST'],
                'update-display' => ['GET', 'POST'],
                'delete-display' => ['DELETE'],
                'auto-number-list' => ['GET'],
                'update-auto-number' => ['POST'],
                'counter-service-doctor-options' => ['GET'],
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
            'options'
        ];
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'ruleConfig' => [
                'class' => AccessRule::className()
            ],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index', 'view', 'create', 'update', 'floor-list', 'create-floor', 'update-floor',
                        'service-group-list', 'service-group-options', 'create-service-group', 'update-service-group',
                        'save-service-group-order', 'service-list', 'service-options', 'create-service', 'update-service',
                        'save-service-order', 'kiosk-list', 'kiosk-options', 'create-kiosk', 'update-kiosk', 'card-list',
                        'create-card', 'update-card', 'profile-service-list', 'profile-service-options', 'create-profile-service',
                        'update-profile-service', 'counter-list', 'create-counter', 'update-counter', 'counter-service-list',
                        'create-counter-service', 'update-counter-service', 'counter-service-options', 'play-station-list',
                        'create-play-station', 'update-play-station', 'play-station-options', 'display-list', 'display-options',
                        'create-display', 'update-display', 'auto-number-list', 'update-auto-number', 'counter-service-doctor-options'
                    ],
                    'roles' => ['@'],
                ],
                [
                    'actions' => [
                        'delete', 'delete-floor', 'delete-service-group', 'delete-service', 'delete-kiosk',
                        'delete-card', 'delete-profile-service', 'delete-counter', 'delete-counter-service',
                        'delete-play-station', 'delete-display'
                    ],
                    'allow' => true,
                    'roles' => [User::ROLE_ADMIN]
                ]
            ],
        ];
        return $behaviors;
    }

    // รายการชั้น
    public function actionFloorList()
    {
        return TblFloor::find()->all();
    }

    // สร้างรายการ ชั้น
    public function actionCreateFloor()
    {
        $model = new TblFloor();
        if($model->load(\Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            return [
                'message' => 'บันทึกรายการสำเร็จ',
                'floor' => $model
            ];
        } else {
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // อัพเดทรายการ ชั้น
    public function actionUpdateFloor($id)
    {
        $model = $this->findModelFloor($id);
        if(Yii::$app->request->isGet) {
            return [
                'floor' => $model
            ];
        } elseif($model->load(\Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
            return [
                'message' => 'แก้ไขรายการสำเร็จ',
                'floor' => $model
            ];
        } else {
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ ชั้น
    public function actionDeleteFloor($id)
    {
        $this->findModelFloor($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // กลุ่มบริการ
    public function actionServiceGroupList()
    {
        return AppQuery::getServiceGroupList();
    }

    // ตัวเลือกการสร้างรายการ กลุ่มบริการ
    public function actionServiceGroupOptions()
    {
        
        $floorOptions = AppQuery::getFloorOptions();
        // $floorOptions = $this->mapDataOptions($floors);

        $queueServiceOptions = AppQuery::getQueueServiceOptions();
        // $queueServiceOptions = $this->mapDataOptions($queueServices);
        return [
            'floorOptions' => $floorOptions,
            'queueServiceOptions' => $queueServiceOptions
        ];
    }

    // สร้างรายการ กลุ่มบริการ
    public function  actionCreateServiceGroup()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblServiceGroup();
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไข กลุ่มบริการ
    public function  actionUpdateServiceGroup()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelServiceGroup($params['service_group_id']);
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ กลุ่มบริการ
    public function actionDeleteServiceGroup($id)
    {
        $this->findModelServiceGroup($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // จัดเรียงกลุ่มบริการ
    public function actionSaveServiceGroupOrder()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        foreach($params['groups'] as $index => $group) {
            $modelServiceGroup = $this->findModelServiceGroup($group['id']);
            $modelServiceGroup->service_group_order = $index+1;
            if(!$modelServiceGroup->save()) {
                throw new HttpException(422, Json::encode($modelServiceGroup->errors));
            }
        }
        return [
            'message' => 'บันทึกสำเร็จ!'
        ];
    }

    // รายการ ชื่อบริการ
    public function actionServiceList()
    {
        return AppQuery::getServiceList();
    }

    // ตัวเลือก หน้าบันทึกชื่อบริการ
    public function actionServiceOptions()
    {
        $serviceGroupOptions = AppQuery::getServiceGroupOptions();
        // $serviceGroupOptions = $this->mapDataOptions($serviceGroups);

        $prefixOptions = AppQuery::getPrefixOptions();
        // $prefixOptions =  $this->mapDataOptions($prefixs);

        $cardOptions = AppQuery::getCardOptions();
        // $cardOptions = $this->mapDataOptions($cards);

        $statusOptions = AppQuery::getStatusOptions();
        return [
            'serviceGroupOptions' => $serviceGroupOptions,
            'prefixOptions' => $prefixOptions,
            'cardOptions' => $cardOptions,
            'statusOptions' => $statusOptions,
        ];
    }

    // สร้างรายการ บริการ
    public function actionCreateService()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblService();
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไข บริการ
    public function  actionUpdateService()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelService($params['service_id']);
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ บริการ
    public function actionDeleteService($id)
    {
        $this->findModelService($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // จัดเรียงบริการ
    public function actionSaveServiceOrder()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        foreach($params['services'] as $index => $service) {
            $modelService = $this->findModelService($service['id']);
            $modelService->service_order = $index+1;
            if(!$modelService->save()) {
                throw new HttpException(422, Json::encode($modelService->errors));
            }
        }
        return [
            'message' => 'บันทึกสำเร็จ!'
        ];
    }

    // รายการ ตู้ Kiosk
    public function actionKioskList()
    {
        $response = [];
        $rows = AppQuery::getKioskList();
        foreach ($rows as $key => $row) {
            $groupIds = Json::decode($row['service_groups']);
            $groups = TblServiceGroup::find()->where(['service_group_id' => $groupIds])->all();
            $response[] = [
                'kiosk_id' => $row['kiosk_id'],
                'kiosk_name' => $row['kiosk_name'],
                'kiosk_des' => $row['kiosk_des'],
                'kiosk_status' => $row['kiosk_status'],
                'user_id' => $row['user_id'],
                'name' => $row['name'],
                'groupIds' => $groupIds,
                'groups' => $groups,
            ];
        }
        return $response;
    }

    // ตัวเลือก หน้าบันทึก kiosk
    public function actionKioskOptions()
    {
        $serviceGroups = AppQuery::getServiceGroupOptions();
        $serviceGroupOptions = $this->mapDataOptions($serviceGroups);

        $userKioskOptions = AppQuery::getUserKioskOptions();
        // $userKioskOptions = $this->mapDataOptions($userKioks);

        return [
            'serviceGroupOptions' => $serviceGroupOptions,
            'userKioskOptions' => $userKioskOptions
        ];
    }

    // สร้างรายการ ตู้กดบัตรคิว
    public function actionCreateKiosk()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblKiosk();
        $model->load($params, '');
        $model->service_groups = !empty($params['service_groups']) ? Json::encode(explode(',',$params['service_groups'])) : '';
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไข ตู้กดบัตรคิว
    public function  actionUpdateKiosk()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelKiosk($params['kiosk_id']);
        $model->load($params, '');
        $model->service_groups = !empty($params['service_groups']) ? Json::encode(explode(',',$params['service_groups'])) : '';
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ ตู้กดบัตรคิว
    public function actionDeleteKiosk($id)
    {
        $this->findModelKiosk($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // รายการ บัตรคิว
    public function actionCardList()
    {
        return TblCard::find()->all();
    }

    // สร้างรายการ บัตรคิว
    public function actionCreateCard()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblCard();
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไขรายกากร บัตรคิว
    public function actionUpdateCard($id)
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelCard($id);
        if(Yii::$app->request->isGet) {
            return [
                'model' => $model
            ];
        } else {
            $model->load($params, '');
            if($model->validate() && $model->save()) {
                return [
                    'message' => 'บันทึกสำเร็จ!',
                    'model' => $model
                ];
            } else {
                // Validation error
                throw new HttpException(422, Json::encode($model->errors));
            }
        }
    }

    // ลบรายการ บัตรคิว
    public function actionDeleteCard($id)
    {
        $this->findModelCard($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // รายการโปรไฟล์
    public function actionProfileServiceList()
    {
        return AppQuery::getProfileList();
    }

    // ตัวเลือก ฟอร์มสร้างโปรไฟล์
    public function actionProfileServiceOptions()
    {
        $counters = AppQuery::getCounterOptions();
        $queue_service_options = AppQuery::getQueueServiceOptions();
        $services = (new \yii\db\Query())
            ->select([
                'tbl_service.service_id', 
                'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name',
                'tbl_service_group.queue_service_id',
                'tbl_service.service_code'
            ])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_service.service_status' => 1
            ])
            ->all();
        $examinations = AppQuery::getExaminationOprions();
        return [
            'counters' => $counters,
            'queue_service_options' => $queue_service_options,
            'services' => $services,
            'examinations' => $examinations,
        ];
    }

    // สร้างรายการ โปรไฟล์
    public function actionCreateProfileService()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblProfileService();
        $model->load($params, '');
        $model->service_id = !empty($params['service_id']) ? $params['service_id'] : '';
        $model->examination_id = !empty($params['examination_id']) ? $params['examination_id'] : '';
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไขรายการ โปรไฟล์
    public function actionUpdateProfileService()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelProfileService($params['profile_service_id']);
        $model->load($params, '');
        $model->service_id = !empty($params['service_id']) ? $params['service_id'] : '';
        $model->examination_id = !empty($params['examination_id']) ? $params['examination_id'] : '';
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ โปรไฟล์
    public function actionDeleteProfileService($id)
    {
        $this->findModelProfileService($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // รายการ เคาน์เตอร์
    public function actionCounterList()
    {
        return TblCounter::find()->all();
    }

    // สร้างรายการ เคาน์เตอร์
    public function actionCreateCounter()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblCounter();
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไขรายการ เคาน์เตอร์
    public function actionUpdateCounter()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelCounter($params['counter_id']);
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ เคาน์เตอร์
    public function actionDeleteCounter($id)
    {
        $this->findModelCounter($id)->delete();
        TblCounterService::deleteAll(['counter_id' => $id]);
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // รายการ จุดบริการ/ช่องบริการ
    public function actionCounterServiceList()
    {
        $response = [];
        $counter_services = TblCounterService::find()->all();
        foreach ($counter_services as $key => $counter_service) {
            $counter = $this->findModelCounter($counter_service['counter_id']);
            $service_sound = $this->findModelSound($counter_service['counter_service_sound']);
            $service_no_sound = $this->findModelSound($counter_service['counter_service_no_sound']);
            $response[] = [
                'counter_service_id' => $counter_service['counter_service_id'],
                'counter_service_name' => $counter_service['counter_service_name'],
                'counter_service_no' => $counter_service['counter_service_no'],
                'counter_service_sound' => $counter_service['counter_service_sound'],
                'counter_service_no_sound' => $counter_service['counter_service_no_sound'],
                'counter_id' => $counter_service['counter_id'],
                'doctor_id' => (string)$counter_service['doctor_id'],
                'counter_service_status' => $counter_service['counter_service_status'],
                'counter_name' => $counter['counter_name'],
                'service_sound_name' => $service_sound['sound_th'],
                'service_no_sound_name' => $service_no_sound['sound_th'],
                'doctor_name' => empty($counter_service['doctor_id']) ? '-' : $counter_service->doctor->fullname
            ];
        }
        return $response;
    }

    // options
    public function actionCounterServiceOptions()
    {
        $counters = AppQuery::getCounterOptions();
        $sound_options = AppQuery::getCounterServiceSoundOptions();
        $sound_no_options = AppQuery::getCounterServiceNoSoundOptions();
        $doctors = AppQuery::getDoctorOptions();
        return [
            'counter_options' => $counters,
            'sound_options' => $sound_options,
            'sound_no_options' => $sound_no_options,
            'doctors' => $doctors
        ];
    }

    // สร้างรายการ จุดบริการ/ช่องบริการ
    public function actionCreateCounterService()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblCounterService();
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไขรายการ จุดบริการ/ช่องบริการ
    public function actionUpdateCounterService()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelCounterService($params['counter_service_id']);
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ เคาน์เตอร์
    public function actionDeleteCounterService($id)
    {
        $this->findModelCounterService($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // รายการ โปรแกรมเสียง
    public function actionPlayStationList()
    {
        $response = [];
        $play_stations = TblPlayStation::find()->all();
        foreach ($play_stations as $key => $play_station) {
            $counters = TblCounter::find()->where(['counter_id' => unserialize($play_station['counter_id']) ])->all();
            $counter_services = (new \yii\db\Query())
                ->select([
                    'tbl_counter_service.counter_service_id',
                    'tbl_counter_service.counter_service_name',
                    'tbl_counter_service.counter_service_no',
                    'tbl_counter_service.counter_service_sound',
                    'tbl_counter_service.counter_service_no_sound',
                    'tbl_counter_service.counter_id',
                    'tbl_counter_service.counter_service_status',
                    'tbl_counter.counter_name'
                ])
                ->from('tbl_counter_service')
                ->innerJoin('tbl_counter', 'tbl_counter.counter_id = tbl_counter_service.counter_id')
                ->where(['counter_service_id' => unserialize($play_station['counter_service_id'])])
                ->all();
            $response[] = [
                'play_station_id' => $play_station['play_station_id'],
                'play_station_name' => $play_station['play_station_name'],
                'counter_id' => $play_station['counter_id'],
                'counter_service_id' => $play_station['counter_service_id'],
                'last_active_date' => $play_station['last_active_date'],
                'play_station_status' => $play_station['play_station_status'],
                'counter_ids' => unserialize($play_station['counter_id']),
                'counter_service_ids' => unserialize($play_station['counter_service_id']),
                'counters' => $counters,
                'counter_services' => $counter_services
            ];
        }
        return $response;
    }

    // สร้างรายการ โปรแกรมเสียง
    public function actionCreatePlayStation()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblPlayStation();
        $model->load($params, '');
        $counter_id = Json::decode($params['counter_id']);
        $counter_service_id = Json::decode($params['counter_service_id']);
        $model->counter_id = \serialize($counter_id);
        $model->counter_service_id = \serialize($counter_service_id);
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไขรายการ โปรแกรมเสียง
    public function actionUpdatePlayStation()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelPlayStation($params['play_station_id']);
        $model->load($params, '');
        $counter_id = Json::decode($params['counter_id']);
        $counter_service_id = Json::decode($params['counter_service_id']);
        $model->counter_id = \serialize($counter_id);
        $model->counter_service_id = \serialize($counter_service_id);
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // ลบรายการ โปรแกรมเสียง
    public function actionDeletePlayStation($id)
    {
        $this->findModelPlayStation($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // ตัวเลือก โปรแกรมเสียง
    public function actionPlayStationOptions()
    {
        $counters = AppQuery::getCounterOptions();
        $counter_services = (new \yii\db\Query())
            ->select([
                'tbl_counter_service.counter_service_id', 
                'CONCAT(\'(\',tbl_counter.counter_name, \') \', tbl_counter_service.counter_service_name) AS counter_service_name',
                'tbl_counter_service.counter_id'
            ])
            ->from('tbl_counter_service')
            ->innerJoin('tbl_counter', 'tbl_counter.counter_id = tbl_counter_service.counter_id')
            ->where([
                'tbl_counter_service.counter_service_status' => 1
            ])
            ->all();
        return [
            'counters' => $counters,
            'counter_services' => $counter_services
        ];
    }

    // รายการ จอแสดงผล
    public function actionDisplayList()
    {
        $displays = TblDisplay::find()->all();
        $response = [];
        foreach ($displays as $key => $display) {
            $counterIds = unserialize($display['counter_id']);
            $serviceIds = unserialize($display['service_id']);
            $counters = TblCounter::find()->where(['counter_id' => $counterIds])->all();
            $services = (new \yii\db\Query())
                ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
                ->from('tbl_service')
                ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
                ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
                ->where([
                    'tbl_service.service_status' => 1,
                    'tbl_service.service_id' => $serviceIds
                ])
                ->all();
            $response[] = [
                'display_id' => $display['display_id'],
                'display_name' => $display['display_name'],
                'display_css' => $display['display_css'],
                'page_length' => $display['page_length'],
                'display_status' => $display['display_status'],
                'counter_id' => $display['counter_id'],
                'service_id' => $display['service_id'],
                'counterIds' => $counterIds,
                'serviceIds' => $serviceIds,
                'counters' => $counters,
                'services' => $services,
            ];
        }
        return $response;
    }

    // display form options
    public function actionDisplayOptions()
    {
        $counters = AppQuery::getCounterOptions();
        $services = ArrayHelper::map((new \yii\db\Query())
            ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_service.service_status' => 1
            ])
            ->all(), 'service_id', 'service_name');
        return [
            'counters' => $this->mapDataOptions($counters),
            'services' => $this->mapDataOptions($services),
        ];
    }

    // สร้างรายการ จอแสดงผล
    public function actionCreateDisplay()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = new TblDisplay();
        $model->load($params, '');
        $counter_id = explode(',',$params['counter_id']);
        $service_id = explode(',',$params['service_id']);
        $model->counter_id = \serialize($counter_id);
        $model->service_id = \serialize($service_id);
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    // แก้ไขรายการ จอแสดงผล
    public function actionUpdateDisplay($id)
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelDisplay($id);
        if(Yii::$app->request->isGet) {
            $model->counter_id = \unserialize($model['counter_id']);
            $model->service_id = \unserialize($model['service_id']);
            return [
                'model' => $model
            ];
        } else {
            $model->load($params, '');
            $counter_id = explode(',',$params['counter_id']);
            $service_id = explode(',',$params['service_id']);
            $model->counter_id = \serialize($counter_id);
            $model->service_id = \serialize($service_id);
            if($model->validate() && $model->save()) {
                return [
                    'message' => 'บันทึกสำเร็จ!',
                    'model' => $model
                ];
            } else {
                // Validation error
                throw new HttpException(422, Json::encode($model->errors));
            }
        }
    }

    // ลบรายการ จอแสดงผล
    public function actionDeleteDisplay($id)
    {
        $this->findModelDisplay($id)->delete();
        return [
            'message' => 'ลบรายการสำเร็จ'
        ];
    }

    // รายการเลขรัน คิว
    public function actionAutoNumberList()
    {
        return AppQuery::getAutonumberList();
    }

    // แก้ไขรายการ จุดบริการ/ช่องบริการ
    public function actionUpdateAutoNumber()
    {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $model = $this->findModelAutoNumber($params['id']);
        $model->load($params, '');
        if($model->validate() && $model->save()) {
            return [
                'message' => 'บันทึกสำเร็จ!',
                'model' => $model
            ];
        } else {
            // Validation error
            throw new HttpException(422, Json::encode($model->errors));
        }
    }

    public function actionCounterServiceDoctorOptions()
    {
        $rows = (new \yii\db\Query())
            ->select([
                'tbl_counter_service.counter_service_id',
                'tbl_counter_service.counter_service_name',
                'tbl_counter.counter_name',
                'tbl_doctor.doctor_name',
                'tbl_counter_service.doctor_id',
                'tbl_doctor.doctor_title'
            ])
            ->from('tbl_counter_service')
            ->innerJoin('tbl_counter', 'tbl_counter.counter_id = tbl_counter_service.counter_id')
            ->innerJoin('tbl_doctor', 'tbl_doctor.doctor_id = tbl_counter_service.doctor_id')
            ->all();
        return $rows;
    }

    private function mapDataOptions($options)
    {
        $result = [];
        foreach ($options as $key => $value) {
            $result[] = [
                'id' => (int)$key,
                'name' => $value
            ];
        }
        return $result;
    }
}