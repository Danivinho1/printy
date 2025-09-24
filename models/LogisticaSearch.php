<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class LogisticaSearch extends Logistica
{
    public $filtro_pago;   // nombres: 'Liquidado', 'Por Liquidar', 'Pendiente'
    public $filtro_envio;  // nombres: 'Enviado', 'Pendiente'
    public $filtro_fecha;  // 'urgente' o 'retrasado'

    public function rules()
    {
        return [
            [['id', 'venta_id', 'tipo_letrero_id', 'estatus_pago_id', 'estatus_envio_id'], 'integer'],
            [['nombre_letrero', 'telefono', 'created_at', 'updated_at', 'filtro_pago', 'filtro_envio', 'filtro_fecha'], 'safe'],
            [['total', 'anticipo', 'restante'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Logistica::find()->alias('l')
            ->leftJoin('ventas v', 'l.venta_id = v.id')
            ->leftJoin('catalogos sp', 'l.estatus_pago_id = sp.id AND sp.tipo = "estatus_pago"')
            ->leftJoin('catalogos se', 'l.estatus_envio_id = se.id AND se.tipo = "envio"');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Aplicar filtro de pago si está definido
        if (!empty($this->filtro_pago)) {
            $query->andWhere(['sp.nombre' => $this->filtro_pago]);
        }

        // Aplicar filtro de envío si está definido
        if (!empty($this->filtro_envio)) {
            $query->andWhere(['se.nombre' => $this->filtro_envio]);
        }

        // Aplicar filtro de fecha si está definido
        if (!empty($this->filtro_fecha)) {
            $hoy = new \DateTime();
            $hoyStr = $hoy->format('Y-m-d');
            $proxUrgente = (clone $hoy)->modify('+2 days')->format('Y-m-d');

            if ($this->filtro_fecha === 'urgente') {
                $query->andWhere(['between', 'v.fecha_entrega', $hoyStr, $proxUrgente]);
            } elseif ($this->filtro_fecha === 'retrasado') {
                $query->andWhere(['<', 'v.fecha_entrega', $hoyStr]);
            }
        }

        // Filtros básicos
        $query->andFilterWhere([
            'l.id' => $this->id,
            'l.tipo_letrero_id' => $this->tipo_letrero_id,
        ]);

        $query->andFilterWhere(['like', 'l.nombre_letrero', $this->nombre_letrero])
              ->andFilterWhere(['like', 'l.telefono', $this->telefono]);

        $query->with(['venta.extras', 'estatusPago', 'envio', 'tipoLetrero']);

        // Orden ascendente estilo DisenoSearch
        $query->addOrderBy(new Expression("
            CASE
                WHEN v.fecha_entrega IS NOT NULL THEN 1
                ELSE 2
            END ASC,
            CASE
                WHEN v.fecha_entrega IS NOT NULL THEN v.fecha_entrega
                ELSE NOW()
            END ASC
        "));
        $query->addOrderBy(new Expression('l.id ASC, l.created_at ASC'));

        return $dataProvider;
    }

    public static function getFiltrosConteos()
    {
        $hoy = new \DateTime();
        $hoyStr = $hoy->format('Y-m-d');
        $proxUrgente = (clone $hoy)->modify('+2 days')->format('Y-m-d');

        $total = Logistica::find()->count();

        $liquidados = Logistica::find()
            ->alias('l')
            ->leftJoin('catalogos sp', 'l.estatus_pago_id = sp.id AND sp.tipo = "estatus_pago"')
            ->where(['sp.nombre' => 'Liquidado'])
            ->count();

        $porLiquidar = Logistica::find()
            ->alias('l')
            ->leftJoin('catalogos sp', 'l.estatus_pago_id = sp.id AND sp.tipo = "estatus_pago"')
            ->where(['sp.nombre' => 'Por Liquidar'])
            ->count();

        $enviados = Logistica::find()
            ->alias('l')
            ->leftJoin('catalogos se', 'l.estatus_envio_id = se.id AND se.tipo = "envio"')
            ->where(['se.nombre' => 'Enviado'])
            ->count();

        $enviosPendientes = Logistica::find()
            ->alias('l')
            ->leftJoin('catalogos se', 'l.estatus_envio_id = se.id AND se.tipo = "envio"')
            ->where(['se.nombre' => 'Pendiente'])
            ->count();

        $urgentes = Logistica::find()->alias('l')
            ->leftJoin('ventas v', 'l.venta_id = v.id')
            ->where(['between', 'v.fecha_entrega', $hoyStr, $proxUrgente])
            ->count();

        $retrasados = Logistica::find()->alias('l')
            ->leftJoin('ventas v', 'l.venta_id = v.id')
            ->where(['<', 'v.fecha_entrega', $hoyStr])
            ->count();

        return [
            'total' => $total,
            'liquidados' => $liquidados,
            'por_liquidar' => $porLiquidar,
            'enviados' => $enviados,
            'envios_pendientes' => $enviosPendientes,
            'urgentes' => $urgentes,
            'retrasados' => $retrasados,
        ];
    }

    /**
     * Método de debugging para verificar los datos de catálogos
     */
    public static function debugCatalogos()
    {
        echo "<pre>=== DEBUG COMPLETO DE CATÁLOGOS ===\n";
        
        // Para verificar los valores exactos en la tabla catalogos
        $estatusPago = \app\models\Catalogos::find()
            ->where(['tipo' => 'estatus-pago'])
            ->all();
        
        $estatusEnvio = \app\models\Catalogos::find()
            ->where(['tipo' => 'estatus-envio'])
            ->all();

        echo "=== ESTATUS PAGO ===\n";
        foreach ($estatusPago as $pago) {
            echo "ID: {$pago->id}, Nombre: '{$pago->nombre}' (longitud: " . strlen($pago->nombre) . ")\n";
            // Mostrar caracteres hexadecimales para detectar espacios ocultos
            echo "Hex: " . bin2hex($pago->nombre) . "\n";
        }

        echo "\n=== ESTATUS ENVÍO ===\n";
        foreach ($estatusEnvio as $envio) {
            echo "ID: {$envio->id}, Nombre: '{$envio->nombre}' (longitud: " . strlen($envio->nombre) . ")\n";
            echo "Hex: " . bin2hex($envio->nombre) . "\n";
        }
        
        // Verificar estructura de tabla logistica
        echo "\n=== MUESTRA DE REGISTROS LOGÍSTICA ===\n";
        $logisticaSample = \app\models\Logistica::find()
            ->alias('l')
            ->leftJoin('catalogos sp', 'l.estatus_pago_id = sp.id AND sp.tipo = "estatus-pago"')
            ->leftJoin('catalogos se', 'l.estatus_envio_id = se.id AND se.tipo = "estatus-envio"')
            ->select(['l.id', 'l.estatus_pago_id', 'l.estatus_envio_id', 'sp.nombre as pago_nombre', 'se.nombre as envio_nombre'])
            ->limit(5)
            ->asArray()
            ->all();
            
        foreach ($logisticaSample as $sample) {
            echo "Logística ID: {$sample['id']}, Pago ID: {$sample['estatus_pago_id']}, Envío ID: {$sample['estatus_envio_id']}\n";
            echo "  -> Pago Nombre: '{$sample['pago_nombre']}', Envío Nombre: '{$sample['envio_nombre']}'\n";
        }
        
        echo "</pre>";
    }
}
