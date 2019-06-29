<?php

namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblDoctor]].
 *
 * @see TblDoctor
 */
class TblDoctorQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblDoctor[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblDoctor|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
