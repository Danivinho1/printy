<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class VentasSearch extends Ventas
{
    public function rules()
    {
        return [
            [['entrega_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Ventas::find()->alias('v')
            ->leftJoin('catalogos e', 'v.entrega_id = e.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['v.id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtro directo por entrega_id (si viene en formulario)
        $query->andFilterWhere(['v.entrega_id' => $this->entrega_id]);

        // Filtro especial desde la URL (?entrega=urgente)
        $filtroEntrega = \Yii::$app->request->get('entrega');
        if ($filtroEntrega === 'urgente') {
            $query->andWhere(['e.nombre' => 'Urgente']);
        }

        return $dataProvider;
    }

    // Conteos de total y urgentes
    public static function getFiltrosConteos()
    {
        $total = Ventas::find()->count();

        $urgentes = Ventas::find()->alias('v')
            ->leftJoin('catalogos e', 'v.entrega_id = e.id')
            ->where(['e.nombre' => 'Urgente'])
            ->count();

        return [
            'total' => $total,
            'urgentes' => $urgentes,
        ];
    }
}
