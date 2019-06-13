<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblDisplay;

/**
 * TblDisplaySearch represents the model behind the search form about `app\modules\v1\models\TblDisplay`.
 */
class TblDisplaySearch extends TblDisplay
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['display_id', 'display_status'], 'integer'],
            [['display_name', 'display_css', 'counter_id', 'service_id'], 'safe'],
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
        $query = TblDisplay::find();

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
            'display_id' => $this->display_id,
            'display_status' => $this->display_status,
        ]);

        $query->andFilterWhere(['like', 'display_name', $this->display_name])
            ->andFilterWhere(['like', 'display_css', $this->display_css])
            ->andFilterWhere(['like', 'counter_id', $this->counter_id])
            ->andFilterWhere(['like', 'service_id', $this->service_id]);

        return $dataProvider;
    }
}
