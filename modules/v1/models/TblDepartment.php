<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_department".
 *
 * @property string $department_code รหัสแผนก
 * @property string $department_desc ชื่อแผนก
 */
class TblDepartment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_department';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['department_code', 'department_desc'], 'required'],
            [['department_code'], 'string', 'max' => 50],
            [['department_desc'], 'string', 'max' => 255],
            [['department_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'department_code' => 'รหัสแผนก',
            'department_desc' => 'ชื่อแผนก',
        ];
    }
}
