<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblPrefix]].
 *
 * @see TblPrefix
 */
class TblPrefixQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblPrefix[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblPrefix|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
