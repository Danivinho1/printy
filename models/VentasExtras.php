<?php
namespace app\models;

use yii\db\ActiveRecord;

class VentasExtras extends ActiveRecord
{
    public static function tableName()
    {
        return 'ventas_extras';
    }

    public function rules()
    {
        return [
            [['venta_id', 'extra_id'], 'required'],
            [['venta_id', 'extra_id'], 'integer'],
        ];
    }
}