<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "roles_permisos".
 *
 * @property int $id
 * @property int $role_id
 * @property int $permiso_id
 * @property string $created_at
 *
 * @property Role $role
 * @property Permiso $permiso
 */
class RolePermiso extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'roles_permisos';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'permiso_id'], 'required'],
            [['role_id', 'permiso_id'], 'integer'],
            [['created_at'], 'safe'],
            [['role_id', 'permiso_id'], 'unique', 'targetAttribute' => ['role_id', 'permiso_id']],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
            [['permiso_id'], 'exist', 'skipOnError' => true, 'targetClass' => Permiso::class, 'targetAttribute' => ['permiso_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Rol',
            'permiso_id' => 'Permiso',
            'created_at' => 'Creado',
        ];
    }

    /**
     * Gets query for related role.
     */
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    /**
     * Gets query for related permiso.
     */
    public function getPermiso()
    {
        return $this->hasOne(Permiso::class, ['id' => 'permiso_id']);
    }
}