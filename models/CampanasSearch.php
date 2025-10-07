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
        [['nombre', 'analisis', 'mensaje_predeterminado', 'created_at', 'updated_at'], 'safe'],
        [['inversion', 'presupuesto'], 'number'],
    ];
}

public function search($params)
{
    $query = Campanas::find();
    $dataProvider = new ActiveDataProvider(['query' => $query]);

    $this->load($params);

    if (!$this->validate()) {
        return $dataProvider;
    }

    $query->andFilterWhere(['id' => $this->id])
          ->andFilterWhere(['campaña_id' => $this->campaña_id])
          ->andFilterWhere(['asesor_id' => $this->asesor_id])
          ->andFilterWhere(['mensajes' => $this->mensajes])
          ->andFilterWhere(['retorno' => $this->retorno])
          ->andFilterWhere(['like', 'nombre', $this->nombre])     // <-- filtro
          ->andFilterWhere(['like', 'analisis', $this->analisis]);

    return $dataProvider;
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
    
}
