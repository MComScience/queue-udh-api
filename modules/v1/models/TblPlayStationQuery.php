<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblPlayStation]].
 *
 * @see TblPlayStation
 */
class TblPlayStationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblPlayStation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblPlayStation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
