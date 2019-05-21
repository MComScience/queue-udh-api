<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tbl_dept".
 *
 * @property string $dept_id รหัสแผก
 * @property string $dept_name ชื่อแผนก
 * @property int $dept_group_id รหัสกลุ่ม/แผนก
 * @property string $dept_prefix ตัวอักษรนำหน้าเลขคิว
 * @property int $dept_num_digit จำนวนหลักเลขคิว
 * @property int $card_id แบบบัตรคิว
 * @property int $prefix_id ตัวอักษรนำหน้าเลขคิว
 * @property int $prefix_running เรียงเลขคิวต่อเนื่องแผนกอื่น ที่มีตัวอักษรนำหน้าเลขคิวเดียวกัน
 * @property int $print_copy_qty จำนวนพิมพ์/ครั้ง
 * @property int $dept_order ลำดับการแสดงผล
 * @property int $dept_status สถานะ
 */
class TblDept extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;

    public $order_dept;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_dept';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_id', 'dept_name', 'dept_group_id', 'dept_num_digit', 'prefix_id', 'prefix_running', 'dept_status'], 'required'],
            [['dept_group_id', 'dept_num_digit', 'card_id', 'prefix_id', 'prefix_running', 'print_copy_qty', 'dept_order', 'dept_status'], 'integer'],
            [['dept_id'], 'string', 'max' => 100],
            [['dept_name'], 'string', 'max' => 255],
            [['dept_prefix'], 'string', 'max' => 10],
            [['dept_id'], 'unique'],
            [['order_dept'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'dept_id' => 'รหัสแผก',
            'dept_name' => 'ชื่อแผนก',
            'dept_group_id' => 'กลุ่ม/แผนก',
            'dept_prefix' => 'ตัวอักษรนำหน้าเลขคิว',
            'dept_num_digit' => 'จำนวนหลักเลขคิว',
            'card_id' => 'แบบบัตรคิว',
            'prefix_id' => 'ตัวอักษรนำหน้าเลขคิว',
            'prefix_running' => 'เรียงเลขคิวต่อเนื่องแผนกอื่น ที่มีตัวอักษรนำหน้าเลขคิวเดียวกัน',
            'print_copy_qty' => 'จำนวนพิมพ์บัตรคิว/ครั้ง',
            'dept_order' => 'ลำดับการแสดงผล',
            'dept_status' => 'สถานะ',
        ];
    }

    // กลุ่มแผนก
    public function getDeptGroup()
    {
        return $this->hasOne(TblDeptGroup::className(), ['dept_group_id' => 'dept_group_id']);
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


    // สถานะการใช้งาน
    public function getStatusNameById($code)
    {
        $status = $this->getAllStatus();
        return ArrayHelper::getValue($status,$code, '');
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = $this->getAllStatus();
        return ArrayHelper::getValue($status,$this->dept_status, '');
    }

    // ตัวอักษรนำหน้าเลขคิว
    public function getPrefixById($id)
    {
        $model = $this->findOne($id);
        if($model) {
            return $model->dept_prefix;
        }
        return null;
    }

    // สถานะทั้งหมด
    public function getAllStatus()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }

    public function runningOptions()
    {
        return [
            0 => 'NO',
            1 => 'YES'
        ];
    }

    public function getIsRunningContinue()
    {
        return (int)$this->prefix_running === 1;
    }

    /**
     * {@inheritdoc}
     * @return TblDeptQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblDeptQuery(get_called_class());
    }
}
