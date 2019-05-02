<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_sound".
 *
 * @property int $sound_id
 * @property string $sound_name ชื่อไฟล์
 * @property string $sound_path_name โฟรเดอร์ไฟล์
 * @property string $sound_th เสียงเรียก
 * @property int $sound_type ประเภทเสียง
 */
class TblSound extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_sound';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sound_name', 'sound_path_name'], 'required'],
            [['sound_type'], 'integer'],
            [['sound_name', 'sound_path_name', 'sound_th'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sound_id' => 'Sound ID',
            'sound_name' => 'ชื่อไฟล์',
            'sound_path_name' => 'โฟรเดอร์ไฟล์',
            'sound_th' => 'เสียงเรียก',
            'sound_type' => 'ประเภทเสียง',
        ];
    }
}
