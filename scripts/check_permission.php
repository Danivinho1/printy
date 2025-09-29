<?php
// Uso: php scripts/check_permission.php <userId> <modulo> [accion]
// Ejemplo: php scripts/check_permission.php 6 ventas create

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// Cargar config web si existe
$webConfigFile = __DIR__ . '/../config/web.php';
if (!file_exists($webConfigFile)) {
    echo "Error: config/web.php no encontrado. Asegúrate de ejecutar desde la raíz del proyecto.\n";
    exit(2);
}
$webConfig = require $webConfigFile;

// Construir una configuración mínima para la app de consola,
// copiando solo lo necesario (db, cache, params) y forzando errorHandler de consola.
$config = [
    'id' => 'cli-check-perm',
    'basePath' => dirname(__DIR__),
    'bootstrap' => isset($webConfig['bootstrap']) ? $webConfig['bootstrap'] : [],
    'components' => [
        'errorHandler' => ['class' => 'yii\console\ErrorHandler'],
    ],
    'params' => isset($webConfig['params']) ? $webConfig['params'] : [],
];

// Copiar db, cache y log si existen en web config (para que ActiveRecord y cache funcionen)
if (isset($webConfig['components']['db'])) {
    $config['components']['db'] = $webConfig['components']['db'];
}
if (isset($webConfig['components']['cache'])) {
    $config['components']['cache'] = $webConfig['components']['cache'];
}
if (isset($webConfig['components']['log'])) {
    // en log puede haber targets; pasar tal cual suele funcionar
    $config['components']['log'] = $webConfig['components']['log'];
}

// Crear la aplicación de consola con la config segura
new yii\console\Application($config);

// Parsear argumentos
$userId = isset($argv[1]) ? (int)$argv[1] : null;
$mod = $argv[2] ?? null;
$act = $argv[3] ?? 'index';

if (!$userId || !$mod) {
    echo "Uso: php scripts/check_permission.php <userId> <modulo> [accion]\n";
    exit(1);
}

// Buscar usuario y comprobar permiso
$user = \app\models\Usuario::findOne($userId);
if (!$user) {
    echo "Usuario no encontrado: $userId\n";
    exit(1);
}

try {
    $allowed = $user->can($mod, $act);
} catch (\Throwable $e) {
    echo "Error comprobando permiso: " . $e->getMessage() . "\n";
    exit(3);
}

echo $allowed ? "ALLOW\n" : "DENY\n";
exit($allowed ? 0 : 2);
