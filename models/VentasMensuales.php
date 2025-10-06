<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ventas_mensuales".
 *
 * @property int $id
 * @property string $fecha
 * @property float|null $total
 * @property string|null $nivel
 */
class VentasMensuales extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ventas_mensuales';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nivel'], 'default', 'value' => null],
            [['total'], 'default', 'value' => 0.00],
            [['fecha'], 'required'],
            [['fecha'], 'safe'],
            [['total'], 'number'],
            [['nivel'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha' => 'Fecha',
            'total' => 'Total',
            'nivel' => 'Nivel',
        ];
    }
public function getNivel()
{
    return $this->hasOne(Catalogos::class, ['id' => 'nivel_id']);
}


}
