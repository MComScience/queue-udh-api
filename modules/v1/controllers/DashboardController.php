<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\filters\auth\CompositeAuth;
use app\filters\auth\HttpBearerAuth;
use yii\web\Response;
use yii\filters\AccessControl;
use app\helpers\Enum;
use app\modules\v1\models\TblQueue;
use app\modules\v1\models\TblDeptGroup;
use app\modules\v1\models\TblDept;
use app\modules\v1\models\TblKiosk;
use app\modules\v1\models\TblServiceGroup;
use app\modules\v1\models\TblService;
use app\components\ChartBuilder;
use yii\helpers\ArrayHelper;
use app\filters\AccessRule;
use app\modules\v1\models\User;

class DashboardController extends ActiveController
{
    public $modelClass = '';

    const CACHE_KEY = 'Q_No';

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
                'count-services' => ['GET'],
                'pie-chart' => ['GET'],
                'column-chart' => ['GET'],
                'floor-chart' => ['GET'],
                'column-dept-chart' => ['GET'],
                'kiosk-chart' => ['GET'],
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
            'options', 'count-services', 'pie-chart', 'column-chart', 'floor-chart',
            'column-dept-chart', 'kiosk-chart'
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
                    'actions' => ['index', 'view', 'create', 'update'],
                    'roles' => ['@'],
                ],
                [
                    'actions' => ['delete'],
                    'allow' => true,
                    'roles' => [User::ROLE_ADMIN]
                ]
            ],
        ];
        return $behaviors;
    }

    public function actionCountServices($startDate = '', $endDate = '')
    {
        // ถ้ามีการค้นหาจากวันที่
        if (!empty($startDate) && !empty($endDate)) {
            // วันที่เริ่ม
            $startDate = $startDate . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $endDate . ' 23:59:59';
        } else {
            $startDate = Enum::startDateNow(); // start date today
            $endDate = Enum::endDateNow(); // end date today
        }
        $itemServices = [];
        $countAll = TblQueue::find()->betweenCreateAt($startDate, $endDate)->count(); // จำนวนทั้งหมด
        $itemServices[] = [
            'service_group_name' => 'ทั้งหมด',
            'items' => [
                [
                    'service_name' => 'รวมจำนวนทั้งหมด',
                    'count' => $countAll
                ]
            ]
        ];
        $serviceGroups = TblServiceGroup::find()->orderByAsc()->all();
        // นับคิวแต่ละแผนก ** เฉพาะบริการที่ Active
        foreach ($serviceGroups as $serviceGroup) {
            $serviceItems = [];
            // บริการ
            $services = TblService::find()
                ->where(['service_group_id' => $serviceGroup['service_group_id']])
                ->isActive()
                ->orderByAsc()
                ->all();
            foreach ($services as $k => $service) {
                $count = TblQueue::find()
                    ->where(['service_id' => $service['service_id']])
                    ->betweenCreateAt($startDate, $endDate)
                    ->count();
                $serviceItems[] = [
                    'service_name' => $service['service_name'],
                    'count' => $count
                ];
            }
            $itemServices[] = [
                'service_group_name' => $serviceGroup['service_group_name'],
                'items' => $serviceItems
            ];
        }
        return $itemServices;
    }

    public function actionPieChart($startDate = '', $endDate = '')
    {
        if (!empty($startDate) && !empty($endDate)) {
            // วันที่เริ่ม
            $startDate = $startDate . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $endDate . ' 23:59:59';
        } else {
            $startDate = Enum::startDateNow(); // start date today
            $endDate = Enum::endDateNow(); // end date today
        }
        return ChartBuilder::getPieChart($startDate, $endDate);
    }

    public function actionColumnChart($startDate = '', $endDate = '')
    {
        if (!empty($startDate) && !empty($endDate)) {
            // วันที่เริ่ม
            $startDate = $startDate . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $endDate . ' 23:59:59';
        } else {
            $startDate = Enum::startDateNow(); // start date today
            $endDate = Enum::endDateNow(); // end date today
        }
        return ChartBuilder::getColumnChart($startDate, $endDate);
    }

    public function actionFloorChart($startDate = '', $endDate = '')
    {
        if (!empty($startDate) && !empty($endDate)) {
            // วันที่เริ่ม
            $startDate = $startDate . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $endDate . ' 23:59:59';
        } else {
            $startDate = Enum::startDateNow(); // start date today
            $endDate = Enum::endDateNow(); // end date today
        }
        return ChartBuilder::getChartFloor($startDate, $endDate);
    }

    public function actionColumnDeptChart($startDate = '', $endDate = '')
    {
        if (!empty($startDate) && !empty($endDate)) {
            // วันที่เริ่ม
            $startDate = $startDate . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $endDate . ' 23:59:59';
        } else {
            $startDate = Enum::startDateNow(); // start date today
            $endDate = Enum::endDateNow(); // end date today
        }
        return ChartBuilder::getColumnDeptChart($startDate, $endDate);
    }

    public function actionKioskChart($startDate = '', $endDate = '')
    {
        if (!empty($startDate) && !empty($endDate)) {
            // วันที่เริ่ม
            $startDate = $startDate . ' 00:00:00';
            // วันที่สิ้นสุด
            $endDate = $endDate . ' 23:59:59';
        } else {
            $startDate = Enum::startDateNow(); // start date today
            $endDate = Enum::endDateNow(); // end date today
        }
        return ChartBuilder::getKioskChart($startDate, $endDate);
    }
}