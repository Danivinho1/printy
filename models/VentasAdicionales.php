<?php
namespace app\models;

use yii\db\ActiveRecord;

class VentasAdicionales extends ActiveRecord
{
    public static function tableName()
    {
        return 'ventas_adicionales';
    }

    public function rules()
    {
        return [
            [['venta_id', 'adicional_id'], 'required'],
            [['venta_id', 'adicional_id'], 'integer'],
        ];
    }
}