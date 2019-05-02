<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblDept;

/**
 * TblDeptSearch represents the model behind the search form about `app\modules\v1\models\TblDept`.
 */
class TblDeptSearch extends TblDept
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dept_id', 'dept_name', 'dept_prefix'], 'safe'],
            [['dept_group_id', 'dept_num_digit', 'card_id', 'dept_status'], 'integer'],
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
        $query = TblDept::find();

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
            'dept_group_id' => $this->dept_group_id,
            'dept_num_digit' => $this->dept_num_digit,
            'card_id' => $this->card_id,
            'dept_status' => $this->dept_status,
        ]);

        $query->andFilterWhere(['like', 'dept_id', $this->dept_id])
            ->andFilterWhere(['like', 'dept_name', $this->dept_name])
            ->andFilterWhere(['like', 'dept_prefix', $this->dept_prefix]);

        return $dataProvider;
    }
}
