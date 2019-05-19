<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_dept_group".
 *
 * @property int $dept_group_id รหัสกลุ่ม/แผนก
 * @property string $dept_group_name ชื่อกลุ่ม/แผนก
 */
class TblDeptGroup extends \yii\db\ActiveRecord
{
    public $order_group;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_dept_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_group_name'], 'required'],
            [['dept_group_order'], 'integer'],
            [['order_group'], 'safe'],
            [['dept_group_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'dept_group_id' => 'รหัสกลุ่ม/แผนก',
            'dept_group_name' => 'ชื่อกลุ่ม/แผนก',
            'dept_group_order' => 'ลำดับการแสดงผล',
        ];
    }

    // แผนก
    public function getDepts()
    {
        return $this->hasMany(TblDept::className(), ['dept_group_id' => 'dept_group_id']);
    }
}
