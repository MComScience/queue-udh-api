<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_counter".
 *
 * @property int $counter_id รหัสเคาท์เตอร์
 * @property string $counter_name ชื่อเคาท์เตอร์
 */
class TblCounter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_counter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['counter_name'], 'required'],
            [['counter_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'counter_id' => 'รหัสเคาท์เตอร์',
            'counter_name' => 'ชื่อเคาท์เตอร์',
        ];
    }

    // จุดบริการ / ช่องบริการ
    public function getCounterServices()
    {
        return $this->hasMany(TblCounterService::className(), ['counter_id' => 'counter_id']);
    }
}
