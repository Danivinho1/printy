<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "diseno".
 *
 * @property int $id
 * @property int $venta_id
 * @property string|null $tipo_letrero
 * @property string|null $nombre_letrero
 * @property string|null $telefono
 * @property int|null $entrega_id
 * @property string|null $fecha_confirmacion
 * @property int $responsable_id
 * @property float|null $extra_precio
 * @property int|null $contacto_cliente_id
 * @property int|null $especificaciones_id
 * @property int|null $vectorizado_id
 * @property int|null $enviado_corte_id
 * @property int|null $avance
 * @property int|null $estatus_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Catalogos $contactoCliente
 * @property Catalogos $entrega
 * @property Catalogos $enviadoCorte
 * @property Catalogos $especificaciones
 * @property Catalogos $estatus
 * @property Produccion[] $produccions
 * @property Usuario $responsable
 * @property Catalogos $vectorizado
 * @property Ventas $venta
 */
class Diseno extends \yii\db\ActiveRecord
{
    
     public $extras_ids = [];
    public $adicionales_ids = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'diseno';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo_letrero_id', 'nombre_letrero', 'telefono', 'entrega_id', 'fecha_confirmacion', 'contacto_cliente_id', 'especificaciones_id', 'vectorizado_id', 'enviado_corte_id', 'estatus_id'], 'default', 'value' => null],
            [['contacto_cliente_id', 'especificaciones_id', 'vectorizado_id', 'enviado_corte_id', 'estatus_id'], 'safe'],
            [['extra_precio'], 'default', 'value' => 0.00],
            [['avance'], 'default', 'value' => 0],
            [['venta_id', ], 'required'],
            [['venta_id','responsable_id','tipo_letrero_id', 'entrega_id', 'responsable_id', 'contacto_cliente_id', 'especificaciones_id', 'vectorizado_id', 'enviado_corte_id', 'avance', 'estatus_id'], 'integer'],
            [['fecha_confirmacion', 'created_at', 'updated_at'], 'safe'],
            [['extra_precio'], 'number'],
            [['nombre_letrero'], 'string', 'max' => 100],
            [['telefono'], 'string', 'max' => 20],
            [['venta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ventas::class, 'targetAttribute' => ['venta_id' => 'id']],
            [['entrega_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['entrega_id' => 'id']],
            [['tipo_letrero_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['tipo_letrero_id' => 'id']],
            [['contacto_cliente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['contacto_cliente_id' => 'id']],
            [['especificaciones_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['especificaciones_id' => 'id']],
            [['vectorizado_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['vectorizado_id' => 'id']],
            [['enviado_corte_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['enviado_corte_id' => 'id']],
            [['estatus_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['estatus_id' => 'id']],
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
            'telefono' => 'Telefono',
            'entrega_id' => 'Entrega',
            'fecha_confirmacion' => 'Fecha Confirmacion',
            'responsable_id' => 'Responsable',
            'extra_precio' => 'Extra Precio',
            'contacto_cliente_id' => 'Contacto Cliente',
            'especificaciones_id' => 'Especificaciones (Produccion)',
            'vectorizado_id' => 'Vectorizado',
            'enviado_corte_id' => 'Enviado Corte',
            'avance' => 'Avance',
            'estatus_id' => 'Estatus',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[ContactoCliente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContactoCliente()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'contacto_cliente_id']);
    }
    
    /**
     * Gets query for [[Entrega]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEntrega()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'entrega_id']);
    }

    /**
     * Gets query for [[EnviadoCorte]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEnviadoCorte()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'enviado_corte_id']);
    }

    /**
     * Gets query for [[Especificaciones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEspecificaciones()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'especificaciones_id']);
    }

    /**
     * Gets query for [[Estatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstatus()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'estatus_id']);
    }

    /**
     * Gets query for [[Produccions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduccions()
    {
        return $this->hasMany(Produccion::class, ['diseno_id' => 'id']);
    }

    /**
     * Gets query for [[Responsable]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResponsable()
    {
        return $this->hasOne(Usuario::class, ['id' => 'responsable_id']);
    }

    /**
     * Gets query for [[Vectorizado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVectorizado()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'vectorizado_id']);
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
    
    public function getExtrasNombres()
{
    if (!$this->venta) {
        return 'Ninguno';
    }
    return $this->venta->getExtrasNombres();
}

public function getAdicionalesNombres()
{
    if (!$this->venta) {
        return 'Ninguno';
    }
    return $this->venta->getAdicionalesNombres();
}

public function afterFind()
    {
        parent::afterFind();
        if ($this->venta) {
            $this->extras_ids = $this->venta->getExtrasIds();
            $this->adicionales_ids = $this->venta->getAdicionalesIds();
        }
    }
public function beforeSave($insert)
{
    if (!parent::beforeSave($insert)) {
        return false;
    }

    // Solo al crear (insertar)
    if ($insert && empty($this->estatus_id)) {
        // Buscar el ID de "Pendiente" en catalogos
        $estatusPendiente = \app\models\Catalogos::find()
            ->where(['tipo' => 'estatus', 'nombre' => 'Pendiente'])
            ->select('id')
            ->scalar();

        if ($estatusPendiente) {
            $this->estatus_id = $estatusPendiente;
        }
    }

    return true;
}


    public function afterSave($insert, $changedAttributes)
{
    parent::afterSave($insert, $changedAttributes);

    // 🔹 Actualizar Producción si la fecha o diseñador cambian
    $produccion = Produccion::findOne(['venta_id' => $this->venta_id]);
    if ($produccion && $this->venta) {
        $produccion->fecha_confirmacion = $this->fecha_confirmacion;
        $produccion->disenador_id = $this->responsable_id;

        // Recorremos los extras actuales de la venta
        $chapetonesExtras = [];
        foreach ($this->venta->extras as $extra) {
            if (stripos($extra->nombre, 'chapetones') !== false) {
                $chapetonesExtras[] = $extra->nombre;
            }
        }

        $produccion->chapetones = !empty($chapetonesExtras) ? implode(', ', $chapetonesExtras) : null;
        $produccion->save(false);
    }

    // 🔹 Sincronizar extra_precio en ventas
    if (array_key_exists('extra_precio', $changedAttributes)) {
        Yii::$app->db->createCommand()
            ->update('ventas', ['extra_precio' => $this->extra_precio], ['id' => $this->venta_id])
            ->execute();
    }

    // 🔹 Calcular fecha_entrega solo si está vacía y hay fecha_confirmacion
    if (!empty($this->fecha_confirmacion) && $this->venta && empty($this->venta->fecha_entrega)) {
        $fechaEntrega = $this->sumarDiasHabiles($this->fecha_confirmacion, 8);

        $this->venta->fecha_entrega = $fechaEntrega;
        $this->venta->save(false); // false para evitar validaciones extra
    }
}

/**
 * Suma días hábiles (omite sábados, domingos y festivos de la tabla feriados)
 */
private function sumarDiasHabiles($fechaInicio, $dias)
{
    $fecha = new \DateTime($fechaInicio);
    $agregados = 0;

    // Obtener feriados desde la BD
    $diasFestivos = \app\models\Feriados::find()
        ->select('fecha')
        ->column();

    while ($agregados < $dias) {
        $fecha->modify('+1 day');
        $diaSemana = $fecha->format('N'); // 1 = lunes, 7 = domingo
        $formatoFecha = $fecha->format('Y-m-d');

        if ($diaSemana < 6 && !in_array($formatoFecha, $diasFestivos)) {
            $agregados++;
        }
    }

    return $fecha->format('Y-m-d');
}



}

