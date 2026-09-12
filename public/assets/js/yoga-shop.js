(function ($) {
  function store(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }
  function load(key) {
    try { return JSON.parse(localStorage.getItem(key) || "[]"); } catch (e) { return []; }
  }
  function cart() { return load("yoga_cart"); }
  function wish() { return load("yoga_wish"); }
  function money(n) { return "$" + Number(n).toFixed(2); }
  function qtyFromPage() {
    var q = parseInt($("#quantity").text(), 10);
    return q > 0 ? q : 1;
  }
  function addItem(listKey, item, qty) {
    var list = load(listKey);
    var found = list.find(function (row) { return row.id === item.id; });
    if (found) found.qty = (found.qty || 1) + (qty || 1);
    else list.push(Object.assign({ qty: qty || 1 }, item));
    store(listKey, list);
    render();
  }
  function productFromBtn($btn) {
    return {
      id: $btn.data("id"),
      title: $btn.data("title"),
      price: parseFloat($btn.data("price")) || 0,
      image: $btn.data("image") || ""
    };
  }
  function renderList(target, items, removableKey) {
    var $el = $(target);
    if (!$el.length) return;
    if (!items.length) {
      $el.html("<p>Your list is empty.</p>");
      return;
    }
    var html = items.map(function (item) {
      return '<div class="flex gap-4 border-b border-primary/20 py-3 items-center">' +
        (item.image ? '<img src="' + item.image + '" alt="" class="w-16 h-16 object-cover">' : "") +
        "<div class='flex-1'><p class='uppercase'>" + item.title + "</p><p>" + money(item.price) + " × " + (item.qty || 1) + "</p></div>" +
        '<button type="button" class="yoga-remove underline" data-key="' + removableKey + '" data-id="' + item.id + '">Remove</button></div>';
    }).join("");
    var total = items.reduce(function (sum, item) { return sum + item.price * (item.qty || 1); }, 0);
    $el.html(html + "<p class='mt-4 font-medium'>Total: " + money(total) + "</p>");
  }
  function render() {
    var c = cart();
    var w = wish();
    $(".yoga-cart-count").text(c.reduce(function (n, i) { return n + (i.qty || 1); }, 0));
    renderList("#cart-drawer-items", c, "yoga_cart");
    renderList("#wishlist-drawer-items", w, "yoga_wish");
    renderList("#yoga-cart-table", c, "yoga_cart");
    renderList("#yoga-wishlist-table", w, "yoga_wish");
    renderList("#yoga-checkout-summary", c, "yoga_cart");
  }
  $(document).on("click", ".yoga-add-cart", function () {
    addItem("yoga_cart", productFromBtn($(this)), qtyFromPage());
  });
  $(document).on("click", ".yoga-buy-now", function (e) {
    e.preventDefault();
    addItem("yoga_cart", productFromBtn($(this)), qtyFromPage());
    window.location.href = "/checkout";
  });
  $(document).on("click", ".yoga-add-wish", function () {
    addItem("yoga_wish", productFromBtn($(this)), 1);
  });
  $(document).on("click", ".yoga-remove", function () {
    var key = $(this).data("key");
    var id = $(this).data("id");
    store(key, load(key).filter(function (item) { return item.id !== id; }));
    render();
  });
  $(document).on("submit", ".yoga-checkout-form", function () {
    store("yoga_cart", []);
  });
  render();
})(jQuery);
