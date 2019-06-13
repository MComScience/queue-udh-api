<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * This is the model class for table "tbl_kiosk".
 *
 * @property int $kiosk_id
 * @property string $kiosk_name ชื่อ
 * @property string $kiosk_des รายละเอียด
 * @property int $user_id ผู้ใช้งาน
 * @property string $service_groups กลุ่มบริการ
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
            [['kiosk_name', 'user_id', 'service_groups'], 'required'],
            [['user_id', 'kiosk_status'], 'integer'],
            [['service_groups'], 'string'],
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
            'service_groups' => 'กลุ่มบริการ',
            'kiosk_status' => 'สถานะ'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getDeptGroupNames()
    {
        $li = [];
        if ($this->service_groups) {
            $serviceGroups = Json::decode($this->service_groups);
            $items = TblServiceGroup::find()->where(['service_group_id' => $serviceGroups])->all();
            foreach ($items as $item) {
                $li[] = Html::tag('li', $item['service_group_name']);
            }
        }
        return Html::tag('ol', implode("\n", $li));
    }
}
