<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/db.php';
require_login();
include __DIR__ . '/../views/partials/header.php';
include __DIR__ . '/../views/partials/navbar.php';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$stmt = db()->prepare("SELECT DATE(sale_date) d, COUNT(*) trx, SUM(grand_total) gt FROM sales WHERE DATE(sale_date) BETWEEN ? AND ? GROUP BY DATE(sale_date) ORDER BY d DESC");
$stmt->execute([$from, $to]);
$rows = $stmt->fetchAll();
$total = 0; foreach ($rows as $r) { $total += (float)$r['gt']; }
?>
<div class="container mt-4">
  <h4>Laporan Penjualan</h4>
  <form class="form-inline mb-3">
    <label class="mr-2">Dari</label>
    <input type="date" name="from" class="form-control mr-2" value="<?=htmlspecialchars($from)?>">
    <label class="mr-2">Sampai</label>
    <input type="date" name="to" class="form-control mr-2" value="<?=htmlspecialchars($to)?>">
    <button class="btn btn-primary">Terapkan</button>
  </form>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>Tanggal</th><th class="text-right">Transaksi</th><th class="text-right">Grand Total</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['d'])?></td>
            <td class="text-right"><?=intval($r['trx'])?></td>
            <td class="text-right"><?=number_format($r['gt'],0,',','.')?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th>Total</th><th></th><th class="text-right"><?=number_format($total,0,',','.')?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
