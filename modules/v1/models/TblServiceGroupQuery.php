<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblServiceGroup]].
 *
 * @see TblServiceGroup
 */
class TblServiceGroupQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblServiceGroup[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblServiceGroup|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function orderByAsc()
    {
        return $this->orderBy('service_group_order asc');
    }
}
