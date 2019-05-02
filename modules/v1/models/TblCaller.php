<?php

namespace app\modules\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "tbl_caller".
 *
 * @property int $caller_id รหัส
 * @property int $queue_id รหัสคิว
 * @property int $counter_id เคาท์เตอร์
 * @property int $counter_service_id ช่องบริการ
 * @property string $call_time เวลาเรียก
 * @property string $hold_time เวลาพักคิว
 * @property string $end_time เวลาเสร็จสิ้น
 * @property string $created_at วันที่บันทึก
 * @property string $updated_at วันที่แก้ไข
 * @property int $created_by ผู้บันทึก
 * @property int $updated_by ผู้แก้ไข
 * @property int $caller_status สถานะ
 */
class TblCaller extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_caller';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_id', 'counter_id', 'counter_service_id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'caller_status'], 'required'],
            [['queue_id', 'counter_id', 'counter_service_id', 'created_by', 'updated_by', 'caller_status'], 'integer'],
            [['call_time', 'hold_time', 'end_time', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'caller_id' => 'รหัส',
            'queue_id' => 'รหัสคิว',
            'counter_id' => 'เคาท์เตอร์',
            'counter_service_id' => 'ช่องบริการ',
            'call_time' => 'เวลาเรียก',
            'hold_time' => 'เวลาพักคิว',
            'end_time' => 'เวลาเสร็จสิ้น',
            'created_at' => 'วันที่บันทึก',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้บันทึก',
            'updated_by' => 'ผู้แก้ไข',
            'caller_status' => 'สถานะ',
        ];
    }

    // ข้อมูลคิว
    public function getQueue()
    {
        return $this->hasOne(TblQueue::className(), ['queue_id' => 'queue_id']);
    }

    // เคาท์เตอร์
    public function getCounter()
    {
        return $this->hasOne(TblCounter::className(), ['counter_id' => 'counter_id']);
    }

    // ช่องบริการ
    public function getCounterService()
    {
        return $this->hasOne(TblCounterService::className(), ['counter_service_id' => 'counter_service_id']);
    }

    // สถานะการเรียก
    public function getAllStatus()
    {
        return [
            1 => 'กำลังเรียก',
            2 => 'พักคิว',
            3 => 'เรียกเสร็จ',
            4 => 'เสร็จสิ้น'
        ];
    }
    
    // สถานะ
    public function getStatusNameById($status)
    {
        $statusList = $this->getAllStatus();
        return ArrayHelper::getValue($statusList,$status, '');
    }

    // สถานะ
    public function getStatusName()
    {
        $statusList = $this->getAllStatus();
        return ArrayHelper::getValue($statusList,$this->caller_status, '');
    }
}
