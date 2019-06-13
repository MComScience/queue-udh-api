<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_room_type".
 *
 * @property int $room_type_id รหัส
 * @property string $room_type_name ชื่อห้อง
 */
class TblRoomType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_room_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['room_type_name'], 'required'],
            [['room_type_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'room_type_id' => 'รหัส',
            'room_type_name' => 'ชื่อห้อง',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblRoomTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblRoomTypeQuery(get_called_class());
    }
}
