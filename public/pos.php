<?php
require_once __DIR__ . '/../app/auth.php';
require_login();
$token = csrf_token();
include __DIR__ . '/../views/partials/header.php';
include __DIR__ . '/../views/partials/navbar.php';
?>
<div class="container-fluid mt-3">
  <div class="row">
    <div class="col-lg-7 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="form-row">
            <div class="col-md-9 mb-2">
              <input id="search" type="text" class="form-control form-control-lg" placeholder="Scan barcode / ketik nama produk ...">
            </div>
            <div class="col-md-3 mb-2">
              <button id="btn-clear" class="btn btn-outline-secondary btn-lg btn-block">Bersihkan</button>
            </div>
          </div>
          <div id="search-results" class="list-group small" style="max-height: 240px; overflow:auto;"></div>
        </div>
      </div>
      <div class="card shadow-sm mt-3">
        <div class="card-body p-2">
          <h5>Keranjang</h5>
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0" id="cart-table">
              <thead>
                <tr>
                  <th>Produk</th>
                  <th class="text-right">Harga</th>
                  <th width="110">Qty</th>
                  <th class="text-right">Diskon</th>
                  <th class="text-right">Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <th colspan="4" class="text-right">Subtotal</th>
                  <th class="text-right" id="t-subtotal">0</th>
                  <th></th>
                </tr>
                <tr>
                  <th colspan="4" class="text-right">Diskon Nota</th>
                  <th class="text-right">
                    <input type="number" id="discount-total" class="form-control form-control-sm text-right" value="0" min="0">
                  </th>
                  <th></th>
                </tr>
                <tr>
                  <th colspan="4" class="text-right">PPN</th>
                  <th class="text-right"><input type="number" id="tax-total" class="form-control form-control-sm text-right" value="0" min="0"></th>
                  <th></th>
                </tr>
                <tr>
                  <th colspan="4" class="text-right">Grand Total</th>
                  <th class="text-right h5" id="t-grand">0</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="text-right">
            <button class="btn btn-success btn-lg" id="btn-pay">Bayar</button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-5 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5>Pembayaran</h5>
          <div class="form-group">
            <label>Metode</label>
            <select id="pay-method" class="form-control">
              <option value="cash">Tunai</option>
              <option value="transfer">Transfer</option>
              <option value="mixed">Campuran</option>
            </select>
          </div>
          <div class="form-group">
            <label>Dibayar</label>
            <input type="number" id="paid-amount" class="form-control" value="0" min="0">
            <small class="form-text text-muted">Kembalian: <span id="change-amount">0</span></small>
          </div>
          <div class="form-group">
            <label>Pelanggan</label>
            <input type="text" id="customer-name" class="form-control" placeholder="Kosongkan untuk 'Umum'">
          </div>
          <button id="btn-checkout" class="btn btn-primary btn-block">Selesaikan Transaksi</button>
          <input type="hidden" id="csrf" value="<?=$token?>">
          <div id="pay-alert" class="mt-3"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../views/partials/footer.php'; ?>
