<?php
return [
    'class' => 'yii\db\Connection',
    // Fuerza TCP/IP en macOS
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=printy', // Cambia 'printy' al nombre real de tu base
    'username' => 'root',   // XAMPP por defecto
    'password' => '',       // XAMPP por defecto (vacío). Si pusiste clave, ponla aquí.
    'charset' => 'utf8',
];