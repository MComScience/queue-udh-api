<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblCounter]].
 *
 * @see TblCounter
 */
class TblCounterQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblCounter[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblCounter|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
