<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Html;
/**
 * This is the model class for table "tbl_profile_service".
 *
 * @property int $profile_service_id รหัส
 * @property string $profile_service_name ชื่อโปรไฟล์
 * @property int $counter_id เคาท์เตอร์
 * @property string $dept_id แผนก
 * @property int $profile_service_status สถานะ
 */
class TblProfileService extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_profile_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['profile_service_name', 'counter_id', 'dept_id', 'profile_service_status'], 'required'],
            [['counter_id', 'profile_service_status'], 'integer'],
            [['dept_id'], 'safe'],
            [['profile_service_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'profile_service_id' => 'รหัส',
            'profile_service_name' => 'ชื่อโปรไฟล์',
            'counter_id' => 'เคาน์เตอร์',
            'dept_id' => 'แผนก',
            'profile_service_status' => 'สถานะ',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblProfileServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblProfileServiceQuery(get_called_class());
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = $this->getStatusLits();
        return ArrayHelper::getValue($status,$this->profile_service_status, '');
    }

    // สถานะทั้งหมด
    public function getStatusLits()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }

    public function getCounter()
    {
        return $this->hasOne(TblCounter::className(), ['counter_id' => 'counter_id']);
    }

    public function getDeptList()
    {
        if($this->dept_id){
            $depts = TblDept::find()->where(['dept_id' => Json::decode($this->dept_id)])->all();
            $li = [];
            foreach ($depts as $key => $dept) {
                $li[] = Html::tag('li', $dept['dept_name']);
            }
            return Html::tag('ol', implode("\n", $li));
        }
    }
}
