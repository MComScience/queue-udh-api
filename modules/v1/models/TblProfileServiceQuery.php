<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblProfileService]].
 *
 * @see TblProfileService
 */
class TblProfileServiceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblProfileService[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblProfileService|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
