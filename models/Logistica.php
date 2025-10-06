<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "logistica".
 *
 * @property int $id
 * @property int $venta_id
 * @property int|null $tipo_letrero_id
 * @property string|null $nombre_letrero
 * @property string|null $chapetones
 * @property int|null $num_chapetones
 * @property string|null $telefono
 * @property float|null $total
 * @property float|null $anticipo
 * @property float|null $restante
 * @property int|null $estatus_pago_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Catalogos $estatusPago
 * @property Ventas $venta
 */
class Logistica extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logistica';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo_letrero_id', 'nombre_letrero', 'telefono', 'estatus_pago_id', 'estatus_envio_id',], 'default', 'value' => null],
            [['total', 'anticipo', 'restante'], 'default', 'value' => 0],
            [['venta_id'], 'required'],
            [['venta_id', 'tipo_letrero_id', 'estatus_pago_id', 'estatus_envio_id', 'revision_calidad'], 'integer'],
            [['total', 'anticipo', 'restante'], 'number'],
            [['created_at', 'updated_at', 'fecha_pago_liquidado'], 'safe'],
            [[ 'nombre_letrero'], 'string', 'max' => 100],
            [['telefono'], 'string', 'max' => 20],
            [['venta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ventas::class, 'targetAttribute' => ['venta_id' => 'id']],
            [['estatus_pago_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['estatus_pago_id' => 'id']],
            [['tipo_letrero_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['tipo_letrero_id' => 'id']],
             [['estatus_envio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['estatus_envio_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'venta_id' => 'Venta ID',
            'tipo_letrero_id' => 'Tipo Letrero',
            'nombre_letrero' => 'Nombre Letrero',
            'chapetones' => 'Chapetones',
            'num_chapetones' => 'Num Chapetones',
            'telefono' => 'Telefono',
            'total' => 'Total',
            'anticipo' => 'Anticipo',
            'restante' => 'Restante',
            'estatus_pago_id' => 'Estatus Pago',
            'estatus_envio_id' => 'Estatus Envio',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[EstatusPago]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstatusPago()
{
    return $this->hasOne(Catalogos::class, ['id' => 'estatus_pago_id'])
                ->alias('estatusPago')
                ->andWhere(['estatusPago.tipo' => 'estatus_pago']); // <--- especificar alias
}


    public function getEnvio()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'estatus_envio_id']);
    }

    /**
     * Gets query for [[Venta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVenta()
    {
        return $this->hasOne(Ventas::class, ['id' => 'venta_id']);
    }

    public function getDiseno()
   {
    return $this->hasOne(Diseno::class, ['venta_id' => 'venta_id']);
    
    }
    public function getProduccion()
    {
        return $this->hasOne(Produccion::class, ['venta_id' => 'venta_id'])
            ->via('venta');
    }

    public function getTipoLetrero()
    {
    return $this->hasOne(Catalogos::class, ['id' => 'tipo_letrero_id']);
    }

    public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {

        // Si el estatus cambió a Liquidado y aún no tiene fecha
        if ($this->isAttributeChanged('estatus_pago_id')) {
            if ($this->estatusPago && $this->estatusPago->nombre === 'Liquidado' && !$this->fecha_pago_liquidado) {
                $this->fecha_pago_liquidado = date('Y-m-d H:i:s');
            }
        }

        return true;
    }
    return false;
}

    
   private $_sincronizandoVentas = false;

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Evitar loop infinito
        if ($this->_sincronizandoVentas) {
            return;
        }

        // Revisar si alguno de los campos de precios cambió
        $camposPrecio = ['total', 'anticipo', 'restante'];
        $cambioPrecio = false;
        foreach ($camposPrecio as $campo) {
            if (array_key_exists($campo, $changedAttributes)) {
                $cambioPrecio = true;
                break;
            }
        }

        if ($cambioPrecio) {
            $this->_sincronizandoVentas = true;

            try {
                // Buscar el registro correspondiente en Ventas
                $venta = \app\models\Ventas::findOne($this->id);
                if ($venta) {
                    // Mapear campos: Logistica.total -> Ventas.precio_total
                    $venta->precio_total = $this->total ?? 0;
                    $venta->anticipo = $this->anticipo ?? 0;
                    $venta->restante = $this->restante ?? 0;

                    // Guardar sin validación para evitar problemas con otros campos obligatorios
                    $venta->save(false, ['precio_total', 'anticipo', 'restante']);
                }
            } catch (\Exception $e) {
                Yii::error("Error sincronizando Logistica -> Ventas: ".$e->getMessage(), __METHOD__);
            }

            $this->_sincronizandoVentas = false;
        }
    }


}
