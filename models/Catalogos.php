<?php

namespace app\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "catalogos".
 *
 * @property int $id
 * @property string $tipo
 * @property string $nombre
 * @property int|null $orden
 * @property int|null $activo
 *
 * @property Diseno[] $disenos
 * @property Diseno[] $disenos0
 * @property Diseno[] $disenos1
 * @property Diseno[] $disenos2
 * @property Diseno[] $disenos3
 * @property Diseno[] $disenos4
 * @property Diseno[] $disenos5
 * @property Logistica[] $logisticas
 * @property Produccion[] $produccions
 * @property Produccion[] $produccions0
 * @property Produccion[] $produccions1
 * @property Produccion[] $produccions2
 * @property Ventas[] $ventas
 * @property Ventas[] $ventas0
 * @property Ventas[] $ventas1
 * @property Ventas[] $ventas2
 * @property Ventas[] $ventas3
 * @property Ventas[] $ventas4
 */
class Catalogos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalogos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orden'], 'default', 'value' => 0],
            [['activo'], 'default', 'value' => 1],
            [['tipo', 'nombre'], 'required'],
            [['orden', 'activo'], 'integer'],
            [['tipo'], 'string', 'max' => 50],
            [['nombre'], 'string', 'max' => 100],
            [['tipo', 'nombre'], 'unique', 'targetAttribute' => ['tipo', 'nombre']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo' => 'Tipo',
            'nombre' => 'Nombre',
            'orden' => 'Orden',
            'activo' => 'Activo',
        ];
    }

    /**
     * Gets query for [[Disenos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenos()
    {
        return $this->hasMany(Diseno::class, ['entrega_id' => 'id']);
    }


    /**
     * Gets query for [[Disenos1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenos1()
    {
        return $this->hasMany(Diseno::class, ['contacto_cliente_id' => 'id']);
    }

    /**
     * Gets query for [[Disenos2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenos2()
    {
        return $this->hasMany(Diseno::class, ['especificaciones_id' => 'id']);
    }

    /**
     * Gets query for [[Disenos3]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenos3()
    {
        return $this->hasMany(Diseno::class, ['vectorizado_id' => 'id']);
    }

    /**
     * Gets query for [[Disenos4]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenos4()
    {
        return $this->hasMany(Diseno::class, ['enviado_corte_id' => 'id']);
    }

    /**
     * Gets query for [[Disenos5]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDisenos5()
    {
        return $this->hasMany(Diseno::class, ['estatus_id' => 'id']);
    }

    /**
     * Gets query for [[Logisticas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLogisticas()
    {
        return $this->hasMany(Logistica::class, ['estatus_pago_id' => 'id']);
    }

    public function getLogisticas0()
    {
        return $this->hasMany(Logistica::class, ['estatus_envio_id' => 'id']);
    }

    /**
     * Gets query for [[Produccions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduccions()
    {
        return $this->hasMany(Produccion::class, ['corte_id' => 'id']);
    }

    /**
     * Gets query for [[Produccions0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduccions0()
    {
        return $this->hasMany(Produccion::class, ['fabricacion_id' => 'id']);
    }

    /**
     * Gets query for [[Produccions1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduccions1()
    {
        return $this->hasMany(Produccion::class, ['estatus_pago_id' => 'id']);
    }

    /**
     * Gets query for [[Produccions2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduccions2()
    {
        return $this->hasMany(Produccion::class, ['empaquetado_id' => 'id']);
    }

    /**
     * Gets query for [[Ventas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas()
    {
        return $this->hasMany(Ventas::class, ['entrega_id' => 'id']);
    }

    /**
     * Gets query for [[Ventas0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas0()
    {
        return $this->hasMany(Ventas::class, ['adicionales_id' => 'id']);
    }

    /**
     * Gets query for [[Ventas1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas1()
    {
        return $this->hasMany(Ventas::class, ['extras_id' => 'id']);
    }

    /**
     * Gets query for [[Ventas2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas2()
    {
        return $this->hasMany(Ventas::class, ['medio_id' => 'id']);
    }

    /**
     * Gets query for [[Ventas3]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas3()
    {
        return $this->hasMany(Ventas::class, ['campaña_id' => 'id']);
    }

    /**
     * Gets query for [[Ventas4]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentas4()
    {
        return $this->hasMany(Ventas::class, ['asesor_id' => 'id']);
    }

    public function getVentas5()
    {
        return $this->hasMany(Ventas::class, ['tipo_letrero_id' => 'id']);
    }

    public static function getLista($tipo)
    {
        return ArrayHelper::map(
            self::find()
                ->where(['tipo' => $tipo])
                ->orderBy('nombre ASC')
                ->all(),
            'id',
            'nombre'
        );
    }

    

}