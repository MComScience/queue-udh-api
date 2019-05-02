<?php

namespace app\modules\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use app\modules\v1\traits\ModelTrait;
use app\modules\v1\components\AutoNumber;
use app\modules\v1\behaviors\CoreMultiValueBehavior;
/**
 * This is the model class for table "tbl_queue".
 *
 * @property int $queue_id รหัสคิว
 * @property string $queue_no เลขคิว
 * @property int $patient_id ไอดีผู้ป่วย
 * @property int $dept_group_id กลุ่มแผนก
 * @property string $dept_id รหัสแผนก
 * @property int $priority_id ความสำคัญ
 * @property int $queue_type ประเภทคิว
 * @property int $queue_status_id สถานะคิว
 * @property string $created_at วันที่บันทึก
 * @property string $updated_at วันที่แก้ไข
 * @property int $created_by ผู้บันทึก
 * @property int $updated_by ผู้แก้ไข
 */
class TblQueue extends \yii\db\ActiveRecord
{
    use ModelTrait;
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
                    ActiveRecord::EVENT_BEFORE_INSERT => 'queue_no',
                ],
                'value' => function ($event) {
                    if(empty($this->queue_no)){
                        return $this->generateNumber();
                    }else{
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
            [['patient_id', 'dept_group_id', 'dept_id', 'priority_id', 'queue_type', 'queue_status_id'], 'required'],
            [['patient_id', 'dept_group_id', 'priority_id', 'queue_type', 'queue_status_id', 'created_by', 'updated_by'], 'integer'],
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
            'priority_id' => 'ความสำคัญ',
            'queue_type' => 'ประเภทคิว',
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

    // ประเภทคิว
    public function getQueueTypeName($type)
    {
        $types = $this->getQueueTypes();
        return ArrayHelper::getValue($types,$type, '');
    }

    // ประเภทคิว
    public function getQueueTypes()
    {
        return [
            1 => 'คิวซักประวัติ',
            2 => 'คิวห้องตรวจ'
        ];
    }

    private function generateNumber()
    {
        $department = $this->findModelDept($this->dept_id); // แผนก
        $maxId = $this->find()->where(['dept_id' => $this->dept_id, 'dept_group_id' => $this->dept_group_id])->max('queue_id');
        $no = 1;
        if($maxId) {
            $modelQueue = $this->findOne($maxId);
            $no = $modelQueue['queue_no'];
        }
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
        }*/
        $component = \Yii::createObject([
            'class'     => AutoNumber::className(),
            'prefix'    => $department ? $department['dept_prefix'] : 'A',
            'number'    => $no,
            'digit'     => $department ? (int)$department['dept_num_digit'] : 3,
        ]);
        return $component->generate();
    }
}
