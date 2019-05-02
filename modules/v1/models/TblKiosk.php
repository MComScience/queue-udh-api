<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_kiosk".
 *
 * @property int $kiosk_id
 * @property string $kiosk_name ชื่อ
 * @property string $kiosk_des รายละเอียด
 */
class TblKiosk extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_kiosk';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kiosk_name'], 'required'],
            [['kiosk_name', 'kiosk_des'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'kiosk_id' => 'Kiosk ID',
            'kiosk_name' => 'ชื่อ',
            'kiosk_des' => 'รายละเอียด',
        ];
    }
}
