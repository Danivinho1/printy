<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class ProduccionSearch extends Produccion
{
    public $estatus; // virtual para recibir el filtro de la URL

    public function rules()
    {
        return [
            [['id', 'venta_id', 'disenador_id', 'empaquetado_id'], 'integer'],
            [['fecha_entrega', 'created_at', 'updated_at', 'estatus'], 'safe'],
            // NO incluir entrega_id aquí porque no existe en la tabla
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Produccion::find()->alias('p')
            ->leftJoin('ventas v', 'p.venta_id = v.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['p.id' => SORT_DESC],
                'attributes' => [
                    'p.id' => [
                        'asc' => ['p.id' => SORT_ASC],
                        'desc' => ['p.id' => SORT_DESC],
                    ],
                    'v.fecha_entrega' => [
                        'asc' => ['v.fecha_entrega' => SORT_ASC],
                        'desc' => ['v.fecha_entrega' => SORT_DESC],
                    ],
                    'p.created_at' => [
                        'asc' => ['p.created_at' => SORT_ASC],
                        'desc' => ['p.created_at' => SORT_DESC],
                    ],
                    // NO incluir entrega_id en el sorting
                ],
            ],
        ]);

        // 🔹 Cargar parámetros de la URL (incluye estatus)
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // filtros básicos - SIN entrega_id
        $query->andFilterWhere([
            'p.id' => $this->id,
            'p.venta_id' => $this->venta_id,
            'p.disenador_id' => $this->disenador_id,
            'p.empaquetado_id' => $this->empaquetado_id,
            // NO filtrar por entrega_id porque no existe en produccion
        ]);

        // 🔹 Aplicar filtro por estatus si existe
        if ($this->estatus) {
            $this->applyEstatusFilter($query, $this->estatus);
        }

        // relaciones
        $query->with(['venta', 'disenador', 'empaquetado']);

        // Orden: primero los que tienen fecha de entrega más próxima
        $query->addOrderBy(new Expression("
            CASE
                WHEN v.fecha_entrega IS NOT NULL THEN 1
                ELSE 2
            END ASC,
            v.fecha_entrega ASC
        "));

        return $dataProvider;
    }

    private function applyEstatusFilter($query, $filtroEstatus)
    {
        // Agregar join para empaquetado solo si no existe
        $query->leftJoin('catalogos e', 'p.empaquetado_id = e.id');
        
        switch ($filtroEstatus) {
            case 'urgente':
                // Urgentes que NO están listos (usando la ruta venta->entrega)
                $query->leftJoin('catalogos ent', 'v.entrega_id = ent.id')
                      ->andWhere(['ent.nombre' => 'Urgente'])
                      ->andWhere(['OR',
                          ['e.nombre' => null],
                          ['!=', 'e.nombre', 'Listo']
                      ]);
                break;
            case 'pendiente':
                // Solo pendientes
                $query->andWhere([
                    'e.nombre' => 'Pendiente',
                    'e.tipo' => 'empaquetado'
                ]);
                break;
            case 'listo':
                // Solo listos
                $query->andWhere([
                    'e.nombre' => 'Listo',
                    'e.tipo' => 'empaquetado'
                ]);
                break;
            case 'por_vencer':
                // Por vencer (7 días naturales)
                $hoy = new \DateTime();
                $limite = (clone $hoy)->modify('+7 day');
                $query->andWhere(['between', 'v.fecha_entrega', $hoy->format('Y-m-d'), $limite->format('Y-m-d')]);
                break;
        }
    }

    // Conteos de filtros - SIN DUPLICAR JOINS
    public static function getFiltrosConteos()
    {
        $total = Produccion::find()->count();

        // Urgentes que NO están listos (usando venta->entrega)
        $urgentes = Produccion::find()->alias('p')
            ->leftJoin('ventas v', 'p.venta_id = v.id')
            ->leftJoin('catalogos ent', 'v.entrega_id = ent.id')
            ->leftJoin('catalogos e', 'p.empaquetado_id = e.id')
            ->where(['ent.nombre' => 'Urgente'])
            ->andWhere(['OR', ['e.nombre' => null], ['!=', 'e.nombre', 'Listo']])
            ->count();

        // Todos los pendientes
        $pendientes = Produccion::find()->alias('p')
            ->leftJoin('catalogos e', 'p.empaquetado_id = e.id')
            ->where([
                'e.nombre' => 'Pendiente',
                'e.tipo' => 'empaquetado'
            ])
            ->count();

        // Todos los listos
        $listos = Produccion::find()->alias('p')
            ->leftJoin('catalogos e', 'p.empaquetado_id = e.id')
            ->where([
                'e.nombre' => 'Listo',
                'e.tipo' => 'empaquetado'
            ])
            ->count();

        // Por vencer (7 días naturales)
        $hoy = new \DateTime();
        $limite7 = (clone $hoy)->modify('+7 day');
        $porVencer = Produccion::find()->alias('p')
            ->leftJoin('ventas v', 'p.venta_id = v.id')
            ->where(['between', 'v.fecha_entrega', $hoy->format('Y-m-d'), $limite7->format('Y-m-d')])
            ->count();

        return [
            'total' => $total,
            'urgentes' => $urgentes,
            'pendientes' => $pendientes,
            'listos' => $listos,
            'por_vencer' => $porVencer,
        ];
    }

    
}