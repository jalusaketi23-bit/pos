<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit;
}
$payload = json_decode($_POST['payload'] ?? '', true);
if (!$payload || !check_csrf($payload['csrf'] ?? '')) {
  echo json_encode(['ok'=>false,'error'=>'CSRF/Request tidak valid']); exit;
}

$items = $payload['items'] ?? [];
if (!$items) { echo json_encode(['ok'=>false,'error'=>'Items kosong']); exit; }

$discount_total = (float)($payload['discount_total'] ?? 0);
$tax_total = (float)($payload['tax_total'] ?? 0);
$paid_amount = (float)($payload['paid_amount'] ?? 0);
$method = $payload['method'] ?? 'cash';
$customer_name = trim($payload['customer_name'] ?? '');

try {
  $pdo = db();
  $pdo->beginTransaction();

  // Find/ensure customer (default to Umum id=1 if exists)
  $customer_id = null;
  if ($customer_name !== '') {
    $st = $pdo->prepare("SELECT id FROM customers WHERE name = ? LIMIT 1");
    $st->execute([$customer_name]);
    $row = $st->fetch();
    if ($row) $customer_id = (int)$row['id'];
    else {
      $pdo->prepare("INSERT INTO customers (name) VALUES (?)")->execute([$customer_name]);
      $customer_id = (int)$pdo->lastInsertId();
    }
  } else {
    $st = $pdo->query("SELECT id FROM customers WHERE name='Umum' LIMIT 1");
    $row = $st->fetch();
    $customer_id = $row ? (int)$row['id'] : null;
  }

  // compute totals & validate stock
  $subtotal = 0;
  foreach ($items as $it) {
    $pid = (int)$it['product_id'];
    $qty = max(1, (int)$it['qty']);
    $unit = (float)$it['unit_price'];
    $disc = max(0, (float)$it['discount']);
    $subtotal += ($unit * $qty) - $disc;

    // check stock
    $s = $pdo->prepare("SELECT stock, name FROM products WHERE id = ? FOR UPDATE");
    $s->execute([$pid]);
    $p = $s->fetch();
    if (!$p) throw new Exception("Produk tidak ditemukan (ID $pid)");
    if ((int)$p['stock'] < $qty) throw new Exception("Stok tidak cukup untuk {$p['name']}");
  }
  $grand = max(0, $subtotal - $discount_total + $tax_total);

  // create invoice no SL-YYYYMM-####
  $ym = date('Ym');
  $st = $pdo->prepare("SELECT COUNT(*) c FROM sales WHERE DATE_FORMAT(sale_date,'%Y%m') = ?");
  $st->execute([date('Ym')]);
  $run = (int)$st->fetch()['c'] + 1;
  $invoice_no = sprintf("SL-%s-%04d", date('Ym'), $run);

  // insert sale
  session_start();
  $user_id = $_SESSION['user']['id'] ?? 1;
  $pdo->prepare("INSERT INTO sales (customer_id, invoice_no, sale_date, subtotal, discount_total, tax_total, grand_total, user_id) VALUES (?,?,?,?,?,?,?,?)")
      ->execute([$customer_id, $invoice_no, date('Y-m-d H:i:s'), $subtotal, $discount_total, $tax_total, $grand, $user_id]);
  $sale_id = (int)$pdo->lastInsertId();

  // insert items & update stock + movements
  $itStmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, qty, unit_price, discount) VALUES (?,?,?,?,?)");
  $updStock= $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
  $movStmt = $pdo->prepare("INSERT INTO stock_movements (product_id, ref_type, ref_id, qty) VALUES (?,?,?,?)");
  foreach ($items as $it) {
    $pid = (int)$it['product_id'];
    $qty = max(1, (int)$it['qty']);
    $unit = (float)$it['unit_price'];
    $disc = max(0, (float)$it['discount']);
    $itStmt->execute([$sale_id, $pid, $qty, $unit, $disc]);
    $updStock->execute([$qty, $pid]);
    $movStmt->execute([$pid, 'sale', $sale_id, -$qty]);
  }

  // payment
  $payStmt = $pdo->prepare("INSERT INTO payments (sale_id, method, paid_amount, change_amount, notes) VALUES (?,?,?,?,?)");
  $payStmt->execute([$sale_id, $method, $paid_amount, max(0, $paid_amount - $grand), null]);

  $pdo->commit();
  echo json_encode(['ok'=>true,'invoice_no'=>$invoice_no, 'sale_id'=>$sale_id]);
} catch (Exception $e) {
  if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
