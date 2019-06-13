<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_queue_failed".
 *
 * @property int $queue_failed_id
 * @property string $queue_failed_message ปัญหา
 * @property string $hn HN
 * @property string $fullname ชื่อ-นามสกุล
 * @property string $created_at วันที่บันทึก
 */
class TblQueueFailed extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_queue_failed';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_failed_message', 'created_at'], 'required'],
            [['created_at'], 'safe'],
            [['queue_failed_message', 'fullname'], 'string', 'max' => 255],
            [['hn'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'queue_failed_id' => 'Queue Failed ID',
            'queue_failed_message' => 'ปัญหา',
            'hn' => 'HN',
            'fullname' => 'ชื่อ-นามสกุล',
            'created_at' => 'วันที่บันทึก',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblQueueFailedQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblQueueFailedQuery(get_called_class());
    }
}
