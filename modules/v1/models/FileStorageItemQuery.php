<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 14/5/2562
 * Time: 10:47
 */
namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[FileStorageItem]].
 *
 * @see FileStorageItem
 */
class FileStorageItemQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return FileStorageItem[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return FileStorageItem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function findByRefId($ref_id = null)
    {
        return $this->andOnCondition(['ref_id' => $ref_id])->one();
    }
}