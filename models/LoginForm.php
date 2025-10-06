<?php
namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Usuario o contraseña incorrectos.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            // Desmarca “Recordarme” si quieres evitar tema de authKey mientras tanto
            $duration = $this->rememberMe ? 3600 * 24 * 30 : 0;
            return Yii::$app->user->login($this->getUser(), $duration);
        }
        return false;
    }

    protected function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Usuario::findByUsername($this->username);
        }
        return $this->_user;
    }
}