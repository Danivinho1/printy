<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $username
 * @property string|null $email
 * @property string $password_hash
 * @property string|null $nombre_completo
 * @property int $role_id
 * @property int $activo
 * @property string|null $ultimo_login
 * @property int $intentos_fallidos
 * @property string|null $bloqueado_hasta
 *
 * @property Role $role
 */
class Usuario extends ActiveRecord implements IdentityInterface
{
    public $new_password; // para formularios (opcional)

    public static function tableName()
    {
        return 'usuarios';
    }

    public function rules()
    {
        return [
            [['username', 'role_id'], 'required'],
            [['email'], 'email'],
            [['role_id', 'intentos_fallidos'], 'integer'],
            [['activo'], 'boolean'],
            [['username'], 'string', 'max' => 50],
            [['email', 'nombre_completo'], 'string', 'max' => 100],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['new_password'], 'string', 'min' => 6],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Usuario',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'nombre_completo' => 'Nombre completo',
            'role_id' => 'Rol',
            'activo' => 'Activo',
            'new_password' => 'Contraseña (nueva)',
        ];
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    // ----------------- IdentityInterface -----------------
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'activo' => 1]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'activo' => 1]);
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
        if (empty($this->password_hash)) {
            return false;
        }
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    // ----------------- Autorización -----------------
    public function can($modulo, $accion = 'index'): bool
    {
        // Admin por rol
        if ($this->role && (int)$this->role->es_admin === 1) {
            return true;
        }

        // Permiso “admin/all”
        $hasAll = (new \yii\db\Query())
            ->from('roles_permisos rp')
            ->innerJoin('permisos p', 'p.id = rp.permiso_id')
            ->where([
                'rp.role_id' => $this->role_id,
                'p.modulo' => 'admin',
                'p.accion' => 'all',
            ])->exists();
        if ($hasAll) {
            return true;
        }

        // Permiso específico
        $hasSpecific = (new \yii\db\Query())
            ->from('roles_permisos rp')
            ->innerJoin('permisos p', 'p.id = rp.permiso_id')
            ->where([
                'rp.role_id' => $this->role_id,
                'p.modulo' => $modulo,
                'p.accion' => $accion,
            ])->exists();

        return $hasSpecific;
    }
}