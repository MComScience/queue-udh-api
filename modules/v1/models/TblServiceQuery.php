<?php

namespace app\modules\v1\models;

use app\modules\v1\models\TblService;

/**
 * This is the ActiveQuery class for [[TblService]].
 *
 * @see TblService
 */
class TblServiceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return TblService[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return TblService|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function orderByAsc()
    {
        return $this->orderBy('service_order asc');
    }

    public function isActive()
    {
        return $this->andWhere(['service_status' => TblService::STATUS_ACTIVE]);
    }

    public function findByServiceCode($service_code = null)
    {
        return $this->andOnCondition(['service_code' => $service_code])->one();
    }
}
