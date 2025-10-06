<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * DisenoSearch represents the model behind the search form of `app\models\Diseno`.
 */
class DisenoSearch extends Diseno
{
    public $filtro_estatus;

    public function rules()
    {
        return [
            [['id', 'venta_id', 'tipo_letrero_id', 'entrega_id', 'responsable_id', 'contacto_cliente_id', 'especificaciones_id', 'vectorizado_id', 'enviado_corte_id', 'avance', 'estatus_id'], 'integer'],
            [['nombre_letrero', 'telefono', 'fecha_confirmacion', 'created_at', 'updated_at', 'filtro_estatus'], 'safe'],
            [['extra_precio'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        // Query base con alias únicos
        $query = Diseno::find()->alias('d')
            ->leftJoin('ventas v', 'd.venta_id = v.id')
            ->leftJoin('catalogos e', 'd.entrega_id = e.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['d.id' => SORT_ASC],
                'attributes' => [
                    'd.id' => [
                        'asc' => ['d.id' => SORT_ASC],
                        'desc' => ['d.id' => SORT_DESC],
                    ],
                    'd.created_at' => [
                        'asc' => ['d.created_at' => SORT_ASC],
                        'desc' => ['d.created_at' => SORT_DESC],
                    ]
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros básicos
        $query->andFilterWhere([
            'd.id' => $this->id,
            'd.venta_id' => $this->venta_id,
            'd.tipo_letrero_id' => $this->tipo_letrero_id,
            'd.entrega_id' => $this->entrega_id,
            'd.responsable_id' => $this->responsable_id,
            'd.contacto_cliente_id' => $this->contacto_cliente_id,
            'd.especificaciones_id' => $this->especificaciones_id,
            'd.vectorizado_id' => $this->vectorizado_id,
            'd.enviado_corte_id' => $this->enviado_corte_id,
            'd.avance' => $this->avance,
            'd.extra_precio' => $this->extra_precio,
        ]);

        $query->andFilterWhere(['like', 'd.nombre_letrero', $this->nombre_letrero])
              ->andFilterWhere(['like', 'd.telefono', $this->telefono]);

        // Filtro de estatus usando los joins existentes
        $filtroEstatus = \Yii::$app->request->get('estatus');
        if ($filtroEstatus && in_array($filtroEstatus, ['urgente', 'pendiente', 'listo'])) {
            $this->applyEstatusFilter($query, $filtroEstatus);
        }

        // Relaciones
        $query->with(['estatus','entrega','tipoLetrero','responsable','venta.extras','venta.adicionales']);

        // Orden: fechas reales más próximas PRIMERO, luego urgentes sin fecha, luego normales sin fecha
        $query->addOrderBy(new Expression("
            CASE
                WHEN v.fecha_entrega IS NOT NULL THEN 1
                WHEN e.nombre = 'Urgente' THEN 2
                ELSE 3
            END ASC,
            CASE
                WHEN v.fecha_entrega IS NOT NULL THEN v.fecha_entrega
                ELSE NOW()
            END ASC
        "));
        $query->addOrderBy(['d.id' => SORT_ASC, 'd.created_at' => SORT_ASC]);

        return $dataProvider;
    }

    private function applyEstatusFilter($query, $filtroEstatus)
    {
        // Agregar join para estatus solo si no existe
        $query->leftJoin('catalogos s', 'd.estatus_id = s.id');
        
        switch ($filtroEstatus) {
            case 'urgente':
                // Urgentes que NO están listos (usando alias existente 'e')
                $query->andWhere(['e.nombre' => 'Urgente'])
                      ->andWhere(['OR',
                          ['s.nombre' => null],
                          ['!=', 's.nombre', 'Listo']
                      ]);
                break;
            case 'pendiente':
                // Solo pendientes
                $query->andWhere(['s.nombre' => 'Pendiente']);
                break;
            case 'listo':
                // Solo listos
                $query->andWhere(['s.nombre' => 'Listo']);
                break;
        }
    }

    // Conteos de filtros - SIN DUPLICAR JOINS
    public static function getFiltrosConteos()
    {
        $total = Diseno::find()->count();

        // Urgentes que NO están listos
        $urgentes = Diseno::find()->alias('d')
            ->leftJoin('catalogos e', 'd.entrega_id = e.id')
            ->leftJoin('catalogos s', 'd.estatus_id = s.id')
            ->where(['e.nombre' => 'Urgente'])
            ->andWhere(['OR', ['s.nombre' => null], ['!=', 's.nombre', 'Listo']])
            ->count();

        // Todos los pendientes
        $pendientes = Diseno::find()->alias('d')
            ->leftJoin('catalogos s', 'd.estatus_id = s.id')
            ->where(['s.nombre' => 'Pendiente'])
            ->count();

        // Todos los listos
        $listos = Diseno::find()->alias('d')
            ->leftJoin('catalogos s', 'd.estatus_id = s.id')
            ->where(['s.nombre' => 'Listo'])
            ->count();

        return [
            'total' => $total,
            'urgentes' => $urgentes,
            'pendientes' => $pendientes,
            'listos' => $listos
        ];
    }
}