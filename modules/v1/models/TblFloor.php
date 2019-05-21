<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_floor".
 *
 * @property int $floor_id รหัส
 * @property string $floor_name ชื่อชั้น
 */
class TblFloor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_floor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['floor_name'], 'required'],
            [['floor_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'floor_id' => 'รหัส',
            'floor_name' => 'ชื่อชั้น',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblFloorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblFloorQuery(get_called_class());
    }
}
