<?php
// config/services.php

return [
   'stripe' => [
      'key' => $_ENV['STRIPE_KEY'],
      'secret' => $_ENV['STRIPE_SECRET'],
   ],

   'aws' => [
      'key' => $_ENV['AWS_ACCESS_KEY_ID'],
      'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
      'region' => $_ENV['AWS_DEFAULT_REGION'],
      'url' => $_ENV['AWS_URL'],
      's3' => [
         'bucket' => $_ENV['AWS_BUCKET'],
      ],
   ],

   // Ejemplo para login social con GitHub
   'github' => [
      'client_id' => $_ENV['GITHUB_CLIENT_ID'],
      'client_secret' => $_ENV['GITHUB_CLIENT_SECRET'],
      'redirect' => $_ENV['APP_URL'] . '/auth/github/callback',
   ],
];

