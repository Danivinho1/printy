<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "productos_vendidos".
 *
 * @property int $id
 * @property int|null $tipo_letrero_id Tabla catalogos: tipo_letrero
 * @property int|null $unidades
 * @property float|null $subtotal
 * @property float|null $total
 * @property string $created_at
 *
 * @property Catalogos $tipoLetrero
 */
class ProductosVendidos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'productos_vendidos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tipo_letrero_id'], 'default', 'value' => null],
            [['unidades'], 'default', 'value' => 0],
            [['total'], 'default', 'value' => 0.00],
            [['tipo_letrero_id', 'unidades'], 'integer'],
            [['subtotal', 'total'], 'number'],
            [['created_at'], 'safe'],
            [['tipo_letrero_id'], 'exist', 'skipOnError' => true, 'targetClass' => Catalogos::class, 'targetAttribute' => ['tipo_letrero_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipo_letrero_id' => 'Tipo Letrero ID',
            'unidades' => 'Unidades',
            'subtotal' => 'Subtotal',
            'total' => 'Total',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[TipoLetrero]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipoLetrero()
    {
        return $this->hasOne(Catalogos::class, ['id' => 'tipo_letrero_id']);

    }

}
