<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblCard;

/**
 * TblCardSearch represents the model behind the search form about `app\modules\v1\models\TblCard`.
 */
class TblCardSearch extends TblCard
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_id', 'card_status'], 'integer'],
            [['card_name', 'card_template'], 'safe'],
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
        $query = TblCard::find();

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
            'card_id' => $this->card_id,
            'card_status' => $this->card_status,
        ]);

        $query->andFilterWhere(['like', 'card_name', $this->card_name])
            ->andFilterWhere(['like', 'card_template', $this->card_template]);

        return $dataProvider;
    }
}
