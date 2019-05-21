<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblFloor;

/**
 * TblFloorSearch represents the model behind the search form about `app\modules\v1\models\TblFloor`.
 */
class TblFloorSearch extends TblFloor
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['floor_id'], 'integer'],
            [['floor_name'], 'safe'],
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
        $query = TblFloor::find();

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
            'floor_id' => $this->floor_id,
        ]);

        $query->andFilterWhere(['like', 'floor_name', $this->floor_name]);

        return $dataProvider;
    }
}
