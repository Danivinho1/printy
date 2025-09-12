<?php

namespace app\models;

use app\models\VentasAdicionales;
use app\models\VentasExtras;
use app\models\Catalogos;
use app\models\Usuarios;
use Yii;

/**
 * This is the model class for table "ventas".
 *
 * @property int $id
 * @property int $tipo_letrero_id
 * @property string $nombre_letrero
 * @property int|null $entrega_id
 * @property string|null $fecha_entrega
 * @property int|null $medio_id
 * @property string|null $telefono
 * @property int|null $campaña_id
 * @property int|null $asesor_id
 * @property int|null $unidades
 * @property float|null $extra_precio
 * @property float|null $precio_total
 * @property float|null $anticipo
 * @property float|null $restante
 * @property string|null $fecha_compra
 * @property int|null $created_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Catalogos $asesor
 * @property Catalogos $campaña
 * @property Usuarios $createdBy
 * @property Diseno[] $disenos
 * @property Catalogos $entrega
 * @property Logistica[] $logisticas
 * @property Catalogos $medio
 * @property Produccion[] $produccions
 * @property Catalogos $tipoLetrero
 * @property VentasAdicionales[] $ventasAdicionales
 * @property VentasExtras[] $ventasExtras
 * @property Catalogos[] $adicionales
 * @property Catalogos[] $extras
 */
class Ventas extends \yii\db\ActiveRecord
{
    // Propiedades virtuales para el formulario
    public $adicionales_ids = [];
    public $extras_ids = [];
    public $skipAfterSave = false;

    public static function tableName()
    {
        return 'ventas';
    }

    public function rules()
    {
        return [
            [['tipo_letrero_id','entrega_id', 'fecha_entrega', 'medio_id', 'telefono','ubicacion', 'campaña_id', 'asesor_id', 'restante', 'created_by'], 'default', 'value' => null],
            [['unidades'], 'default', 'value' => 1],
            [['anticipo'], 'default', 'value' => 0.00],
            [['tipo_letrero_id', 'nombre_letrero'], 'required'],
            [['tipo_letrero_id','entrega_id', 'medio_id', 'campaña_id', 'asesor_id', 'unidades', 'created_by'], 'integer'],
            [['fecha_entrega', 'fecha_compra', 'created_at', 'updated_at'], 'safe'],
            [['extra_precio', 'precio_total', 'anticipo', 'restante'], 'number'],
            [['nombre_letrero', 'ubicacion'], 'string', 'max' => 100],
            [['telefono'], 'string', 'max' => 20],
            [['adicionales_ids', 'extras_ids'], 'safe'],
            [['entrega_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['entrega_id' => 'id']],
            [['tipo_letrero_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['tipo_letrero_id' => 'id']],
            [['medio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['medio_id' => 'id']],
            [['campaña_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['campaña_id' => 'id']],
            [['asesor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['asesor_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo_letrero_id' => 'Tipo Letrero',
            'nombre_letrero' => 'Nombre Letrero',
            'entrega_id' => 'Entrega',
            'fecha_entrega' => 'Fecha Entrega',
            'adicionales_ids' => 'Adicionales',
            'extras_ids' => 'Extras',
            'medio_id' => 'Medio',
            'telefono' => 'Telefono',
            'campaña_id' => 'Campaña',
            'asesor_id' => 'Asesor',
            'ubicacion' => 'Ubicacion',
            'unidades' => 'Unidades',
            'extra_precio' => 'Extra Precio',
            'precio_total' => 'Precio Total',
            'anticipo' => 'Anticipo',
            'restante' => 'Restante',
            'fecha_compra' => 'Fecha Compra',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    // --- RELACIONES ---
    public function getTipoLetrero() { return $this->hasOne(Catalogos::class, ['id' => 'tipo_letrero_id']); }
    public function getAsesor() { return $this->hasOne(Catalogos::class, ['id' => 'asesor_id']); }
    public function getCampaña() { return $this->hasOne(Catalogos::class, ['id' => 'campaña_id']); }
    public function getCreatedBy() { return $this->hasOne(Usuarios::class, ['id' => 'created_by']); }
    public function getDisenos() { return $this->hasMany(Diseno::class, ['venta_id' => 'id']); }
    public function getEntrega() { return $this->hasOne(Catalogos::class, ['id' => 'entrega_id']); }
    public function getLogisticas() { return $this->hasMany(Logistica::class, ['venta_id' => 'id']); }
    public function getMedio() { return $this->hasOne(Catalogos::class, ['id' => 'medio_id']); }
    public function getProduccions() { return $this->hasMany(Produccion::class, ['venta_id' => 'id']); }
    // --- MANY-TO-MANY ---
    public function getVentasAdicionales() { return $this->hasMany(VentasAdicionales::class, ['venta_id' => 'id']); }
    public function getAdicionales() { return $this->hasMany(Catalogos::class, ['id' => 'adicional_id'])->via('ventasAdicionales'); }
    public function getVentasExtras() { return $this->hasMany(VentasExtras::class, ['venta_id' => 'id']); }
    public function getExtras() { return $this->hasMany(Catalogos::class, ['id' => 'extra_id'])->via('ventasExtras'); }

    // --- MÉTODOS PARA OBTENER IDS ---
    public function getAdicionalesIds()
    {
        if ($this->isNewRecord) return [];
        return VentasAdicionales::find()->where(['venta_id'=>$this->id])->select('adicional_id')->column();
    }

    public function getExtrasIds()
    {
        if ($this->isNewRecord) return [];
        return VentasExtras::find()->where(['venta_id'=>$this->id])->select('extra_id')->column();
    }

    // --- MÉTODOS PARA MOSTRAR NOMBRES ---
    public function getAdicionalesNombres()
    {
        if ($this->isNewRecord) return 'Ninguno';
        $nombres = Catalogos::find()
            ->innerJoin('ventas_adicionales', 'catalogos.id = ventas_adicionales.adicional_id')
            ->where(['ventas_adicionales.venta_id'=>$this->id])
            ->select('catalogos.nombre')
            ->column();
        return !empty($nombres) ? implode(', ', $nombres) : 'Ninguno';
    }

    public function getExtrasNombres()
    {
        if ($this->isNewRecord) return 'Ninguno';
        $nombres = Catalogos::find()
            ->innerJoin('ventas_extras', 'catalogos.id = ventas_extras.extra_id')
            ->where(['ventas_extras.venta_id'=>$this->id])
            ->select('catalogos.nombre')
            ->column();
        return !empty($nombres) ? implode(', ', $nombres) : 'Ninguno';
    }

    public function beforeSave($insert)
{
    if (!parent::beforeSave($insert)) {
        return false;
    }

    // Calcula los totales si no están definidos
    $this->precio_total = $this->precio_total ?? 0;
    $this->anticipo = $this->anticipo ?? 0;
    $this->restante = $this->restante ?? 0;

    return true;
}
     private $_sincronizandoPrecio = false;

    public function afterSave($insert, $changedAttributes)
{
    parent::afterSave($insert, $changedAttributes);

    if ($this->skipAfterSave) {
        return;
    }

    /** =====================
     *  GUARDAR EN DISEÑO
     *  ===================== */
    $diseno = \app\models\Diseno::findOne(['venta_id' => $this->id]);
    if (!$diseno) {
        $diseno = new \app\models\Diseno();
        $diseno->venta_id = $this->id;
    }
    $diseno->tipo_letrero_id = $this->tipo_letrero_id;
    $diseno->nombre_letrero = $this->nombre_letrero;
    $diseno->telefono = $this->telefono;
    $diseno->entrega_id = $this->entrega_id;
    $diseno->extra_precio = $this->extra_precio;
    $diseno->save(false);

    /** =====================
     *  ESTATUS DE PAGO
     *  ===================== */
    $estatus_pago_id = $this->restante > 0
        ? \app\models\Catalogos::find()->where(['tipo'=>'estatus_pago','nombre'=>'Por liquidar'])->select('id')->scalar()
        : \app\models\Catalogos::find()->where(['tipo'=>'estatus_pago','nombre'=>'Liquidado'])->select('id')->scalar();

    /** =====================
     *  GUARDAR EN PRODUCCIÓN
     *  ===================== */
    $produccion = \app\models\Produccion::findOne(['venta_id' => $this->id]);
    if (!$produccion) {
        $produccion = new \app\models\Produccion();
        $produccion->venta_id = $this->id;
    }
    $produccion->tipo_letrero_id = $this->tipo_letrero_id;
    $produccion->nombre_letrero = $this->nombre_letrero;
    $produccion->unidades = $this->unidades;
    $produccion->diseno_id = $diseno->id;
    $produccion->estatus_pago_id = $estatus_pago_id;
    $produccion->save(false);

    /** =====================
     *  GUARDAR VENTAS_ADICIONALES
     *  ===================== */
    \app\models\VentasAdicionales::deleteAll(['venta_id' => $this->id]);
    if (!empty($this->adicionales_ids)) {
        foreach ($this->adicionales_ids as $adicionalId) {
            $va = new \app\models\VentasAdicionales();
            $va->venta_id = $this->id;
            $va->adicional_id = $adicionalId;
            $va->save(false);
        }
    }

    /** =====================
     *  GUARDAR VENTAS_EXTRAS
     *  ===================== */
    \app\models\VentasExtras::deleteAll(['venta_id' => $this->id]);
    
    // Calcular chapetones mientras guardamos los extras
    $chapetonesExtras = [];
    if (!empty($this->extras_ids)) {
        foreach ($this->extras_ids as $extraId) {
            $ve = new \app\models\VentasExtras();
            $ve->venta_id = $this->id;
            $ve->extra_id = $extraId;
            $ve->save(false);
            
            // Obtener el nombre del extra para verificar si es chapetón
            $extraNombre = \app\models\Catalogos::find()
                ->where(['id' => $extraId])
                ->select('nombre')
                ->scalar();
            
            if ($extraNombre && stripos($extraNombre, 'chapetones') !== false) {
                $chapetonesExtras[] = $extraNombre;
            }
        }
    }
    /** =====================
     *  GUARDAR LOGÍSTICA
     *  ===================== */
    $logistica = \app\models\Logistica::findOne(['venta_id' => $this->id]) ?: new \app\models\Logistica();
    $logistica->venta_id = $this->id;
    $logistica->tipo_letrero_id = $this->tipo_letrero_id;
    $logistica->nombre_letrero = $this->nombre_letrero;
    $logistica->total = $this->precio_total;
    $logistica->anticipo = $this->anticipo;
    $logistica->restante = $this->restante;
    $logistica->telefono = $this->telefono;
    $logistica->estatus_pago_id = $estatus_pago_id;
    $logistica->save(false);

    if ($this->_sincronizandoPrecio) {
        return; // Evita bucles
    }

    // Solo sincronizar si cambió alguno de estos campos
    if (isset($changedAttributes['precio_total']) || isset($changedAttributes['anticipo'])) {
        $logistica = \app\models\Logistica::findOne($this->id); // ID igual
        if ($logistica) {
            $logistica->_sincronizandoPrecio = true;
            $logistica->total = $this->precio_total;
            $logistica->anticipo = $this->anticipo;
            $logistica->restante = max(0, $this->precio_total - $this->anticipo);
            $logistica->save(false);
            $logistica->_sincronizandoPrecio = false;
        }
    }
}
  public function afterFind()
{
    parent::afterFind();
    $this->adicionales_ids = $this->getAdicionalesIds();
    $this->extras_ids = $this->getExtrasIds();
}

}

