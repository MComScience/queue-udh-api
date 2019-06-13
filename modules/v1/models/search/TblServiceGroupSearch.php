<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblServiceGroup;

/**
 * TblServiceGroupSearch represents the model behind the search form about `app\modules\v1\models\TblServiceGroup`.
 */
class TblServiceGroupSearch extends TblServiceGroup
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_group_id', 'service_group_order', 'floor_id', 'queue_service_id'], 'integer'],
            [['service_group_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TblServiceGroup::find()->orderBy('service_group_order asc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'service_group_id' => $this->service_group_id,
            'service_group_order' => $this->service_group_order,
            'floor_id' => $this->floor_id,
            'queue_service_id' => $this->queue_service_id,
        ]);

        $query->andFilterWhere(['like', 'service_group_name', $this->service_group_name]);

        return $dataProvider;
    }
}
