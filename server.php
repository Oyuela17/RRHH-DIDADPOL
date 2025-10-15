<?php

// Usa el puerto de Render si existe; si no, 8000 (local)
$port = getenv('PORT') ?: 8000;
$host = '0.0.0.0';

echo "Servidor iniciado en http://{$host}:{$port}\n";

// Servidor embebido de PHP sirviendo la carpeta public/
passthru("php -S {$host}:{$port} -t public");
