<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[DeptqD]].
 *
 * @see DeptqD
 */
class DeptqDQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return DeptqD[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return DeptqD|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
