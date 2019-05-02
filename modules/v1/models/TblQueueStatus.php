<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_queue_status".
 *
 * @property int $queue_status_id รหัสสถานะ
 * @property string $queue_status_name ชื่อสถานะ
 */
class TblQueueStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_queue_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_status_name'], 'required'],
            [['queue_status_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'queue_status_id' => 'รหัสสถานะ',
            'queue_status_name' => 'ชื่อสถานะ',
        ];
    }
}
