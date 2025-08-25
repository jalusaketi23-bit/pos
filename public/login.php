<?php
require_once __DIR__ . '/../app/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!check_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF token.';
  } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    // small delay on fail (rate-limit-ish)
    if (login($email, $password)) {
      header('Location: /index.php');
      exit;
    } else {
      usleep(800000);
      $err = 'Login gagal. Cek email/password.';
    }
  }
}
$token = csrf_token();
include __DIR__ . '/../views/partials/header.php';
?>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="mb-4">Login Revo POS</h4>
          <?php if ($err): ?><div class="alert alert-danger"><?=$err?></div><?php endif; ?>
          <form method="post">
            <input type="hidden" name="csrf" value="<?=$token?>">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary btn-block">Login</button>
          </form>
          <p class="mt-3 text-muted small">Demo: admin@revo.local / password</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
