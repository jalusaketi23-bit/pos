<?php
require_once __DIR__ . '/../app/auth.php';
require_login();
include __DIR__ . '/../views/partials/header.php';
include __DIR__ . '/../views/partials/navbar.php';
?>
<div class="container mt-4">
  <div class="jumbotron py-4">
    <h1 class="display-5">Dashboard</h1>
    <p class="lead">Selamat datang di Revo POS. Gunakan menu di atas untuk mulai transaksi, kelola produk, dan lihat laporan.</p>
    <a href="/pos.php" class="btn btn-primary">Buka Kasir</a>
  </div>
</div>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
