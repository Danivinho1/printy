<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "feriados".
 *
 * @property int $id
 * @property string $fecha
 * @property string $descripcion
 */
class Feriados extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feriados';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha', 'descripcion'], 'required'],
            [['fecha'], 'safe'],
            [['descripcion'], 'string', 'max' => 100],
            [['fecha'], 'unique'],
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
            'descripcion' => 'Descripcion',
        ];
    }

}
