<?php
require_once __DIR__ . '/../app/db.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$out = ['items' => []];
if ($q !== '') {
  $like = '%' . $q . '%';
  $stmt = db()->prepare("SELECT id, name, sell_price, stock FROM products WHERE (barcode = ? OR name LIKE ?) AND is_active = 1 ORDER BY name LIMIT 20");
  $stmt->execute([$q, $like]);
  $out['items'] = $stmt->fetchAll();
}
echo json_encode($out);
