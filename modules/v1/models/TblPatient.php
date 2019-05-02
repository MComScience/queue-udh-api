<?php

namespace app\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use app\modules\v1\behaviors\CoreMultiValueBehavior;
use yii\helpers\Json;
/**
 * This is the model class for table "tbl_patient".
 *
 * @property int $patient_id ไอดีผู้ป่วย
 * @property string $hn หมายเลขผู้ป่วย
 * @property int $cid เลขบัตร ปชช
 * @property string $title คำนำหน้า
 * @property string $firstname ชื่อ
 * @property string $lastname นามสกุล
 * @property string $fullname ชื่อ-นามสกุล
 * @property string $birth_date วันเกิด
 * @property int $age อายุ
 * @property string $blood_group กรุ๊ปเลือด
 * @property string $nation สัญชาติ
 * @property string $address ที่อยู่
 * @property string $occ อาชีพ
 * @property string $appoint นัดหมาย
 */
class TblPatient extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_patient';
    }

    public function behaviors()
    {
        return [
            [
                'class' => CoreMultiValueBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'appoint',
                ],
                'value' => function ($event) {
                    if(empty($this->appoint)){
                        return '';
                    } else {
                        return Json::encode($event->sender[$event->data]);
                    }
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hn', 'cid', 'firstname', 'lastname'], 'required'],
            [['age'], 'integer'],
            [['cid'], 'string', 'max' => 13],
            [['birth_date'], 'safe'],
            [['appoint'], 'string'],
            [['hn', 'title', 'nation'], 'string', 'max' => 50],
            [['firstname', 'lastname', 'fullname', 'address'], 'string', 'max' => 255],
            [['blood_group'], 'string', 'max' => 10],
            [['occ'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'patient_id' => 'ไอดีผู้ป่วย',
            'hn' => 'หมายเลขผู้ป่วย',
            'cid' => 'เลขบัตร ปชช',
            'title' => 'คำนำหน้า',
            'firstname' => 'ชื่อ',
            'lastname' => 'นามสกุล',
            'fullname' => 'ชื่อ-นามสกุล',
            'birth_date' => 'วันเกิด',
            'age' => 'อายุ',
            'blood_group' => 'กรุ๊ปเลือด',
            'nation' => 'สัญชาติ',
            'address' => 'ที่อยู่',
            'occ' => 'อาชีพ',
            'appoint' => 'นัดหมาย',
        ];
    }

    public function getQueues()
    {
        return $this->hasMany(TblQueue::className(), ['patient_id' => 'patient_id']);
    }
}
