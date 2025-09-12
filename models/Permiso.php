<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $modulo
 * @property string $accion
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $activo
 */
class Permiso extends ActiveRecord
{
    public static function tableName()
    {
        return 'permisos';
    }

    public function rules()
    {
        return [
            [['modulo', 'accion', 'nombre'], 'required'],
            [['descripcion'], 'string', 'max' => 200],
            [['activo'], 'boolean'],
            [['modulo', 'accion'], 'string', 'max' => 50],
            [['nombre'], 'string', 'max' => 100],
            [['modulo', 'accion'], 'unique', 'targetAttribute' => ['modulo', 'accion']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modulo' => 'Módulo',
            'accion' => 'Acción',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'activo' => 'Activo',
        ];
    }
}