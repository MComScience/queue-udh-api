<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblDeptGroup;

/**
 * TblDeptGroupSearch represents the model behind the search form about `app\modules\v1\models\TblDeptGroup`.
 */
class TblDeptGroupSearch extends TblDeptGroup
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dept_group_id'], 'integer'],
            [['dept_group_name'], 'safe'],
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
        $query = TblDeptGroup::find();

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
        ]);

        $query->andFilterWhere(['like', 'dept_group_name', $this->dept_group_name]);

        return $dataProvider;
    }
}
