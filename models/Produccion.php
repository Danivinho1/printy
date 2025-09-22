<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "produccion".
 *
 * @property int $id
 * @property int $venta_id
 * @property int $diseno_id
 * @property int|null $tipo_letrero_id
 * @property string|null $nombre_letrero
 * @property int $disenador_id
 * @property int|null $unidades
 * @property string|null $chapetones
 * @property int|null $diseno_impresion
 * @property int|null $corte_id
 * @property int|null $fabricacion_id
 * @property string|null $fecha_confirmacion
 * @property int|null $dias_restantes
 * @property int|null $estatus_pago_id
 * @property int|null $envio_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Catalogos $corte
 * @property Usuario $disenador
 * @property Diseno $diseno
 * @property Catalogos $envio
 * @property Catalogos $estatusPago
 * @property Catalogos $fabricacion
 * @property Ventas $venta
 */
class Produccion extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'produccion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo_letrero_id', 'nombre_letrero', 'chapetones', 'diseno_impresion', 'corte_id', 'fabricacion_id', 'fecha_confirmacion', 'dias_restantes', 'estatus_pago_id', 'empaquetado_id'], 'default', 'value' => null],
            [['unidades'], 'default', 'value' => 1],
            [['venta_id', 'diseno_id', 'disenador_id'], 'required'],
            [['venta_id', 'diseno_id', 'tipo_letrero_id', 'disenador_id', 'unidades', 'diseno_impresion', 'corte_id', 'fabricacion_id', 'dias_restantes', 'estatus_pago_id', 'empaquetado_id'], 'integer'],
            [['fecha_confirmacion', 'created_at', 'updated_at'], 'safe'],
            [['nombre_letrero'], 'string', 'max' => 100],
            [['chapetones'], 'string', 'max' => 50],
            [['venta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ventas::class, 'targetAttribute' => ['venta_id' => 'id']],
            [['diseno_id'], 'exist', 'skipOnError' => true, 'targetClass' => Diseno::class, 'targetAttribute' => ['diseno_id' => 'id']],
            [['tipo_letrero_id'], 'exist', 'skipOnError' => true, 'targetClass' => Diseno::class, 'targetAttribute' => ['tipo_letrero_id' => 'id']],
            [['disenador_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['disenador_id' => 'id']],
            [['corte_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['corte_id' => 'id']],
            [['fabricacion_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['fabricacion_id' => 'id']],
            [['estatus_pago_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['estatus_pago_id' => 'id']],
            [['empaquetado_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['envio_id' => 'id']],
        // ... tus otras reglas
            [['envio_id'], 'default', 'value' => function() {
            return \app\models\Catalogos::find()
                ->where(['tipo' => 'envio', 'nombre' => 'Pendiente'])
                ->select('id')
                ->scalar();
        }],
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
            'diseno_id' => 'Diseno ID',
            'tipo_letrero_id' => 'Tipo Letrero',
            'nombre_letrero' => 'Nombre Letrero',
            'disenador_id' => 'Diseñador',
            'unidades' => 'Unidades',
            'chapetones' => 'Chapetones',
            'diseno_impresion' => 'Diseño Impresion',
            'corte_id' => 'Corte',
            'fabricacion_id' => 'Fabricacion',
            'fecha_confirmacion' => 'Fecha Confirmacion',
            'dias_restantes' => 'Dias Restantes',
            'estatus_pago_id' => 'Estatus Pago',
            'empaquetado_id' => 'Estatus',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Corte]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCorte()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'corte_id']);
    }

    /**
     * Gets query for [[Disenador]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenador()
    {
        return $this->hasOne(Usuario::class, ['id' => 'disenador_id']);
    }

    /**
     * Gets query for [[Diseno]].
     *
     * @return \yii\db\ActiveQuery
     */
    // Produccion.php
    public function getDiseno()
    {
        return $this->hasOne(Diseno::class, ['venta_id' => 'venta_id']);
    }


    /**
     * Gets query for [[Envio]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmpaquetado()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'empaquetado_id']);
    }

    /**
     * Gets query for [[EstatusPago]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstatusPago()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'estatus_pago_id']);
    }

    /**
     * Gets query for [[Fabricacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFabricacion()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'fabricacion_id']);
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

    public function getTipoLetrero()
    {
    return $this->hasOne(Catalogos::class, ['id' => 'tipo_letrero_id']);
    }

    public function behaviors()
    {
    return [
        \yii\behaviors\TimestampBehavior::class,
    ];
    }

    public function getChapetonesFromVentas()
{
    if (!$this->venta) return null;
    
    $extrasNombres = $this->venta->getExtrasNombres();
    if ($extrasNombres === 'Ninguno') return null;
    
    $extrasArray = explode(', ', $extrasNombres);
    $chapetones = [];
    
    foreach ($extrasArray as $extra) {
        if (stripos($extra, 'chapetones') !== false) {
            $chapetones[] = $extra;
        }
    }
    
    return !empty($chapetones) ? implode(', ', $chapetones) : null;
}

public function afterSave($insert, $changedAttributes)
{
    parent::afterSave($insert, $changedAttributes);

    // ID de "Listo" desde catalogos
    $listoPId = Catalogos::find()
        ->select('id')
        ->where(['nombre' => 'Listo', 'tipo' => 'empaquetado'])
        ->scalar();

    $pendienteId = Catalogos::find()
        ->select('id')
        ->where(['nombre' => 'Pendiente', 'tipo' => 'empaquetado'])
        ->scalar();

    // Si todos los checks están listos
    if ($this->diseno_impresion == 1 && $this->corte_listo == 1 && $this->fabricacion_listo == 1) {
        if ($this->empaquetado_id != $listoPId) {
            $this->updateAttributes(['empaquetado_id' => $listoPId]);
        }
    } else {
        if ($this->empaquetado_id != $pendienteId) {
            $this->updateAttributes(['empaquetado_id' => $pendienteId]);
        }
    }
}


public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {
        // Si es un nuevo registro y no tiene empaquetado_id, asignar "Pendiente" de tipo "empaquetado"
        if ($insert && empty($this->empaquetado_id)) {
            $pendiente = \app\models\Catalogos::find()
                ->where([
                    'nombre' => 'Pendiente',
                    'tipo' => 'empaquetado'  // Especificar el tipo
                ])
                ->one();
                
            if ($pendiente) {
                $this->empaquetado_id = $pendiente->id;
            }
        }
        return true;
    }
    return false;
}

// Y también actualiza tu vista para que no muestre "Pendiente" cuando sea null:


}
