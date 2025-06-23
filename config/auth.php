<?php

return [
   /*
   |--------------------------------------------------------------------------
   | Autenticación por Defecto
   |--------------------------------------------------------------------------
   |
   | Aquí puedes especificar el guard de autenticación por defecto que
   | se usará en tu aplicación. Puedes cambiarlo según necesites.
   |
   */
   'defaults' => [
      'guard' => 'web',
   ],

   /*
   |--------------------------------------------------------------------------
   | Guards de Autenticación
   |--------------------------------------------------------------------------
   |
   | Aquí se definen todos los guards de autenticación para tu aplicación.
   | Cada guard tiene un 'driver' y un 'provider'. El driver define cómo
   | se autentica el usuario (sesión, token) y el provider define cómo
   | se recupera el usuario de la base de datos.
   |
   */
   'guards' => [
      'web' => [
         'driver' => 'session',
         'provider' => 'users',
      ],

      'api' => [
         'driver' => 'token',
         'provider' => 'users',
      ],
   ],

   /*
   |--------------------------------------------------------------------------
   | Proveedores de Usuarios
   |--------------------------------------------------------------------------
   |
   | Aquí se definen los "providers" de usuarios. Por ahora, solo tenemos
   | uno que usa nuestro modelo User, pero en el futuro podrías tener
   | providers para LDAP, etc.
   |
   */
   'providers' => [
    'users' => [
        'driver' => 'eloquent', // O 'model'
        // --- ¡ESTA ES LA LÍNEA MODIFICADA! ---
        'model' => Phast\App\Modules\Auth\Models\Entities\UserEntity::class, 
    ]
   ],
];