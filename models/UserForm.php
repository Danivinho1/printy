<?php

namespace app\models;

use Yii;
use yii\base\Model;

class UserForm extends Model
{
    public $username;
    public $email;
    public $nombre_completo;
    public $role_id;
    public $password;

    public function rules()
    {
        return [
            [['username', 'role_id'], 'required'],
            [['username'], 'string', 'max' => 50],
            [['email'], 'string', 'max' => 100],
            [['nombre_completo'], 'string', 'max' => 100],
            [['email'], 'email'],
            [['role_id'], 'integer'],
            [['password'], 'string', 'min' => 6],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Usuario',
            'email' => 'Email',
            'nombre_completo' => 'Nombre completo',
            'role_id' => 'Rol',
            'password' => 'Contraseña',
        ];
    }

    public function loadFromUser(User $user): void
    {
        $this->username = $user->username;
        $this->email = $user->email;
        $this->nombre_completo = $user->nombre_completo;
        $this->role_id = $user->role_id;
    }

    public function save(?User $user = null): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $isNew = false;
        if ($user === null) {
            $user = new User();
            $isNew = true;
            $user->activo = 1;
        }

        $user->username = $this->username;
        $user->email = $this->email;
        $user->nombre_completo = $this->nombre_completo;
        $user->role_id = (int)$this->role_id;

        if ($this->password) {
            $user->setPassword($this->password);
        } elseif ($isNew) {
            $this->addError('password', 'La contraseña es obligatoria para crear un usuario.');
            return null;
        }

        return $user->save() ? $user : null;
    }
}