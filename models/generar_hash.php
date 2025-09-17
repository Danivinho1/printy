<?php
// generar_hash.php

echo "Escribe la contraseña: ";
$password = trim(fgets(STDIN));

if (empty($password)) {
    echo "❌ No escribiste ninguna contraseña\n";
    exit;
}

// Genera hash seguro con bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Hash generado:\n$hash\n";
