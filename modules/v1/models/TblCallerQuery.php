<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblCaller]].
 *
 * @see TblCaller
 */
class TblCallerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblCaller[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblCaller|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function betweenCreateAt($startDate, $endDate)
    {
        return $this->andWhere(['between', 'created_at', $startDate, $endDate]);
    }
}
