<?php
namespace app\components;

use yii\db\Query;
use app\helpers\Enum;
use yii\helpers\ArrayHelper;
use app\modules\v1\models\TblPatient;
use app\modules\v1\models\TblQueue;

class AppQuery
{
    // คิวที่เคยลงทะเบียน
    public static function getQueueRegister($params)
    {
        $query = (new Query())
            ->select(['tbl_queue.*'])
            ->from('tbl_queue')
            ->where(['tbl_queue.dept_id' => $params['dept_id']])
            ->andWhere(['between', 'tbl_queue.created_at', $params['startDate'], $params['endDate']])
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id');
        if(!empty($params['cid'])){
            $query->andWhere(['tbl_patient.cid' => $params['cid']]);
        } else {
            $query->andWhere(['tbl_patient.hn' => $params['hn']]);
        }
        return $query->one();
    }

    // คิวที่เคยลงทะเบียน
    public static function getPatientRegister($patient)
    {
        $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $query = (new \yii\db\Query())
        ->select(['tbl_queue.*', 'tbl_dept.*'])
        ->from('tbl_queue')
        ->where(['patient_id' => ArrayHelper::getColumn($patient, 'patient_id')])
        ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
        ->innerJoin('tbl_dept', 'tbl_dept.dept_id = tbl_queue.dept_id')
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
                'DATE_FORMAT(tbl_queue.created_at, "%H:%i") as created_time',
                'tbl_patient.hn',
                'tbl_patient.fullname',
                'tbl_dept.dept_name',
                'file_storage_item.base_url',
                'file_storage_item.path'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_dept', 'tbl_dept.dept_id = tbl_queue.dept_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->andWhere(['between', 'tbl_queue.created_at', $params['startDate'], $params['endDate']])
            ->orderBy('tbl_queue.created_at ASC')
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
                'tbl_patient.cid',
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
                'tbl_queue.dept_id' => $params['dept_ids'],
                'tbl_queue.priority_id' => 1
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->orderBy('tbl_queue.queue_id ASC')
            ->groupBy('tbl_queue.queue_id')
            ->all();

        $query2 = (new Query())
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
                'file_storage_item.path'
            ])
            ->from('tbl_queue')
            ->innerJoin('tbl_patient', 'tbl_patient.patient_id = tbl_queue.patient_id')
            ->innerJoin('tbl_dept', 'tbl_dept.dept_id = tbl_queue.dept_id')
            ->leftJoin('file_storage_item', 'file_storage_item.ref_id = tbl_patient.patient_id')
            ->where([
                'tbl_queue.queue_status_id' => TblQueue::STATUS_WAIT,
                'tbl_queue.dept_id' => $params['dept_ids'],
                'tbl_queue.priority_id' => 2
            ])
            ->andWhere(['between', 'tbl_queue.created_at', $startDate, $endDate])
            ->orderBy('tbl_queue.queue_id ASC')
            ->groupBy('tbl_queue.queue_id')
            ->all();

        return ArrayHelper::merge($query2, $query1);
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
                'tbl_patient.cid',
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
}