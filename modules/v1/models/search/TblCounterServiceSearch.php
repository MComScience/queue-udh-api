<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblCounterService;

/**
 * TblCounterServiceSearch represents the model behind the search form about `app\modules\v1\models\TblCounterService`.
 */
class TblCounterServiceSearch extends TblCounterService
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counter_service_id', 'counter_service_no', 'counter_service_sound', 'counter_service_no_sound', 'counter_id', 'counter_service_status'], 'integer'],
            [['counter_service_name'], 'safe'],
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
        $query = TblCounterService::find()->orderBy('counter_id asc, counter_service_no asc');

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
            'counter_service_id' => $this->counter_service_id,
            'counter_service_no' => $this->counter_service_no,
            'counter_service_sound' => $this->counter_service_sound,
            'counter_service_no_sound' => $this->counter_service_no_sound,
            'counter_id' => $this->counter_id,
            'counter_service_status' => $this->counter_service_status,
        ]);

        $query->andFilterWhere(['like', 'counter_service_name', $this->counter_service_name]);

        return $dataProvider;
    }
}
