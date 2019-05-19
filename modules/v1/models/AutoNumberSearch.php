<?php

namespace app\modules\v1\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\AutoNumber;

/**
 * AutoNumberSearch represents the model behind the search form about `app\modules\v1\models\AutoNumber`.
 */
class AutoNumberSearch extends AutoNumber
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'prefix_id', 'flag'], 'integer'],
            [['dept_code', 'number', 'updated_at'], 'safe'],
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
        $query = AutoNumber::find()->orderBy('updated_at desc');

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
            'id' => $this->id,
            'prefix_id' => $this->prefix_id,
            'flag' => $this->flag,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'dept_code', $this->dept_code])
            ->andFilterWhere(['like', 'number', $this->number]);

        return $dataProvider;
    }
}
