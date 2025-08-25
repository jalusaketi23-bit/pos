<?php
// config/config.php
// Adjust these to your environment.
return [
  'db' => [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'name' => getenv('DB_NAME') ?: 'revo_pos',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
  ],
  'app' => [
    'base_url' => '/', // if using subfolder, e.g. '/pos/'
    'csrf_key' => 'revo_csrf', // session key for csrf token
  ]
];
