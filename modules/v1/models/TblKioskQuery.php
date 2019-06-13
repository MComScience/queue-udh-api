<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblKiosk]].
 *
 * @see TblKiosk
 */
class TblKioskQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblKiosk[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblKiosk|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
