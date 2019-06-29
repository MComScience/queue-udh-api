<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_doctor".
 *
 * @property int $doctor_id
 * @property string $doctor_title คำนำหน้า
 * @property string $doctor_name ชื่อแพทย์
 */
class TblDoctor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_doctor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['doctor_name'], 'required'],
            [['doctor_title'], 'string', 'max' => 100],
            [['doctor_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'doctor_id' => 'Doctor ID',
            'doctor_title' => 'คำนำหน้า',
            'doctor_name' => 'ชื่อแพทย์',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblDoctorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblDoctorQuery(get_called_class());
    }

    public function getFullname()
    {
        return empty($this->doctor_title) ?  $this->doctor_name : $this->doctor_title.' '.$this->doctor_name;
    }
}
