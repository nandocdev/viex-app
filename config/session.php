<?php
// config/session.php

return [
   'driver' => $_ENV['SESSION_DRIVER'] ?? 'file',
   'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120), // en minutos
   'expire_on_close' => false,
   'encrypt' => false,
   'path' => '/',
   'domain' => $_ENV['SESSION_DOMAIN'] ?? null,
   'secure' => filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? false, FILTER_VALIDATE_BOOLEAN),
   'http_only' => true,
   'same_site' => $_ENV['COOKIE_SAME_SITE'] ?? 'Lax',

   // Para el driver de archivo
   'files' => dirname(__DIR__) . '/storage/sessions',

   // Para el driver de base de datos
   'table' => 'sessions',
];