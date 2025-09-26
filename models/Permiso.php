<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo Permiso (ActiveRecord).
 *
 * Ajusta tableName(), rules() y relaciones según tu esquema.
 *
 * Propiedades típicas:
 * @property int $id
 * @property string $modulo
 * @property string $accion
 * @property string $nombre
 * @property string|null $descripcion
 * @property int $activo
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
            [['descripcion'], 'string'],
            [['activo'], 'boolean'],
            [['modulo', 'accion', 'nombre'], 'string', 'max' => 100],
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