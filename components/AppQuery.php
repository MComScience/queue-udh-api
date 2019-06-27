<?php
namespace app\components;

use yii\db\Query;
use app\helpers\Enum;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\v1\models\TblPatient;
use app\modules\v1\models\TblQueue;
use app\modules\v1\models\TblPrefix;
use app\modules\v1\models\TblCard;
use app\modules\v1\models\TblFloor;
use app\modules\v1\models\TblQueueService;
use app\modules\v1\models\User;
use app\modules\v1\models\TblProfileService;
use app\modules\v1\models\TblCounter;
use app\modules\v1\models\TblService;

class AppQuery
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;

    // คิวที่เคยลงทะเบียน
    public static function getQueueRegister($params)
    {
        $patients = [];
        if(!empty($params['hn'])){
            $patients = TblPatient::find()
            ->where([
                'hn' => $params['hn']
            ])
            ->betweenCreateAt($params['startDate'], $params['endDate'])
            ->all();
        } elseif(!empty($params['cid'])) {
            $patients = TblPatient::find()
            ->where([
                'cid' => $params['cid']
            ])
            ->betweenCreateAt($params['startDate'], $params['endDate'])
            ->all();
        }
        
        $queue = null;
        foreach ($patients as $key => $patient) {
            /* $queue = TblQueue::find()
                ->where([
                    'patient_id' => $patient['patient_id'],
                    'service_id' => $params['service_id'],
                    'queue_service_id' => 1
                ])
                ->betweenCreateAt($params['startDate'], $params['endDate'])
                ->one(); */
            $queue = (new Query())
                ->select([
                    'tbl_queue.queue_id',
                    'tbl_queue.queue_no',
                    'tbl_queue.patient_id',
                    'tbl_patient.hn',
                    'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                    'tbl_patient.fullname',
                    'tbl_patient.maininscl_name',
                    'tbl_queue.service_group_id',
                    'tbl_queue.service_id',
                    'tbl_queue.priority_id',
                    'tbl_queue.queue_station',
                    'tbl_queue.case_patient',
                    'tbl_queue.queue_status_id',
                    'tbl_queue.appoint',
                    'tbl_queue.created_at',
                    '`profile`.`name`',
                    'file_storage_item.base_url',
                    'file_storage_item.path',
                    'tbl_service.service_code',
                    'tbl_service.service_name',
                    'tbl_service_group.service_group_name',
                    'tbl_queue_service.queue_service_name'
                ])
                ->from('tbl_queue')
                ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
                ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
                ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
                ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
                ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
                ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
                ->where([
                    'tbl_queue.queue_id' => $queue['queue_id']
                ])
                //->andWhere(['between', 'tbl_queue.created_at', $params['startDate'], $params['endDate']])
                //->groupBy('tbl_queue.queue_id')
                ->one();
            if($queue) {
                break;
            }
        }
        return $queue;
        /* $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_patient.maininscl_name',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.priority_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.queue_status_id',
                'tbl_queue.appoint',
                'tbl_queue.created_at',
                '`profile`.`name`',
                'file_storage_item.base_url',
                'file_storage_item.path',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_queue_service.queue_service_name'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_queue.service_id' => $params['service_id'],
                'tbl_service_group.queue_service_id' => 1
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $params['startDate'], $params['endDate']])
            ->groupBy('tbl_queue.queue_id');
        if(!empty($params['cid'])){
            $query->andWhere(['tbl_patient.cid' => $params['cid']]);
        } else {
            $query->andWhere(['tbl_patient.hn' => $params['hn']]);
        }
        return $query->one(); */
    }

    // คิวที่เคยลงทะเบียน
    public static function getPatientRegister($patient)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new \yii\db\Query())
        ->select(['tbl_queue.*', 'tbl_service.*'])
        ->from('tbl_queue')
        ->where(['tbl_patient.patient_id' => ArrayHelper::getColumn($patient, 'patient_id')])
        ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
        ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
        ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
        ->groupBy('tbl_queue.queue_id')
        ->all();
        return $query;
    }

    // ค้นหาข้อมูลผู้ป่วยทั้งหมดจาก hn
    public static function getPatientByHn($hn)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $patient = TblPatient::find()
                ->where(['hn' => $hn])
                ->andWhere(['between', 'created_at', $startDate, $endDate])
                ->all();
        return $patient;
    }

    // ค้นหาข้อมูลผู้ป่วยทั้งหมดจาก cid
    public static function getPatientByCid($cid)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $patient = TblPatient::find()
                ->where(['cid' => $cid])
                ->andWhere(['between', 'created_at', $startDate, $endDate])
                ->all();
        return $patient;
    }

    // รายการคิวทั้งหมด
    public static function getAllQueue($params)
    {
        $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.queue_status_id',
                'tbl_queue.created_at as created_time',
                'tbl_patient.hn',
                'tbl_patient.fullname',
                'tbl_service.service_name',
                'file_storage_item.base_url',
                'file_storage_item.path',
                '`profile`.`name`'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->andWhere(['between', 'tbl_queue.created_at', $params['startDate'], $params['endDate']])
            ->groupBy('tbl_queue.queue_id')
            ->all();
        return $query;
    }

    // คิวรอเรียก
    public static function getDataWaiting($params)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query1 = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at as print_time',
                'tbl_queue.created_at',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.appoint',
                'file_storage_item.base_url',
                'file_storage_item.path',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_queue_service.queue_service_name',
                '`profile`.`name`'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_WAIT,
                'tbl_queue.service_id' => $params['serviceIds'],
                'tbl_service_group.queue_service_id' => 1 // ประเภทคิวซักประวัติ
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->orderBy('tbl_queue.queue_id ASC')
            ->groupBy('tbl_queue.queue_id')
            ->all();

        /* $query2 = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'tbl_patient.cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.dept_id',
                'tbl_dept.dept_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at',
                'file_storage_item.base_url',
                'file_storage_item.path',
                '`profile`.name'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_dept', 'tbl_dept.dept_id = tbl_queue.dept_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_WAIT,
                'tbl_queue.dept_id' => $params['dept_ids'],
                'tbl_queue.priority_id' => 2
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->orderBy('tbl_queue.queue_id ASC')
            ->groupBy('tbl_queue.queue_id')
            ->all(); */

        // return ArrayHelper::merge($query2, $query1);
        return $query1;
    }

    // คิวรอเรียก
    public static function getDataWaitByHn($params)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.dept_id',
                'tbl_dept.dept_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at',
                'file_storage_item.base_url',
                'file_storage_item.path'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_dept', 'tbl_dept.dept_id = tbl_queue.dept_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_WAIT,
                'tbl_patient.hn' => $params['hn'],
                'tbl_queue.dept_id' => $params['dept_ids'],
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->groupBy('tbl_queue.queue_id')
            ->all();

        return $query;
    }

    // คิวกำลังเรียก
    public static function getDataCaller($params)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.appoint',
                'file_storage_item.base_url',
                'file_storage_item.path',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_queue_service.queue_service_name',
                'tbl_caller.caller_id',
                'tbl_caller.call_time',
                'tbl_caller.hold_time',
                'tbl_caller.end_time',
                'tbl_caller.caller_status',
                'tbl_counter_service.counter_service_name',
                'tbl_caller.counter_service_id',
                'tbl_caller.counter_id',
                '`profile`.name'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('tbl_caller', 'tbl_caller.queue_id = tbl_queue.queue_id')
            ->innerJoin('tbl_counter_service', 'tbl_counter_service.counter_service_id = tbl_caller.counter_service_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_CALL, // สถานะเรียก
                'tbl_queue.service_id' => $params['serviceIds'], // ชื่อบริการ
                'tbl_caller.counter_service_id' => $params['counter_service_id'], // ช่องบริการ
                'tbl_service_group.queue_service_id' => 1 // ประเภทคิวซักประวัติ
            ])
            ->andWhere(['between', 'tbl_caller.created_at', $startDate, $endDate])
            ->groupBy('tbl_caller.caller_id')
            ->orderBy('tbl_caller.call_time ASC')
            ->all();

        return $query;
    }

    // คิวพัก
    public static function getDataHold($params)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.appoint',
                'file_storage_item.base_url',
                'file_storage_item.path',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_queue_service.queue_service_name',
                'tbl_caller.caller_id',
                'tbl_caller.call_time',
                'tbl_caller.hold_time',
                'tbl_caller.end_time',
                'tbl_caller.caller_status',
                'tbl_counter_service.counter_service_name',
                'tbl_caller.counter_service_id',
                'tbl_caller.counter_id',
                '`profile`.name'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('tbl_caller', 'tbl_caller.queue_id = tbl_queue.queue_id')
            ->innerJoin('tbl_counter_service', 'tbl_counter_service.counter_service_id = tbl_caller.counter_service_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_HOLD, // สถานะพักคิว
                'tbl_queue.service_id' => $params['serviceIds'], // ชื่อบริการ
                'tbl_caller.counter_service_id' => $params['counter_service_id'], // ช่องบริการ
                'tbl_service_group.queue_service_id' => 1 // ประเภทคิวซักประวัติ
            ])
            ->andWhere(['between', 'tbl_caller.created_at', $startDate, $endDate])
            ->groupBy('tbl_caller.caller_id')
            ->orderBy('tbl_caller.call_time ASC')
            ->all();

        return $query;
    }

    // คิวรอเรียกห้องตรวจ
    public static function getDataWaitingExamination($params)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at as print_time',
                'tbl_queue.created_at',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.appoint',
                'file_storage_item.base_url',
                'file_storage_item.path',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_queue_service.queue_service_name',
                '`profile`.`name`'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_WAIT,
                'tbl_queue.service_id' => $params['serviceIds'],
                'tbl_service_group.queue_service_id' => 2 // ประเภทคิวห้องตรวจ
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->orderBy('tbl_queue.queue_id ASC')
            ->groupBy('tbl_queue.queue_id')
            ->all();
        return $query;
    }

    // คิวกำลังเรียกห้องตรวจ
    public static function getDataCallerExamination($params)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.appoint',
                'file_storage_item.base_url',
                'file_storage_item.path',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_queue_service.queue_service_name',
                'tbl_caller.caller_id',
                'tbl_caller.call_time',
                'tbl_caller.hold_time',
                'tbl_caller.end_time',
                'tbl_caller.caller_status',
                'tbl_counter_service.counter_service_name',
                'tbl_caller.counter_service_id',
                'tbl_caller.counter_id',
                '`profile`.`name`'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('tbl_caller', 'tbl_caller.queue_id = tbl_queue.queue_id')
            ->innerJoin('tbl_counter_service', 'tbl_counter_service.counter_service_id = tbl_caller.counter_service_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_CALL,
                'tbl_queue.service_id' => $params['serviceIds'],
                'tbl_caller.counter_service_id' => $params['counterServiceIds'],
                'tbl_service_group.queue_service_id' => 2 // ประเภทคิวห้องตรวจ
            ])
            ->andWhere(['between', 'tbl_caller.created_at', $startDate, $endDate])
            ->orderBy('tbl_caller.call_time ASC')
            ->groupBy('tbl_caller.caller_id')
            ->all();
        return $query;
    }

    // คิวพักห้องตรวจ
    public static function getDataHoldExamination($params)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new Query())
            ->select([
                'tbl_queue.queue_id',
                'tbl_queue.queue_no',
                'tbl_queue.patient_id',
                'tbl_patient.hn',
                'CASE WHEN tbl_patient.cid IS NULL OR tbl_patient.cid = \'\' THEN NULL ELSE CONCAT(SUBSTRING(tbl_patient.cid, 1, 9), \'****\') END AS cid',
                'tbl_patient.fullname',
                'tbl_queue.queue_status_id',
                'tbl_patient.maininscl_name',
                'tbl_queue.priority_id',
                'tbl_queue.created_at',
                'tbl_queue.service_group_id',
                'tbl_queue.service_id',
                'tbl_queue.queue_station',
                'tbl_queue.case_patient',
                'tbl_queue.appoint',
                'file_storage_item.base_url',
                'file_storage_item.path',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service_group.service_group_name',
                'tbl_queue_service.queue_service_name',
                'tbl_caller.caller_id',
                'tbl_caller.call_time',
                'tbl_caller.hold_time',
                'tbl_caller.end_time',
                'tbl_caller.caller_status',
                'tbl_counter_service.counter_service_name',
                'tbl_caller.counter_service_id',
                'tbl_caller.counter_id',
                '`profile`.`name`'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_service', 'tbl_service.service_id = tbl_queue.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->innerJoin('tbl_caller', 'tbl_caller.queue_id = tbl_queue.queue_id')
            ->innerJoin('tbl_counter_service', 'tbl_counter_service.counter_service_id = tbl_caller.counter_service_id')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_queue.created_by')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_HOLD,
                'tbl_queue.service_id' => $params['serviceIds'],
                'tbl_caller.counter_service_id' => $params['counterServiceIds'],
                'tbl_service_group.queue_service_id' => 2 // ประเภทคิวห้องตรวจ
            ])
            ->andWhere(['between', 'tbl_caller.created_at', $startDate, $endDate])
            ->orderBy('tbl_caller.hold_time ASC')
            ->groupBy('tbl_caller.caller_id')
            ->all();
        return $query;
    }

    // รายการกลุ่มบริการ
    public static function getServiceGroupList()
    {
        $rows = (new \yii\db\Query())
            ->select([
                'tbl_service_group.service_group_id',
                'tbl_service_group.service_group_name',
                'tbl_service_group.service_group_order',
                'tbl_service_group.floor_id',
                'tbl_service_group.queue_service_id',
                'tbl_queue_service.queue_service_name',
                'tbl_floor.floor_name'
            ])
            ->from('tbl_service_group')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->innerJoin('tbl_floor', 'tbl_floor.floor_id = tbl_service_group.floor_id')
            ->orderBy('tbl_service_group.service_group_order ASC')
            ->all();
        return $rows;
    }

    // รายการบริการ
    public static function getServiceList()
    {
        $rows = (new \yii\db\Query())
            ->select([
                'tbl_service.service_id',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service.service_group_id',
                'tbl_service.service_prefix',
                'tbl_service.service_num_digit',
                'tbl_service.card_id',
                'tbl_service.prefix_id',
                'tbl_service.prefix_running',
                'tbl_service.print_copy_qty',
                'tbl_service.service_order',
                'tbl_service.service_status',
                'tbl_service_group.service_group_name',
                'tbl_card.card_name',
                'tbl_prefix.prefix_code',
                'tbl_floor.*'
            ])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_floor', 'tbl_floor.floor_id = tbl_service_group.floor_id')
            ->innerJoin('tbl_card', 'tbl_card.card_id = tbl_service.card_id')
            ->innerJoin('tbl_prefix', 'tbl_prefix.prefix_id = tbl_service.prefix_id')
            ->orderBy('tbl_service.service_order ASC')
            ->all();
        return $rows;
    }

    // รายการตู้ Kiosk
    public static function getKioskList()
    {
        $rows = (new \yii\db\Query())
            ->select([
                'tbl_kiosk.kiosk_id',
                'tbl_kiosk.kiosk_name',
                'tbl_kiosk.kiosk_des',
                'tbl_kiosk.user_id',
                'tbl_kiosk.service_groups',
                'tbl_kiosk.kiosk_status',
                '`profile`.`name`'
            ])
            ->from('tbl_kiosk')
            ->innerJoin('`profile`', '`profile`.user_id = tbl_kiosk.user_id')
            ->all();
        return $rows;
    }

    // รายการโปรไฟล์
    public static function getProfileList()
    {
        $rows = TblProfileService::find()->all();
        $response = [];
        foreach ($rows as $key => $row) {
            $counter = TblCounter::findOne($row['counter_id']);
            $queueService = TblQueueService::findOne(['queue_service_id' => $row['queue_service_id']]);
            $services = TblService::find()->where(['service_id' => Json::decode($row['service_id'])])->all();
            $examinations = (new \yii\db\Query())
                ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
                ->from('tbl_service')
                ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
                ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
                ->where([
                    'tbl_service.service_status' => 1,
                    'tbl_service_group.queue_service_id' => 2,
                    'tbl_service.service_id' => Json::decode($row['examination_id'])
                ])
                ->all();
            $response[] = [
                'profile_service_id' => $row['profile_service_id'],
                'profile_service_name' => $row['profile_service_name'],
                'counter_id' => $row['counter_id'],
                'service_id' => $row['service_id'],
                'examination_id' => $row['examination_id'],
                'queue_service_id' => $row['queue_service_id'],
                'profile_service_status' => $row['profile_service_status'],
                'counter' => $counter,
                'queue_service' => $queueService,
                'services' => $services,
                'examinations' => $examinations,
                'queue_service_ids' => Json::decode($row['queue_service_id']),
                'service_ids' => Json::decode($row['service_id']),
                'examination_ids' => Json::decode($row['examination_id'])
            ];
        }
        return $response;
    }

    // เลขรัน คิว
    public static function getAutonumberList()
    {
        $rows = (new \yii\db\Query())
            ->select([
                'auto_number.id',
                'auto_number.prefix_id',
                'auto_number.service_group_id',
                'auto_number.service_id',
                'auto_number.dept_code',
                'auto_number.number',
                'auto_number.flag',
                'auto_number.updated_at',
                'tbl_service.service_code',
                'tbl_service_group.service_group_name',
                'tbl_prefix.prefix_code',
                'tbl_service.service_name'
            ])
            ->from('auto_number')
            ->leftJoin('tbl_service', 'tbl_service.service_id = auto_number.service_id')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = auto_number.service_group_id')
            ->innerJoin('tbl_prefix', 'tbl_prefix.prefix_id = auto_number.prefix_id')
            ->all();
        return $rows;
    }

    // ตัวเลือกกลุ่มบริการ
    public static function getServiceGroupOptions()
    {
        return ArrayHelper::map((new \yii\db\Query())
        ->select([
            'tbl_service_group.service_group_id', 
            'CONCAT(\'(\',IFNULL(tbl_floor.floor_name,\'\'),\') \', tbl_service_group.service_group_name) as service_group_name'
        ])
        ->from('tbl_service_group')
        ->innerJoin('tbl_floor', 'tbl_floor.floor_id = tbl_service_group.floor_id')
        ->all(), 'service_group_id', 'service_group_name');
    }

    // ตัวอักษรนำหน้าเลขคิว
    public static function getPrefixOptions()
    {
        return ArrayHelper::map(TblPrefix::find()->asArray()->all(), 'prefix_id', 'prefix_code');
    }

    // ตัวเลือกแบบบัตรคิว
    public static function getCardOptions()
    {
        return ArrayHelper::map(TblCard::find()->asArray()->all(), 'card_id', 'card_name');
    }

    // ตัวเลือกชั้น
    public static function getFloorOptions()
    {
        return ArrayHelper::map(TblFloor::find()->asArray()->all(), 'floor_id', 'floor_name');
    }

    // ตัวเลือกประเภทคิวบริการ
    public static function getQueueServiceOptions()
    {
        return ArrayHelper::map(TblQueueService::find()->asArray()->all(), 'queue_service_id', 'queue_service_name');
    }

    // สถานะการใช้งาน
    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }

    // เคาน์เตอร์
    public static function getCounterOptions()
    {
        return ArrayHelper::map(TblCounter::find()->asArray()->all(), 'counter_id', 'counter_name');
    }

    //ชื่อบริการ
    public static function getServiceOptionsByQueueService($queue_service_id)
    {
        return ArrayHelper::map((new \yii\db\Query())
            ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_service.service_status' => 1,
                'tbl_service_group.queue_service_id' => $queue_service_id
            ])
            ->all(), 'service_id', 'service_name');
    }

    // รายการห้องตรวจ
    public static function getExaminationOprions()
    {
        return ArrayHelper::map((new \yii\db\Query())
            ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_service.service_status' => 1,
                'tbl_service_group.queue_service_id' => 2
            ])
            ->all(), 'service_id', 'service_name');
    }

    // user kiosk
    public static function getUserKioskOptions()
    {
        return ArrayHelper::map(User::find()->where(['role' => 30])->all(), 'id', 'username');
    }

    // เสียงเรียกบริการ
    public static function getCounterServiceSoundOptions()
    {
        return ArrayHelper::map(
            (new \yii\db\Query())
                ->select(['CONCAT(tbl_sound.sound_name,\' \',\'(\',tbl_sound.sound_th,\')\') AS sound_name', 'tbl_sound.sound_id'])
                ->from('tbl_sound')
                ->where('sound_name LIKE :query')
                ->addParams([':query'=>'%Service%'])
                ->all(),'sound_id','sound_name');
    }

    // เสียงเรียกหมายเลข
    public static function getCounterServiceNoSoundOptions()
    {
        return ArrayHelper::map(
            (new \yii\db\Query())
            ->select(['CONCAT(tbl_sound.sound_name,\' \',\'(\',tbl_sound.sound_th,\')\') AS sound_name', 'tbl_sound.sound_id'])
            ->from('tbl_sound')
            ->where('sound_name NOT LIKE :query')
            ->addParams([':query'=>'%Service%'])
            ->all(),'sound_id','sound_name');
    }

    public static function getCounterServiceOptions()
    {
        return ArrayHelper::map((new \yii\db\Query())
            ->select(['tbl_counter_service.counter_service_id', 'CONCAT(\'(\',tbl_counter.counter_name, \') \', tbl_counter_service.counter_service_name) AS counter_service_name'])
            ->from('tbl_counter_service')
            ->innerJoin('tbl_counter', 'tbl_counter.counter_id = tbl_counter_service.counter_id')
            ->where([
                'tbl_counter_service.counter_service_status' => 1
            ])
            ->all(), 'counter_service_id', 'counter_service_name');
    }

    //
    public static function getCallerByGroupkey($group_key)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $callers = (new \yii\db\Query())
            ->select([
                'tbl_caller.*',
                'tbl_queue.queue_no'
            ])
            ->from('tbl_caller')
            ->innerJoin('tbl_queue', 'tbl_queue.queue_id = tbl_caller.queue_id')
            ->where([
                'tbl_caller.group_key' => $group_key,
                'tbl_queue.queue_status_id' => 2
            ])
            ->andWhere(['between', 'tbl_caller.created_at', $startDate, $endDate])
            ->all();
        return $callers;
    }

    // ตัวเลือกการตั้งค่า LED
    public static function getLedOptions()
    {
        // ชื่อบริการ
        $services = (new \yii\db\Query())
            ->select([
                'tbl_service.service_id',
                'tbl_service.service_code',
                'tbl_service.service_name',
                'tbl_service.service_group_id',
                'tbl_service.service_prefix',
                'tbl_service.service_num_digit',
                'tbl_service.card_id',
                'tbl_service.prefix_id',
                'tbl_service.prefix_running',
                'tbl_service.print_copy_qty',
                'tbl_service.service_order',
                'tbl_service.service_status',
                'tbl_service_group.service_group_name',
                'tbl_service_group.service_group_order',
                'tbl_service_group.floor_id',
                'tbl_service_group.queue_service_id',
                'tbl_queue_service.queue_service_name'
            ])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->all();
        // เคาน์เตอร์
        $counters = (new \yii\db\Query())
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
            ->all();
        return [
            'services' => $services,
            'counters' => $counters
        ];
    }
}