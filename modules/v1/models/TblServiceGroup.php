<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_service_group".
 *
 * @property int $service_group_id รหัสกลุ่มบริการ
 * @property string $service_group_name ชื่อกลุ่มบริการ
 * @property int $service_group_order ลำดับการแสดงผล
 * @property int $floor_id ชั้น
 * @property int $queue_service_id ประเภทคิวบริการ
 */
class TblServiceGroup extends \yii\db\ActiveRecord
{
    public $order_group;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_service_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_group_name', 'floor_id', 'queue_service_id'], 'required'],
            [['service_group_order', 'floor_id', 'queue_service_id'], 'integer'],
            [['service_group_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'service_group_id' => 'รหัสกลุ่มบริการ',
            'service_group_name' => 'ชื่อกลุ่มบริการ',
            'service_group_order' => 'ลำดับการแสดงผล',
            'floor_id' => 'ชั้น',
            'queue_service_id' => 'ประเภทคิวบริการ',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblServiceGroupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblServiceGroupQuery(get_called_class());
    }

    // ประเภทคิวบริการ
    public function getQueueService()
    {
        return $this->hasOne(TblQueueService::className(), ['queue_service_id' => 'queue_service_id']);
    }

    // ชั้น
    public function getFloor()
    {
        return $this->hasOne(TblFloor::className(), ['floor_id' => 'floor_id']);
    }
}
