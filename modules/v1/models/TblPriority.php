<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_priority".
 *
 * @property int $priority_id รหัส
 * @property string $priority_name ชื่อความสำคัญ
 */
class TblPriority extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_priority';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['priority_name'], 'required'],
            [['priority_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'priority_id' => 'รหัส',
            'priority_name' => 'ชื่อความสำคัญ',
        ];
    }
}
