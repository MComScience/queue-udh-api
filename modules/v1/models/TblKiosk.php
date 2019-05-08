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
            [['kiosk_name', 'user_id', 'departments'], 'required'],
            [['user_id'], 'integer'],
            [['departments'], 'safe'],
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
            'departments' => 'แผนก/ชั้น',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getDeptGroupNames()
    {
        $li = [];
        if ($this->departments) {
            $dept = Json::decode($this->departments);
            $items = TblDeptGroup::find()->where(['dept_group_id' => $dept])->all();
            foreach ($items as $item) {
                $li[] = Html::tag('li', $item['dept_group_name']);
            }
        }
        return Html::tag('ol', implode("\n", $li));
    }
}
