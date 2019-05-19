<?php

namespace app\modules\v1\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblPrefix;

/**
 * TblPrefixSearch represents the model behind the search form about `app\modules\v1\models\TblPrefix`.
 */
class TblPrefixSearch extends TblPrefix
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prefix_id'], 'integer'],
            [['prefix_code'], 'safe'],
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
        $query = TblPrefix::find();

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
            'prefix_id' => $this->prefix_id,
        ]);

        $query->andFilterWhere(['like', 'prefix_code', $this->prefix_code]);

        return $dataProvider;
    }
}
