<?php

namespace app\modules\v1\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\v1\models\TblKiosk;

/**
 * TblKioskSearch represents the model behind the search form about `app\modules\v1\models\TblKiosk`.
 */
class TblKioskSearch extends TblKiosk
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kiosk_id'], 'integer'],
            [['kiosk_name', 'kiosk_des'], 'safe'],
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
        $query = TblKiosk::find();

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
            'kiosk_id' => $this->kiosk_id,
        ]);

        $query->andFilterWhere(['like', 'kiosk_name', $this->kiosk_name])
            ->andFilterWhere(['like', 'kiosk_des', $this->kiosk_des]);

        return $dataProvider;
    }
}
