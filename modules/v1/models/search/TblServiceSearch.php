<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblService;

/**
 * TblServiceSearch represents the model behind the search form about `app\modules\v1\models\TblService`.
 */
class TblServiceSearch extends TblService
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'service_code', 'service_group_id', 'service_num_digit', 'card_id', 'prefix_id', 'prefix_running', 'print_copy_qty', 'service_order', 'service_status'], 'integer'],
            [['service_name', 'service_prefix'], 'safe'],
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
        $query = TblService::find();

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
            'service_id' => $this->service_id,
            'service_code' => $this->service_code,
            'service_group_id' => $this->service_group_id,
            'service_num_digit' => $this->service_num_digit,
            'card_id' => $this->card_id,
            'prefix_id' => $this->prefix_id,
            'prefix_running' => $this->prefix_running,
            'print_copy_qty' => $this->print_copy_qty,
            'service_order' => $this->service_order,
            'service_status' => $this->service_status,
        ]);

        $query->andFilterWhere(['like', 'service_name', $this->service_name])
            ->andFilterWhere(['like', 'service_prefix', $this->service_prefix]);

        return $dataProvider;
    }
}
