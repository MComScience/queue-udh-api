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
 * @property int $dept_group_id กลุ่มแผนก
 * @property string $dept_id รหัสแผนก
 * @property int $priority_id ประเภทคิว
 * @property int $queue_station จุดออกบัตรคิว
 * @property int $case_patient มาโดย
 * @property int $queue_status_id สถานะคิว
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
            [['patient_id', 'dept_group_id', 'dept_id', 'priority_id', 'queue_station', 'case_patient', 'queue_status_id'], 'required'],
            [['patient_id', 'dept_group_id', 'priority_id', 'queue_station', 'case_patient', 'queue_status_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['queue_no', 'dept_id'], 'string', 'max' => 100],
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
            'dept_group_id' => 'กลุ่มแผนก',
            'dept_id' => 'รหัสแผนก',
            'priority_id' => 'ประเภทคิว',
            'queue_station' => 'จุดออกบัตรคิว',
            'case_patient' => 'มาโดย',
            'queue_status_id' => 'สถานะคิว',
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

    // กลุ่มแผนก
    public function getDeptGroup()
    {
        return $this->hasOne(TblDeptGroup::className(), ['dept_group_id' => 'dept_group_id']);
    }

    // แผนก
    public function getDept()
    {
        return $this->hasOne(TblDept::className(), ['dept_id' => 'dept_id']);
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
            3 => 'รถนอน'
        ];
    }

    public function getCasePatientName()
    {
        $cases = $this->getCasePatientTypes();
        return ArrayHelper::getValue($cases, $this->case_patient, '');
    }

    private function generateNumber()
    {
        $department = $this->findModelDept($this->dept_id); // แผนก
        $modelPrefix = $this->findModelPrefix($department['prefix_id']); // ข้อมูลตัวอักหน้าเลขคิว
        $isRunningContinue = $department->isRunningContinue;
        if($isRunningContinue){ // ถ้ากำหนดให้เรียงเลขคิวต่อแผนกอื่นที่มีเลขนำหน้าคิวเดียวกัน
            $modelAutoNumber = AutoNumber::findOne([
                'prefix_id' => $department['prefix_id'],
                'updated_at' => Yii::$app->formatter->asDate('now', 'php:Y-m-d'),
                'flag' => 1
            ]);
        } else {
            $modelAutoNumber = AutoNumber::findOne([
                'prefix_id' => $department['prefix_id'],
                'dept_code' => $department['dept_id'],
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
                'digit' => (int)$department['dept_num_digit'],
            ]);
            $number = $component->generate();

            $autonumber = new AutoNumber();
            if(!$isRunningContinue){
                $autonumber->dept_code = $department['dept_id'];
                $autonumber->flag = 0;
            } else {
                $autonumber->flag = 1;
            }
            $autonumber->number = $number;
            $autonumber->prefix_id = $department['prefix_id'];
            $autonumber->updated_at = Yii::$app->formatter->asDate('now', 'php:Y-m-d');
            $autonumber->save();
            return $number;
        } else {
            $component = \Yii::createObject([
                'class' => BaseAutoNumber::className(),
                'prefix' => $prefix,
                'number' => $modelAutoNumber['number'],
                'digit' => (int)$department['dept_num_digit'],
            ]);
            $number = $component->generate();

            if(!$isRunningContinue){
                $modelAutoNumber->dept_code = $department['dept_id'];
                $modelAutoNumber->flag = 0;
            } else {
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
}
