<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 14/5/2562
 * Time: 10:36
 */
namespace app\modules\v1\models;

/**
 * This is the ActiveQuery class for [[TblDept]].
 *
 * @see TblDept
 */
class TblDeptQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblDept[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblDept|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function findByDeptCode($dept_code = null)
    {
        return $this->andOnCondition(['dept_id' => $dept_code])->one();
    }
}