<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblPlayStation;

/**
 * TblPlayStationSearch represents the model behind the search form about `app\modules\v1\models\TblPlayStation`.
 */
class TblPlayStationSearch extends TblPlayStation
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['play_station_id', 'play_station_status'], 'integer'],
            [['play_station_name', 'counter_id', 'counter_service_id'], 'safe'],
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
        $query = TblPlayStation::find();

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
            'play_station_id' => $this->play_station_id,
            'play_station_status' => $this->play_station_status,
        ]);

        $query->andFilterWhere(['like', 'play_station_name', $this->play_station_name])
            ->andFilterWhere(['like', 'counter_id', $this->counter_id])
            ->andFilterWhere(['like', 'counter_service_id', $this->counter_service_id]);

        return $dataProvider;
    }
}
