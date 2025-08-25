(function(){
  function formatRupiah(n){ return (n||0).toLocaleString('id-ID'); }
  var cart = []; // [{id, name, price, qty, discount}]
  var $cartBody, $tSubtotal, $tGrand, $discountTotal, $taxTotal, $paid, $change;

  function recalc(){
    var subtotal = 0;
    cart.forEach(function(it){
      subtotal += (it.price * it.qty) - (it.discount||0);
    });
    var disc = parseFloat($discountTotal.val())||0;
    var tax  = parseFloat($taxTotal.val())||0;
    var grand = Math.max(0, subtotal - disc + tax);
    $tSubtotal.text(formatRupiah(subtotal));
    $tGrand.text(formatRupiah(grand));
    var paid = parseFloat($paid.val())||0;
    $change.text(formatRupiah(Math.max(0, paid - grand)));
  }

  function renderCart(){
    $cartBody.empty();
    cart.forEach(function(it, idx){
      var row = $('<tr>');
      row.append($('<td>').text(it.name));
      row.append($('<td class="text-right">').text(formatRupiah(it.price)));
      var qty = $('<input type="number" min="1" class="form-control form-control-sm text-right">').val(it.qty).on('input', function(){
        it.qty = Math.max(1, parseInt(this.value)||1); recalc(); renderCart();
      });
      row.append($('<td width="110">').append(qty));
      var disc = $('<input type="number" min="0" class="form-control form-control-sm text-right">').val(it.discount||0).on('input', function(){
        it.discount = Math.max(0, parseFloat(this.value)||0); recalc();
      });
      row.append($('<td class="text-right">').append(disc));
      var sub = (it.price * it.qty) - (it.discount||0);
      row.append($('<td class="text-right">').text(formatRupiah(sub)));
      var del = $('<button class="btn btn-sm btn-outline-danger">&times;</button>').on('click', function(){
        cart.splice(idx,1); renderCart(); recalc();
      });
      row.append($('<td>').append(del));
      $cartBody.append(row);
    });
    recalc();
  }

  function addToCart(p){
    var found = cart.find(function(it){ return it.id == p.id; });
    if (found) found.qty += 1;
    else cart.push({id:p.id, name:p.name, price:parseFloat(p.sell_price), qty:1, discount:0});
    renderCart();
  }

  function search(q){
    $('#search-results').empty();
    if (!q) return;
    $.getJSON('/api/products_search.php', {q:q}, function(res){
      (res.items||[]).forEach(function(p){
        var item = $('<a class="list-group-item list-group-item-action">').text(p.name+' — Rp'+formatRupiah(p.sell_price)+' (Stok: '+p.stock+')');
        item.on('click', function(){ addToCart(p); });
        $('#search-results').append(item);
      });
    });
  }

  $(function(){
    $cartBody = $('#cart-table tbody');
    $tSubtotal= $('#t-subtotal');
    $tGrand   = $('#t-grand');
    $discountTotal = $('#discount-total').on('input', recalc);
    $taxTotal = $('#tax-total').on('input', recalc);
    $paid     = $('#paid-amount').on('input', recalc);
    $change   = $('#change-amount');

    $('#search').on('input', function(){
      search(this.value.trim());
    }).on('keypress', function(e){
      if (e.which === 13) { // Enter adds first result if exists
        var first = $('#search-results .list-group-item').first();
        if (first.length) first.click();
        $('#search').select();
      }
    });
    $('#btn-clear').on('click', function(){
      cart = []; renderCart(); $('#search').val('').focus(); $('#search-results').empty();
    });
    $('#btn-pay').on('click', function(){
      $('#paid-amount').focus();
    });

    $('#btn-checkout').on('click', function(){
      if (!cart.length) { $('#pay-alert').html('<div class="alert alert-warning">Keranjang kosong.</div>'); return; }
      var payload = {
        csrf: $('#csrf').val(),
        items: cart.map(function(it){ return {product_id: it.id, qty: it.qty, unit_price: it.price, discount: it.discount||0}; }),
        discount_total: parseFloat($discountTotal.val())||0,
        tax_total: parseFloat($taxTotal.val())||0,
        paid_amount: parseFloat($('#paid-amount').val())||0,
        method: $('#pay-method').val(),
        customer_name: $('#customer-name').val()
      };
      $.ajax({
        url:'/api/checkout.php', method:'POST', data: {payload: JSON.stringify(payload)},
        success:function(resp){
          try { var r = JSON.parse(resp); } catch(e){ r={ok:false,error:'Respon tidak valid'}; }
          if (r.ok) {
            $('#pay-alert').html('<div class="alert alert-success">Transaksi selesai. No: '+r.invoice_no+'</div>');
            cart = []; renderCart();
          } else {
            $('#pay-alert').html('<div class="alert alert-danger">'+(r.error||'Gagal')+'</div>');
          }
        },
        error:function(){ $('#pay-alert').html('<div class="alert alert-danger">Terjadi kesalahan koneksi.</div>'); }
      });
    });

    renderCart();
  });
})();
