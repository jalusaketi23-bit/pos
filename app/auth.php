<?php
// app/auth.php
session_start();

function csrf_token() {
  $conf = require __DIR__ . '/../config/config.php';
  if (empty($_SESSION[$conf['app']['csrf_key']])) {
    $_SESSION[$conf['app']['csrf_key']] = bin2hex(random_bytes(16));
  }
  return $_SESSION[$conf['app']['csrf_key']];
}

function check_csrf($token) {
  $conf = require __DIR__ . '/../config/config.php';
  return isset($_SESSION[$conf['app']['csrf_key']]) && hash_equals($_SESSION[$conf['app']['csrf_key']], $token);
}

function require_login() {
  if (empty($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
  }
}

function login($email, $password) {
  require_once __DIR__ . '/db.php';
  $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user'] = [
      'id' => $user['id'],
      'name' => $user['name'],
      'email' => $user['email'],
      'role' => $user['role']
    ];
    session_regenerate_id(true);
    return true;
  }
  return false;
}

function logout() {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
}
