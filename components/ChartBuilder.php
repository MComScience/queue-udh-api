<?php

namespace app\components;

use app\modules\v1\models\TblFloor;
use app\modules\v1\models\TblDeptGroup;
use app\modules\v1\models\TblQueue;
use app\modules\v1\models\TblDept;
use app\modules\v1\models\TblService;
use app\modules\v1\models\TblServiceGroup;
use app\modules\v1\models\TblKiosk;
use app\helpers\Enum;
use yii\helpers\ArrayHelper;

class ChartBuilder
{
    public static function getChartFloor($startDate, $endDate)
    {
        $floors = TblFloor::find()->all();
        $seriesFloor = [];
        $seriesDrilldownFloor = [];
        $seriesDrilldownDept = [];
        foreach ($floors as $key => $floor) {
            $serviceGroups = TblServiceGroup::find()->where(['floor_id' => $floor['floor_id']])->all();
            $serviceGroupIds = ArrayHelper::getColumn($serviceGroups, 'service_group_id');
            $countFloor = TblQueue::find()
                ->where(['service_group_id' => $serviceGroupIds])
                ->betweenCreateAt($startDate, $endDate)
                ->count();
            // ชั้น
            $seriesFloor[] = [
                'name' => $floor['floor_name'],
                'y' => (float)$countFloor,
                'drilldown' => $floor['floor_id']
            ];
            // นับคิวแต่ละแผนก ** เฉพาะแผนกที่ Active
            $dataDrilldownServiceGroup = [];
            foreach ($serviceGroups as $serviceGroup) {
                $countDeptGroup = TblQueue::find()
                    ->where(['service_group_id' => $serviceGroup['service_group_id']])
                    ->betweenCreateAt($startDate, $endDate)
                    ->count();
                $dataDrilldownServiceGroup[] = [
                    'name' => $serviceGroup['service_group_name'],
                    'y' => intval($countDeptGroup),
                    'drilldown' => $serviceGroup['service_group_name']
                ];

                // บริการ
                $services = TblService::find()->where(['service_group_id' => $serviceGroup['service_group_id']])->isActive()->orderByAsc()->all();
                $dataDrilldown = [];
                foreach ($services as $service) {
                    # code...
                    $count = TblQueue::find()
                        ->where(['service_id' => $service['service_id']])
                        ->betweenCreateAt($startDate, $endDate)
                        ->count();
                    $dataDrilldown[] = [
                        $service['service_name'],
                        (float)$count,
                    ];
                }
                $seriesDrilldownDept[] = [
                    'id' => $serviceGroup['service_group_name'],
                    'data' => $dataDrilldown
                ];
            }
            $seriesDrilldownFloor[] = [
                'name' => $floor['floor_name'],
                'id' => $floor['floor_id'],
                'data' => $dataDrilldownServiceGroup
            ];
        }
        return [
            'series_floor' => $seriesFloor,
            'series_drilldown_floor' => $seriesDrilldownFloor,
            'series_drilldown_dept' => $seriesDrilldownDept
        ];
    }

    public static function getPieChart($startDate, $endDate)
    {
        $seriesPie = [];
        $countAll = TblQueue::find()->betweenCreateAt($startDate, $endDate)->count(); // จำนวนทั้งหมด
        $services = TblService::find()->isActive()->orderByAsc()->all();
        foreach ($services as $k => $service) {
            $count = TblQueue::find()
                ->where(['service_id' => $service['service_id']])
                ->betweenCreateAt($startDate, $endDate)
                ->count();
            $y = $countAll > 0 ? (float)number_format(($count / $countAll) * 100, 2) : 0;
            $seriesPie[] = [
                'name' => $service['service_name'],
                'y' => $y,
            ];
        }
        return $seriesPie;
    }

    public static function getColumnChart($startDate, $endDate)
    {
        $seriesColumn = [];
        $seriesDrilldown = [];
        $serviceGroups = TblServiceGroup::find()->orderByAsc()->all();
        // นับคิวแต่ละบริการ ** เฉพาะบริการที่ Active
        foreach ($serviceGroups as $serviceGroup) {
            $countService = 0;
            $dataDrilldown = [];
            // บริการ
            $services = TblService::find()->where(['service_group_id' => $serviceGroup['service_group_id']])->isActive()->orderByAsc()->all();
            foreach ($services as $k => $service) {
                $count = TblQueue::find()
                    ->where(['service_id' => $service['service_id']])
                    ->betweenCreateAt($startDate, $endDate)
                    ->count();
                $countService = $countService + $count;
                $dataDrilldown[] = [
                    $service['service_name'],
                    intval($count)
                ];
            }
            $seriesColumn[] = [
                'name' => $serviceGroup['service_group_name'],
                'y' => $countService,
                'drilldown' => $serviceGroup['service_group_name']
            ];
            $seriesDrilldown[] = [
                'name' => $serviceGroup['service_group_name'],
                'id' => $serviceGroup['service_group_name'],
                'data' => $dataDrilldown
            ];
        }
        return [
            'seriesColumn' => $seriesColumn,
            'seriesDrilldown' => $seriesDrilldown
        ];
    }

    public static function getColumnDeptChart($startDate, $endDate)
    {
        $services = TblService::find()->isActive()->orderByAsc()->all();
        $series = [];
        $countAll = 0;
        // นับคิวแต่ละแผนก ** เฉพาะแผนกที่ Active
        foreach ($services as $index => $service) {
            $count = TblQueue::find()
                ->where(['service_id' => $service['service_id']])
                ->betweenCreateAt($startDate, $endDate)
                ->count();
            $series[] = [
                'name' => $service['service_name'],
                'y' => intval($count),
                'color' => '#7CB5EC'
            ];
            $countAll = $countAll + $count;
        }
        $all = [
            [
                'name' => 'คิวทั้งหมด',
                'y' => intval($countAll),
                'color' => '#e75b8d'
            ]
        ];
        return ArrayHelper::merge($all, $series);
    }

    public static function getKioskChart($startDate, $endDate)
    {
        $kiosks = TblKiosk::find()->all();
        $series = [];
        foreach ($kiosks as $key => $kiosk) {
            $count = TblQueue::find()
                ->where([
                    'created_by' => $kiosk['user_id'],
                ])
                ->betweenCreateAt($startDate, $endDate)
                ->count();
            $series[] = [
                'name' => $kiosk['kiosk_name'] . ' (' . $kiosk['kiosk_des'] . ')',
                'y' => intval($count),
                'drilldown' => $kiosk['kiosk_id']
            ];
        }
        return $series;
    }
}