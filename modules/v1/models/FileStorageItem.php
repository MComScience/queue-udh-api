<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "file_storage_item".
 *
 * @property int $id
 * @property string $base_url
 * @property string $path
 * @property string $type
 * @property int $size
 * @property string $name
 * @property int $ref_id
 * @property string $created_at
 */
class FileStorageItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file_storage_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['base_url', 'path', 'created_at'], 'required'],
            [['size', 'ref_id'], 'integer'],
            [['created_at'], 'safe'],
            [['base_url', 'path'], 'string', 'max' => 1024],
            [['type', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'base_url' => 'Base Url',
            'path' => 'Path',
            'type' => 'Type',
            'size' => 'Size',
            'name' => 'Name',
            'ref_id' => 'Ref ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FileStorageItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FileStorageItemQuery(get_called_class());
    }
}
