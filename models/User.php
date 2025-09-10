<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'usuarios';
    }

    public function rules()
    {
        return [
            [['username', 'role_id'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 100],
            [['nombre_completo'], 'string', 'max' => 100],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['role_id', 'activo', 'intentos_fallidos'], 'integer'],
            [['ultimo_login', 'bloqueado_hasta', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id, 'activo' => 1])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username, 'activo' => 1])->one();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    // Paso 2: método para verificar permisos desde las vistas/layouts
    public function hasPermission(string $module, string $action = 'all'): bool
    {
        // Acceso total si el rol es admin
        if ($this->role && (int)$this->role->es_admin === 1) {
            return true;
        }

        // Delegar en el componente centralizado
        return Yii::$app->perm->can($module . '.' . $action);
    }
}