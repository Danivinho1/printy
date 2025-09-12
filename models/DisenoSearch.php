<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DisenoSearch represents the model behind the search form of `app\models\Diseno`.
 */
class DisenoSearch extends Diseno
{
    public $filtro_estatus;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'venta_id', 'tipo_letrero_id', 'entrega_id', 'responsable_id', 'contacto_cliente_id', 'especificaciones_id', 'vectorizado_id', 'enviado_corte_id', 'avance', 'estatus_id'], 'integer'],
            [['nombre_letrero', 'telefono', 'fecha_confirmacion', 'created_at', 'updated_at', 'filtro_estatus'], 'safe'],
            [['extra_precio'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     */
    public function search($params)
    {
        $query = Diseno::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Aplicar filtros básicos
        $query->andFilterWhere([
            'id' => $this->id,
            'venta_id' => $this->venta_id,
            'tipo_letrero_id' => $this->tipo_letrero_id,
            'entrega_id' => $this->entrega_id,
            'responsable_id' => $this->responsable_id,
            'contacto_cliente_id' => $this->contacto_cliente_id,
            'especificaciones_id' => $this->especificaciones_id,
            'vectorizado_id' => $this->vectorizado_id,
            'enviado_corte_id' => $this->enviado_corte_id,
            'avance' => $this->avance,
            'estatus_id' => $this->estatus_id,
            'extra_precio' => $this->extra_precio,
            'fecha_confirmacion' => $this->fecha_confirmacion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'nombre_letrero', $this->nombre_letrero])
            ->andFilterWhere(['like', 'telefono', $this->telefono]);

        // Aplicar filtros especiales SOLO si hay filtro_estatus
        $filtroEstatus = \Yii::$app->request->get('estatus');
        if ($filtroEstatus) {
            $this->applyEstatusFilter($query, $filtroEstatus);
        }

        // Incluir relaciones
        $query->with([
            'estatus',
            'entrega', 
            'tipoLetrero',
            'responsable',
            'venta.extras',
            'venta.adicionales'
        ]);

        return $dataProvider;
    }

    /**
     * Aplica filtros de estatus con la lógica correcta
     */
    private function applyEstatusFilter($query, $filtroEstatus)
    {
        switch ($filtroEstatus) {
            case 'urgente':
                // Solo urgentes que NO están listos
                $query->leftJoin('catalogos ce', 'diseno.entrega_id = ce.id')
                      ->leftJoin('catalogos cs', 'diseno.estatus_id = cs.id')
                      ->andWhere(['ce.nombre' => 'Urgente'])
                      ->andWhere([
                          'OR',
                          ['cs.nombre' => null],
                          ['!=', 'cs.nombre', 'Listo']
                      ]);
                break;
                
            case 'pendiente':
                // Pendientes (incluye urgentes + pendientes)
                $query->leftJoin('catalogos ce', 'diseno.entrega_id = ce.id')
                      ->leftJoin('catalogos cs', 'diseno.estatus_id = cs.id')
                      ->andWhere([
                          'OR',
                          ['cs.nombre' => 'Pendiente'], // Pendientes normales
                          [
                              'AND',
                              ['ce.nombre' => 'Urgente'], // Urgentes
                              ['cs.nombre' => 'Pendiente'] // que también son pendientes
                          ]
                      ]);
                break;
                
            case 'listo':
                // Todos los listos (sin importar si son urgentes)
                $query->leftJoin('catalogos cs', 'diseno.estatus_id = cs.id')
                      ->andWhere(['cs.nombre' => 'Listo']);
                break;
        }
    }

    /**
     * Obtiene conteos para los filtros
     */
    public static function getFiltrosConteos()
    {
        // Total de registros FIJO
        $total = Diseno::find()->count();

        // Urgentes que NO están listos
        $urgentes = Diseno::find()
            ->leftJoin('catalogos ce', 'diseno.entrega_id = ce.id')
            ->leftJoin('catalogos cs', 'diseno.estatus_id = cs.id')
            ->where(['ce.nombre' => 'Urgente'])
            ->andWhere([
                'OR',
                ['cs.nombre' => null],
                ['!=', 'cs.nombre', 'Listo']
            ])
            ->count();

        // Pendientes (TODOS los que tienen estatus pendiente)
        $pendientes = Diseno::find()
            ->leftJoin('catalogos cs', 'diseno.estatus_id = cs.id')
            ->where(['cs.nombre' => 'Pendiente'])
            ->count();

        // Listos (todos)
        $listos = Diseno::find()
            ->leftJoin('catalogos cs', 'diseno.estatus_id = cs.id')
            ->where(['cs.nombre' => 'Listo'])
            ->count();

        return [
            'total' => $total,
            'urgentes' => $urgentes,
            'pendientes' => $pendientes,
            'listos' => $listos
        ];
    }
}