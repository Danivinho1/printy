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
    public $new_password;

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
        $modulo = mb_strtolower(trim((string)$modulo));
        $accion = mb_strtolower(trim((string)$accion));

        $roleId = (int)$this->role_id;
        if ($roleId <= 0) {
            Yii::info("can(): user={$this->id} sin role_id válido", __METHOD__);
            return false;
        }

        // Fast path: validar si el rol es admin
        $isAdmin = (new \yii\db\Query())
            ->select('es_admin')
            ->from('roles')
            ->where(['id' => $roleId, 'activo' => 1])
            ->scalar();

        if ((int)$isAdmin === 1) {
            Yii::info("can(): user={$this->id} (role={$roleId}) es_admin => ALLOW", __METHOD__);
            return true;
        }

        // Cache de permisos por rol
        $cacheKey = "role_perms_{$roleId}";
        $perms = Yii::$app->cache->get($cacheKey);

        if ($perms === false || $perms === null) {
            $rows = (new \yii\db\Query())
                ->select(['p.modulo', 'p.accion'])
                ->from('permisos p')
                ->innerJoin('roles_permisos rp', 'rp.permiso_id = p.id')
                ->where(['rp.role_id' => $roleId, 'p.activo' => 1])
                ->all();

            $perms = [];
            foreach ($rows as $r) {
                $k = mb_strtolower(trim($r['modulo'])) . '::' . mb_strtolower(trim($r['accion']));
                $perms[$k] = true;
            }

            Yii::info("can(): permisos cargados para role={$roleId}: " . implode(', ', array_keys($perms)), __METHOD__);
            Yii::$app->cache->set($cacheKey, $perms, 3600);
        }

        // Permiso global admin::all
        if (!empty($perms['admin::all'])) {
            Yii::info("can(): user={$this->id} tiene admin::all => ALLOW", __METHOD__);
            return true;
        }

        $key = trim($modulo) . '::' . trim($accion);
        Yii::info("DEBUG permisos -> comparando {$key} con " . implode(', ', array_keys($perms)), __METHOD__);

        foreach ($perms as $permKey => $val) {
            if (strcasecmp(trim($permKey), $key) === 0) {
                Yii::info("can(): user={$this->id}, role={$roleId}, match={$permKey} == {$key} => ALLOW", __METHOD__);
                return true;
            }
        }

        Yii::info("can(): user={$this->id}, role={$roleId}, check={$key} => DENY", __METHOD__);
        return false;
    }
}
