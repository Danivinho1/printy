<?php

namespace app\models;

use app\models\VentasAdicionales;
use app\models\VentasExtras;
use app\models\Catalogos;
use app\models\Usuario;
use app\models\Diseno;
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
 * @property float|null $restante (GENERATED column en MySQL)
 * @property string|null $fecha_compra
 * @property int|null $created_by
 * @property string $created_at
 * @property string $updated_at
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
            [['tipo_letrero_id','entrega_id', 'fecha_entrega', 'medio_id', 'telefono','ubicacion', 'campaña_id', 'asesor_id', 'created_by'], 'default', 'value' => null],
            [['unidades'], 'default', 'value' => 1],
            [['anticipo'], 'default', 'value' => 0.00],
            [['tipo_letrero_id', 'nombre_letrero'], 'required'],
            [['tipo_letrero_id','entrega_id', 'medio_id', 'campaña_id', 'asesor_id', 'unidades', 'created_by'], 'integer'],
            [['fecha_entrega', 'fecha_compra', 'created_at', 'updated_at'], 'safe'],
            [['extra_precio', 'precio_total', 'anticipo'], 'number'],
            [['restante'], 'safe'], // ✅ solo lectura
            [['nombre_letrero', 'ubicacion'], 'string', 'max' => 100],
            [['telefono'], 'string', 'max' => 20],
            [['adicionales_ids', 'extras_ids'], 'safe'],
            [['entrega_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['entrega_id' => 'id']],
            [['tipo_letrero_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['tipo_letrero_id' => 'id']],
            [['medio_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['medio_id' => 'id']],
            [['campaña_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['campaña_id' => 'id']],
            [['asesor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['asesor_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::class, 'targetAttribute' => ['created_by' => 'id']],
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

    // ======================
    // Relaciones
    // ======================
    public function getTipoLetrero() { return $this->hasOne(Catalogos::class, ['id' => 'tipo_letrero_id']); }
    public function getAsesor() { return $this->hasOne(Catalogos::class, ['id' => 'asesor_id']); }
    public function getCampaña() { return $this->hasOne(Catalogos::class, ['id' => 'campaña_id']); }
    public function getCreatedBy() { return $this->hasOne(Usuario::class, ['id' => 'created_by']); }
    public function getEntrega() { return $this->hasOne(Catalogos::class, ['id' => 'entrega_id']); }
    public function getMedio() { return $this->hasOne(Catalogos::class, ['id' => 'medio_id']); }
    public function getAdicionales() { return $this->hasMany(Catalogos::class, ['id' => 'adicional_id'])->via('ventasAdicionales'); }
    public function getExtras() { return $this->hasMany(Catalogos::class, ['id' => 'extra_id'])->via('ventasExtras'); }

    public function getVentasAdicionales() { return $this->hasMany(VentasAdicionales::class, ['venta_id' => 'id']); }
    public function getVentasExtras() { return $this->hasMany(VentasExtras::class, ['venta_id' => 'id']); }

    // 🔹 Relación con Diseno
    public function getDiseno()
    {
        return $this->hasOne(Diseno::class, ['venta_id' => 'id']);
    }

    // ======================
    // Métodos especiales
    // ======================

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

    // ======================
    // Hooks
    // ======================

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // ⚡ Evitar que Yii intente guardar 'restante' (columna generada en MySQL)
        unset($this->restante);

        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->adicionales_ids = $this->getAdicionalesIds();
        $this->extras_ids = $this->getExtrasIds();
    }
}
