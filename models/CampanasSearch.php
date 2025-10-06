<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Campanas;

/**
 * CampanasSearch represents the model behind the search form of `app\models\Campanas`.
 */
class CampanasSearch extends Campanas
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'campaña_id', 'asesor_id', 'mensajes', 'retorno'], 'integer'],
            [['inversion', 'presupuesto'], 'number'],
            [['analisis', 'mensaje_predeterminado', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Campanas::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'campaña_id' => $this->campaña_id,
            'asesor_id' => $this->asesor_id,
            'inversion' => $this->inversion,
            'mensajes' => $this->mensajes,
            'retorno' => $this->retorno,
            'presupuesto' => $this->presupuesto,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'analisis', $this->analisis])
            ->andFilterWhere(['like', 'mensaje_predeterminado', $this->mensaje_predeterminado]);

        return $dataProvider;
    }
}
