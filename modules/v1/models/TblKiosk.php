<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_kiosk".
 *
 * @property int $kiosk_id
 * @property string $kiosk_name ชื่อ
 * @property string $kiosk_des รายละเอียด
 * @property int $user_id ผู้ใช้งาน
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
            [['kiosk_name', 'user_id'], 'required'],
            [['user_id'], 'integer'],
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
            'user_id' => 'ผู้ใช้งาน',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
