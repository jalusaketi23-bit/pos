<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/db.php';
require_login();
include __DIR__ . '/../views/partials/header.php';
include __DIR__ . '/../views/partials/navbar.php';

// simple list with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 20;
$off  = ($page - 1) * $per;
$total = db()->query("SELECT COUNT(*) c FROM products")->fetch()['c'];
$stmt = db()->prepare("SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC LIMIT :off,:per");
$stmt->bindValue(':off', $off, PDO::PARAM_INT);
$stmt->bindValue(':per', $per, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Produk</h4>
    <a href="/product_new.php" class="btn btn-sm btn-primary">Tambah Produk</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>SKU</th><th>Barcode</th><th>Nama</th><th>Kategori</th><th class="text-right">Harga Jual</th><th class="text-right">Stok</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['sku'])?></td>
          <td><?=htmlspecialchars($r['barcode'])?></td>
          <td><?=htmlspecialchars($r['name'])?></td>
          <td><?=htmlspecialchars($r['category'] ?? '-')?></td>
          <td class="text-right"><?=number_format($r['sell_price'],0,',','.')?></td>
          <td class="text-right"><?=intval($r['stock'])?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php $pages = max(1, ceil($total / $per)); if ($pages > 1): ?>
    <nav><ul class="pagination">
      <?php for ($i=1;$i<=$pages;$i++): ?>
        <li class="page-item <?=$i==$page?'active':''?>"><a class="page-link" href="?page=<?=$i?>"><?=$i?></a></li>
      <?php endfor; ?>
    </ul></nav>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
