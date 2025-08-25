<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="/index.php">Revo POS</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample07" aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarsExample07">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item"><a class="nav-link" href="/pos.php">Kasir</a></li>
      <li class="nav-item"><a class="nav-link" href="/products.php">Produk</a></li>
      <li class="nav-item"><a class="nav-link" href="/reports.php">Laporan</a></li>
    </ul>
    <span class="navbar-text mr-3">
      <?php if (!empty($_SESSION['user'])): ?>
        Halo, <strong><?=htmlspecialchars($_SESSION['user']['name'])?></strong>
      <?php endif; ?>
    </span>
    <?php if (!empty($_SESSION['user'])): ?>
      <a href="/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    <?php endif; ?>
  </div>
</nav>
