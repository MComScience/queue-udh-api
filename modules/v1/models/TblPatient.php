<?php

namespace app\modules\v1\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\modules\v1\behaviors\CoreMultiValueBehavior;
use yii\db\Expression;
use yii\helpers\Json;
/**
 * This is the model class for table "tbl_patient".
 *
 * @property int $patient_id ไอดีผู้ป่วย
 * @property string $hn หมายเลขผู้ป่วย
 * @property string $vn ครั้งที่มา
 * @property string $cid เลขบัตร ปชช
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
 * @property string $maininscl_name สิทธิ
 * @property string $subinscl_name สิทธิ
 * @property string $created_at วันที่บันทึก
 * @property string $updated_at วันที่แก้ไข
 * @property int $created_by ผู้บันทึก
 * @property int $updated_by ผู้แก้ไข
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
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
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
            [['hn', 'firstname', 'lastname'], 'required'],
            [['birth_date'], 'safe'],
            [['age'], 'integer'],
            [['appoint'], 'string'],
            [['hn', 'vn', 'title', 'nation'], 'string', 'max' => 50],
            [['cid'], 'string', 'max' => 13],
            [['firstname', 'lastname', 'fullname', 'address', 'maininscl_name', 'subinscl_name'], 'string', 'max' => 255],
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
            'vn' => 'ครั้งที่มา',
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
            'maininscl_name' => 'สิทธิ',
            'subinscl_name' => 'สิทธิ',
        ];
    }

    public function getQueues()
    {
        return $this->hasMany(TblQueue::className(), ['patient_id' => 'patient_id']);
    }

    public static function find()
    {
        return new TblPatientQuery(get_called_class());
    }
}
