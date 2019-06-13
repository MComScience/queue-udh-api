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
                        'save-service-order', 'kiosk-list', 'kiosk-options', 'create-kiosk', 'update-kiosk'
                    ],
                    'roles' => ['@'],
                ],
                [
                    'actions' => ['delete', 'delete-floor', 'delete-service-group', 'delete-service', 'delete-kiosk'],
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
        
        $floors = AppQuery::getFloorOptions();
        $floorOptions = $this->mapDataOptions($floors);

        $queueServices = AppQuery::getQueueServiceOptions();
        $queueServiceOptions = $this->mapDataOptions($queueServices);
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
        $serviceGroups = AppQuery::getServiceGroupOptions();
        $serviceGroupOptions = $this->mapDataOptions($serviceGroups);

        $prefixs = AppQuery::getPrefixOptions();
        $prefixOptions =  $this->mapDataOptions($prefixs);

        $cards = AppQuery::getCardOptions();
        $cardOptions = $this->mapDataOptions($cards);

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

        $userKioks = AppQuery::getUserKioskOptions();
        $userKioskOptions = $this->mapDataOptions($userKioks);

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

    private function mapDataOptions($options)
    {
        $result = [];
        foreach ($options as $key => $value) {
            $result[] = [
                'id' => $key,
                'name' => $value
            ];
        }
        return $result;
    }
}