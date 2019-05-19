<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "tbl_prefix".
 *
 * @property int $prefix_id
 * @property string $prefix_code ตัวอักษร
 */
class TblPrefix extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_prefix';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['prefix_code'], 'required'],
            [['prefix_code'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'prefix_id' => 'Prefix ID',
            'prefix_code' => 'ตัวอักษร',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblPrefixQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblPrefixQuery(get_called_class());
    }
}
