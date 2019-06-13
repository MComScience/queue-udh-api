<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[BillH]].
 *
 * @see BillH
 */
class BillHQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return BillH[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return BillH|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
