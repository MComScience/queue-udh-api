<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblProfileService;

/**
 * TblProfileServiceSearch represents the model behind the search form about `app\modules\v1\models\TblProfileService`.
 */
class TblProfileServiceSearch extends TblProfileService
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['profile_service_id', 'counter_id', 'profile_service_status'], 'integer'],
            [['profile_service_name', 'service_id'], 'safe'],
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
        $query = TblProfileService::find();

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
            'profile_service_id' => $this->profile_service_id,
            'counter_id' => $this->counter_id,
            'profile_service_status' => $this->profile_service_status,
        ]);

        $query->andFilterWhere(['like', 'profile_service_name', $this->profile_service_name])
            ->andFilterWhere(['like', 'service_id', $this->service_id]);

        return $dataProvider;
    }
}
