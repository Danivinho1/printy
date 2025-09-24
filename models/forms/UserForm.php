<?php
namespace app\models\forms;

use yii\base\Model;
use app\models\User;

class UserForm extends Model
{
    public $username;
    public $email;
    public $nombre_completo;
    public $role_id;
    public $activo = 1;

    // Campos opcionales para cambiar la contraseña
    public $password;
    public $password_repeat;

    // Para excluir el propio registro en validaciones de unicidad
    private $currentId;

    public function rules()
    {
        return [
            [['username', 'email', 'role_id'], 'required'],
            [['role_id', 'activo'], 'integer'],
            [['username'], 'string', 'max' => 50],
            [['email', 'nombre_completo'], 'string', 'max' => 100],
            ['email', 'email'],

            // Unicidad excluyendo el usuario actual (para update)
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'username',
                'filter' => function ($query) {
                    if ($this->currentId) {
                        $query->andWhere(['!=', 'id', $this->currentId]);
                    }
                },
                'message' => 'El usuario ya existe.',
            ],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'email',
                'filter' => function ($query) {
                    if ($this->currentId) {
                        $query->andWhere(['!=', 'id', $this->currentId]);
                    }
                },
                'message' => 'El email ya está registrado.',
            ],

            // Password opcional en edición
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'skipOnEmpty' => true, 'message' => 'Las contraseñas no coinciden.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Usuario',
            'email' => 'Email',
            'nombre_completo' => 'Nombre completo',
            'role_id' => 'Rol',
            'activo' => 'Activo',
            'password' => 'Nueva contraseña',
            'password_repeat' => 'Repetir contraseña',
        ];
    }

    // Carga datos desde el AR User
    public function loadFromUser(User $user): void
    {
        $this->currentId = $user->id;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->nombre_completo = $user->nombre_completo;
        $this->role_id = $user->role_id;
        $this->activo = (int)$user->activo;
    }

    // Guarda datos en el AR User (retorna true/false)
    public function save(User $user)
    {
        if (!$this->validate()) {
            return false;
        }

        $user->username = $this->username;
        $user->email = $this->email;
        $user->nombre_completo = $this->nombre_completo;
        $user->role_id = (int)$this->role_id;
        $user->activo = (int)$this->activo;

        if (!empty($this->password)) {
            // Cambiar contraseña solo si se proporcionó
            $user->setPassword($this->password);
        }

        return $user->save();
    }
}