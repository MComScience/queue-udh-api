<?php

namespace app\modules\v1\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use app\modules\v1\behaviors\CoreMultiValueBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "tbl_queue".
 *
 * @property int $queue_id
 * @property string $queue_no หมายเลขคิว
 * @property string $queue_hn HN
 * @property string $fullname ชื่อ-นามสกุล
 * @property string $user_info ข้อมูลผู้ป่วย
 * @property string $department_code รหัสแผนก
 * @property string $created_at วันที่บันทึก
 * @property string $updated_at วันที่แก้ไข
 * @property int $created_by ผู้บันทึก
 * @property int $updated_by ผู้แก้ไข
 */
class TblQueue extends \yii\db\ActiveRecord
{
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
            /* [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by','updated_by'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                ],
            ], */
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => CoreMultiValueBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'queue_no',
                ],
                'value' => function ($event) {
                    if(empty($this->queue_no)){
                        return $this->generateQnumber();
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
            [['queue_hn', 'fullname', 'department_code'], 'required'],
            [['user_info'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['queue_no', 'queue_hn', 'department_code'], 'string', 'max' => 50],
            [['fullname'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'queue_id' => 'Queue ID',
            'queue_no' => 'หมายเลขคิว',
            'queue_hn' => 'HN',
            'fullname' => 'ชื่อ-นามสกุล',
            'user_info' => 'ข้อมูลผู้ป่วย',
            'department_code' => 'รหัสแผนก',
            'created_at' => 'วันที่บันทึก',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้บันทึก',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    public function generateQnumber(){
        $queue = ArrayHelper::map($this->find()->where(['department_code' => $this->department_code])->all(),'queue_id','queue_no');
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
            'class'     => \app\modules\v1\components\AutoNumber::className(),
            'prefix'    => 'A',
            'number'    => ArrayHelper::getValue($queue,$qid,null),
            'digit'     => 3,
        ]);
        return $component->generate();
    }

    public function getDepartment()
    {
        return $this->hasOne(TblDepartment::className(), ['department_code' => 'department_code']);
    }
}
