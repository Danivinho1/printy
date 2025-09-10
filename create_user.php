<?php
require_once 'vendor/autoload.php';
require_once 'config/web.php';

$app = new yii\web\Application($config);

$user = new app\models\Usuario();
$user->username = 'admin';
$user->email = 'admin@printy.com';
$user->password = 'admin123'; // Se hasheará automáticamente
$user->nombre_completo = 'Administrador del Sistema';
$user->role_id = 1;
$user->activo = 1;
$user->scenario = 'create';

if ($user->save()) {
    echo "Usuario creado exitosamente\n";
} else {
    echo "Error: " . json_encode($user->errors) . "\n";
}
?>