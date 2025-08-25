<?php
// app/db.php
function db() {
  static $pdo;
  if ($pdo) return $pdo;
  $conf = require __DIR__ . '/../config/config.php';
  $dsn = "mysql:host={$conf['db']['host']};dbname={$conf['db']['name']};charset={$conf['db']['charset']}";
  $opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];
  $pdo = new PDO($dsn, $conf['db']['user'], $conf['db']['pass'], $opt);
  return $pdo;
}
