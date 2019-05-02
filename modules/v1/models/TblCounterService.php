<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "tbl_counter_service".
 *
 * @property int $counter_service_id รหัสช่องบริการ
 * @property string $counter_service_name ชื่อช่องบริการ
 * @property int $counter_service_no หมายเลขช่อง
 * @property int $counter_service_sound เสียงเรียกบริการ
 * @property int $counter_service_no_sound เสียงเรียกหมายเลข
 * @property int $counter_id เคาท์เตอร์
 * @property int $counter_service_status สถานะ
 */
class TblCounterService extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_counter_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['counter_service_name', 'counter_service_no', 'counter_service_sound', 'counter_service_no_sound', 'counter_id', 'counter_service_status'], 'required'],
            [['counter_service_no', 'counter_service_sound', 'counter_service_no_sound', 'counter_id', 'counter_service_status'], 'integer'],
            [['counter_service_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'counter_service_id' => 'รหัสช่องบริการ',
            'counter_service_name' => 'ชื่อช่องบริการ',
            'counter_service_no' => 'หมายเลขช่อง',
            'counter_service_sound' => 'เสียงเรียกบริการ',
            'counter_service_no_sound' => 'เสียงเรียกหมายเลข',
            'counter_id' => 'เคาท์เตอร์',
            'counter_service_status' => 'สถานะ',
        ];
    }

    // เคาท์เตอร์
    public function getCounter()
    {
        return $this->hasOne(TblCounter::className(), ['counter_id' => 'counter_id']);
    }

    // เสียงเรียกบริการ
    public function getServiceSound()
    {
        return $this->hasOne(TblSound::className(), ['counter_service_sound' => 'sound_id']);
    }

    // เสียงเรียกหมายเลข
    public function getServiceNoSound()
    {
        return $this->hasOne(TblSound::className(), ['counter_service_no_sound' => 'sound_id']);
    }

    // สถานะการใช้งาน
    public function getStatusNameById($code)
    {
        $status = $this->getStatusLits();
        return ArrayHelper::getValue($status,$code, '');
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = $this->getStatusLits();
        return ArrayHelper::getValue($status,$this->counter_service_status, '');
    }

    // สถานะทั้งหมด
    public function getStatusLits()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }
}
