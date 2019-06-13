<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_queue_service".
 *
 * @property int $queue_service_id รหัส
 * @property string $queue_service_name ชื่อประเภทคิวบริการ
 */
class TblQueueService extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_queue_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_service_name'], 'required'],
            [['queue_service_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'queue_service_id' => 'รหัส',
            'queue_service_name' => 'ชื่อประเภทคิวบริการ',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblQueueServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblQueueServiceQuery(get_called_class());
    }
}
