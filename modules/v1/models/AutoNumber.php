<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "auto_number".
 *
 * @property int $id
 * @property int $prefix_id รหัสเลขนำหน้า
 * @property string $dept_code รหัสแผนก
 * @property string $number เลขคิว
 * @property int $flag
 * @property string $updated_at วันที่ล่าสุด
 */
class AutoNumber extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auto_number';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['prefix_id', 'number', 'flag', 'updated_at'], 'required'],
            [['prefix_id', 'flag'], 'integer'],
            [['updated_at'], 'safe'],
            [['dept_code', 'number'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prefix_id' => 'รหัสเลขนำหน้า',
            'dept_code' => 'รหัสแผนก',
            'number' => 'เลขคิว',
            'flag' => 'Flag', // 0 คือไม่รันต่อเนื่อง 1 คือ รันต่อเนื่องแผนกอื่น
            'updated_at' => 'วันที่ล่าสุด',
        ];
    }

    public function getPrefix()
    {
        return $this->hasOne(TblPrefix::className(), ['prefix_id' => 'prefix_id']);
    }

    /**
     * {@inheritdoc}
     * @return AutoNumberQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AutoNumberQuery(get_called_class());
    }
}
