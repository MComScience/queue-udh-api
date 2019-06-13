<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblQueueFailed]].
 *
 * @see TblQueueFailed
 */
class TblQueueFailedQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblQueueFailed[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblQueueFailed|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
