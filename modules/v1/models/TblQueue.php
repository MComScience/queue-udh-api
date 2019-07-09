<?php

namespace app\modules\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use app\modules\v1\traits\ModelTrait;
use app\modules\v1\components\AutoNumber as BaseAutoNumber;
use app\modules\v1\behaviors\CoreMultiValueBehavior;
use app\helpers\Enum;

/**
 * This is the model class for table "tbl_queue".
 *
 * @property int $queue_id รหัสคิว
 * @property string $queue_no เลขคิว
 * @property int $patient_id ไอดีผู้ป่วย
 * @property int $service_group_id กลุ่มบริการ
 * @property int $service_id รหัสบริการ
 * @property int $priority_id ประเภทคิว
 * @property int $queue_station จุดออกบัตรคิว
 * @property int $case_patient กรณีผู้ป่วย
 * @property int $queue_status_id สถานะคิว
 * @property int $appoint คิวนัด
 * @property int $parent_id ออกคิวจาก
 * @property int $doctor_id แพทย์
 * @property string $created_at วันที่บันทึก
 * @property string $updated_at วันที่แก้ไข
 * @property int $created_by ผู้บันทึก
 * @property int $updated_by ผู้แก้ไข
 */
class TblQueue extends \yii\db\ActiveRecord
{
    use ModelTrait;

    const STATUS_WAIT = 1; // รอเรียก
    const STATUS_CALL = 2; // กำลังเรียก
    const STATUS_HOLD = 3; // พักคิว
    const STATUS_END = 4; // เสร็จสิ้น
    const STATUS_WAIT_EX = 5; // รอเรียกห้องตรวจ

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_queue';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => Yii::$app->formatter->asDate('now','php:Y-m-d H:i:s'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => CoreMultiValueBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'queue_no',
                ],
                'value' => function ($event) {
                    if (empty($this->queue_no)) {
                        return $this->generateNumber();
                    } else {
                        return $event->sender[$event->data];
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
            [['patient_id', 'service_group_id', 'service_id', 'priority_id', 'queue_station', 'case_patient', 'queue_status_id', 'appoint'], 'required'],
            [['patient_id', 'service_group_id', 'service_id', 'priority_id', 'queue_station', 'case_patient', 'queue_status_id', 'appoint', 'parent_id', 'doctor_id', 'issue_card_ex', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['queue_no'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'queue_id' => 'รหัสคิว',
            'queue_no' => 'เลขคิว',
            'patient_id' => 'ไอดีผู้ป่วย',
            'service_group_id' => 'กลุ่มบริการ',
            'service_id' => 'รหัสบริการ',
            'priority_id' => 'ประเภทคิว',
            'queue_station' => 'จุดออกบัตรคิว',
            'case_patient' => 'กรณีผู้ป่วย',
            'queue_status_id' => 'สถานะคิว',
            'appoint' => 'คิวนัด',
            'parent_id' => 'ออกคิวจาก', // เฉพาะคิวห้องตรวจ
            'doctor_id' => 'แพทย์',
            'issue_card_ex' => 'สถานะออกบัตรคิวห้องตรวจ',
            'created_at' => 'วันที่บันทึก',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้บันทึก',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    // สถานะคิว
    public function getStatus()
    {
        return $this->hasOne(TblQueueStatus::className(), ['queue_status_id' => 'queue_status_id']);
    }

    // ข้อมูลผู้ป่วย
    public function getPatient()
    {
        return $this->hasOne(TblPatient::className(), ['patient_id' => 'patient_id']);
    }

    // กลุ่มบริการ
    public function getServiceGroup()
    {
        return $this->hasOne(TblServiceGroup::className(), ['service_group_id' => 'service_group_id']);
    }

    // ชื่อบริการ
    public function getService()
    {
        return $this->hasOne(TblService::className(), ['service_id' => 'service_id']);
    }

    // ความสำคัญ
    public function getPriority()
    {
        return $this->hasOne(TblPriority::className(), ['priority_id' => 'priority_id']);
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by']);
    }

    // แพทย์
    public function getDoctor()
    {
        return $this->hasOne(TblDoctor::className(), ['doctor_id' => 'doctor_id']);
    }

    // คิวห้องตรวจหรือซักประวัติ
    // public function getRoomType()
    // {
    //     return $this->hasOne(TblRoomType::className(), ['room_type_id' => 'room_type_id']);
    // }

    // ประเภทคิว
    public function getQueueTypeName($type)
    {
        $types = $this->getQueueTypes();
        return ArrayHelper::getValue($types, $type, '');
    }

    // ประเภทคิว
    public function getQueueTypes()
    {
        return [
            1 => 'คิวซักประวัติ',
            2 => 'คิวห้องตรวจ'
        ];
    }

    // กรณีผู้ป่วย
    public function getCasePatientTypes()
    {
        return [
            1 => 'เดินได้',
            2 => 'รถนั่ง',
            3 => 'รถนอน',
            4 => 'อุ้ม(เด็ก)'
        ];
    }

    public function getCasePatientName()
    {
        $cases = $this->getCasePatientTypes();
        return ArrayHelper::getValue($cases, $this->case_patient, '');
    }

    public function generateNumber()
    {
        $service = $this->findModelService($this->service_id); // ชื่อบริการ
        $modelPrefix = $this->findModelPrefix($service['prefix_id']); // ข้อมูลตัวอักหน้าเลขคิว
        $isRunningContinue = $service->isRunningContinue;
        if($isRunningContinue){ // ถ้ากำหนดให้เรียงเลขคิวต่อแผนกอื่นที่มีเลขนำหน้าคิวเดียวกัน
            $modelAutoNumber = AutoNumber::findOne([
                'prefix_id' => $service['prefix_id'],
                'service_group_id' => $service['service_group_id'],
                'updated_at' => Yii::$app->formatter->asDate('now', 'php:Y-m-d'),
                'flag' => 1
            ]);
        } else {
            $modelAutoNumber = AutoNumber::findOne([
                'prefix_id' => $service['prefix_id'],
                'service_id' => $service['service_id'],
                'service_group_id' => $service['service_group_id'],
                'updated_at' => Yii::$app->formatter->asDate('now', 'php:Y-m-d'),
                'flag' => 0
            ]);
        }
        $prefix = $this->priority_id == 1 ? $modelPrefix['prefix_code'] : 'A';
        if(!$modelAutoNumber){ // no data
            $component = \Yii::createObject([
                'class' => BaseAutoNumber::className(),
                'prefix' => $prefix,
                'number' => 1,
                'digit' => (int)$service['service_num_digit'],
            ]);
            $number = $component->generate();

            $autonumber = new AutoNumber();
            if(!$isRunningContinue){
                $autonumber->service_id = $service['service_id'];
                $autonumber->service_group_id = $service['service_group_id'];
                $autonumber->flag = 0;
            } else {
                $autonumber->service_group_id = $service['service_group_id'];
                $autonumber->flag = 1;
            }
            $autonumber->number = $number;
            $autonumber->prefix_id = $service['prefix_id'];
            $autonumber->updated_at = Yii::$app->formatter->asDate('now', 'php:Y-m-d');
            $autonumber->save();
            return $number;
        } else {
            $component = \Yii::createObject([
                'class' => BaseAutoNumber::className(),
                'prefix' => $prefix,
                'number' => $modelAutoNumber['number'],
                'digit' => (int)$service['service_num_digit'],
            ]);
            $number = $component->generate();

            if(!$isRunningContinue){
                $modelAutoNumber->service_id = $service['service_id'];
                $modelAutoNumber->service_group_id = $service['service_group_id'];
                $modelAutoNumber->flag = 0;
            } else {
                $modelAutoNumber->service_group_id = $service['service_group_id'];
                $modelAutoNumber->flag = 1;
            }
            $modelAutoNumber->number = $number;
            $modelAutoNumber->save();
            return $number;
        }
        /* $startDate = Enum::startDateNow(); // start date today
        $endDate = Enum::endDateNow(); // end date today
        $maxId = $this->find()->where([
            'dept_id' => $this->dept_id,
            'dept_group_id' => $this->dept_group_id
        ])
            ->andWhere(['between', 'created_at', $startDate, $endDate])
            ->max('queue_id');
        $no = 1;
        if ($maxId) {
            $modelQueue = $this->findOne($maxId);
            $no = $modelQueue['queue_no'];
        } */
        /* $queue = ArrayHelper::map($this->find()->where([ 'dept_id' => $this->dept_id, 'dept_group_id' => $this->dept_group_id ])->all(),'queue_id','queue_no');
        $qnums = [];
        $maxqnum = null;
        $qid = null;
        if(count($queue) > 0){
            foreach($queue as $key => $q){
                $qnums[$key] = preg_replace("/[^0-9\.]/", '', $q);
            }
            $maxqnum = max($qnums);
            $qid = array_search($maxqnum, $qnums);
        }
        $component = \Yii::createObject([
            'class' => BaseAutoNumber::className(),
            'prefix' => ($department && $this->priority_id == 1) ? $department['dept_prefix'] : 'A',
            'number' => $no,
            'digit' => $department ? (int)$department['dept_num_digit'] : 3,
        ]);
        return $component->generate();*/
    }

    /**
     * {@inheritdoc}
     * @return TblQueueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblQueueQuery(get_called_class());
    }

    public function getAppointStatus()
    {
        return [
            0 => 'ไม่มีนัด',
            1 => 'มีนัด'
        ];
    }

    public function getAppointName()
    {
        $appoints = $this->getAppointStatus();
        return ArrayHelper::getValue($appoints, $this->appoint, '-');
    }

    public function getPrioritys()
    {
        return [
            1 => 'คิวทั่วไป',
            2 => 'คิวด่วน'
        ];
    }

    public function getPriorityName()
    {
        $prioritys = $this->getPrioritys();
        return ArrayHelper::getValue($prioritys, $this->priority_id, '-');
    }
}
