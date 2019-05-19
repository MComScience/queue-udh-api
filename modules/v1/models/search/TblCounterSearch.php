<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblCounter;

/**
 * TblCounterSearch represents the model behind the search form about `app\modules\v1\models\TblCounter`.
 */
class TblCounterSearch extends TblCounter
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counter_id'], 'integer'],
            [['counter_name'], 'safe'],
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
        $query = TblCounter::find();

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
            'counter_id' => $this->counter_id,
        ]);

        $query->andFilterWhere(['like', 'counter_name', $this->counter_name]);

        return $dataProvider;
    }
}
