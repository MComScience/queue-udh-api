<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_service".
 *
 * @property int $service_id รหัสบริการ
 * @property int $service_code รหัสแผก
 * @property string $service_name ชื่อบริการ
 * @property int $service_group_id กลุ่มบริการ
 * @property string $service_prefix ตัวอักษรนำหน้าเลขคิว
 * @property int $service_num_digit จำนวนหลักเลขคิว
 * @property int $card_id แบบบัตรคิว
 * @property int $prefix_id ตัวอักษรนำหน้าเลขคิว
 * @property int $prefix_running เรียงเลขคิวต่อเนื่องแผนกอื่น ที่มีตัวอักษรนำหน้าเลขคิวเดียวกัน
 * @property int $print_copy_qty จำนวนพิมพ์/ครั้ง
 * @property int $service_order ลำดับการแสดงผล
 * @property int $service_status สถานะ
 */
class TblService extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;

    public $order_service;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_group_id', 'service_num_digit', 'card_id', 'prefix_id', 'prefix_running', 'print_copy_qty', 'service_order', 'service_status'], 'integer'],
            [['service_name', 'service_group_id', 'service_num_digit', 'prefix_id', 'prefix_running', 'service_status'], 'required'],
            [['service_code'], 'string', 'max' => 50],
            [['service_name'], 'string', 'max' => 255],
            [['service_prefix'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'service_id' => 'รหัสบริการ',
            'service_code' => 'รหัสแผนก',
            'service_name' => 'ชื่อบริการ',
            'service_group_id' => 'กลุ่มบริการ',
            'service_prefix' => 'ตัวอักษรนำหน้าเลขคิว',
            'service_num_digit' => 'จำนวนหลักเลขคิว',
            'card_id' => 'แบบบัตรคิว',
            'prefix_id' => 'ตัวอักษรนำหน้าเลขคิว',
            'prefix_running' => 'เรียงเลขคิวต่อเนื่องแผนกอื่น ที่มีตัวอักษรนำหน้าเลขคิวเดียวกัน',
            'print_copy_qty' => 'จำนวนพิมพ์/ครั้ง',
            'service_order' => 'ลำดับการแสดงผล',
            'service_status' => 'สถานะ',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblServiceQuery(get_called_class());
    }

    public function runningOptions()
    {
        return [
            0 => 'NO',
            1 => 'YES'
        ];
    }

    // สถานะทั้งหมด
    public function getAllStatus()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = $this->getAllStatus();
        return ArrayHelper::getValue($status, $this->service_status, '');
    }

    // กลุ่มบริการ
    public function getServiceGroup()
    {
        return $this->hasOne(TblServiceGroup::className(), ['service_group_id' => 'service_group_id']);
    }

    // บัตรคิว
    public function getCard()
    {
        return $this->hasOne(TblCard::className(), ['card_id' => 'card_id']);
    }

    // ตัวอักษรหน้าเลขคิว
    public function getPrefix()
    {
        return $this->hasOne(TblPrefix::className(), ['prefix_id' => 'prefix_id']);
    }

    public function getIsRunningContinue()
    {
        return (int)$this->prefix_running === 1;
    }
}
