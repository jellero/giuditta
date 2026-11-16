/* Carrello Giuditta Teresa — nessuna dipendenza.
   I prezzi qui servono solo per la visualizzazione: il totale reale
   viene ricalcolato dal server (netlify/functions/checkout.mjs). */
(function () {
  "use strict";
  var DATA = window.GT || { products: [], shipping: {} };
  var KEY = "gt_cart_v1";
  var MAX_QTY = 50;
  var byId = {};
  DATA.products.forEach(function (p) { byId[p.id] = p; });

  function load() {
    try {
      var raw = JSON.parse(localStorage.getItem(KEY) || "{}");
      var clean = {};
      Object.keys(raw).forEach(function (id) {
        var q = parseInt(raw[id], 10);
        if (byId[id] && byId[id].available && q > 0) clean[id] = Math.min(q, MAX_QTY);
      });
      return clean;
    } catch (e) { return {}; }
  }
  var cart = load();
  function save() { try { localStorage.setItem(KEY, JSON.stringify(cart)); } catch (e) {} }

  function eur(cents) {
    return (cents / 100).toLocaleString("it-IT", { style: "currency", currency: "EUR" });
  }
  function count() { return Object.keys(cart).reduce(function (n, id) { return n + cart[id]; }, 0); }
  function subtotal() { return Object.keys(cart).reduce(function (s, id) { return s + byId[id].price_cents * cart[id]; }, 0); }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]; }); }

  var drawer = document.getElementById("cart");
  var body = drawer && drawer.querySelector(".drawer-body");
  var foot = drawer && drawer.querySelector(".drawer-foot");

  function render() {
    document.querySelectorAll(".cart-count").forEach(function (el) {
      var n = count(); el.textContent = n; el.setAttribute("data-n", n);
    });
    if (!drawer) return;
    var ids = Object.keys(cart);
    if (!ids.length) {
      body.innerHTML = '<div class="cart-empty"><p>Il carrello è vuoto.</p><a class="btn btn-sm" href="/shop/">Scopri i nostri dolci</a></div>';
      foot.hidden = true;
      return;
    }
    foot.hidden = false;
    body.innerHTML = ids.map(function (id) {
      var p = byId[id], q = cart[id];
      return '<div class="line" data-id="' + id + '">' +
        '<img src="/img/' + esc(p.image) + '" alt="" width="72" height="56">' +
        '<div><h4>' + esc(p.name) + '</h4><div class="muted">' + esc(p.weight + " · " + p.pack) + '</div>' +
        '<div class="qty"><button type="button" data-step="-1" aria-label="Diminuisci">−</button>' +
        '<input type="number" min="1" max="' + MAX_QTY + '" value="' + q + '" aria-label="Quantità">' +
        '<button type="button" data-step="1" aria-label="Aumenta">+</button></div></div>' +
        '<div class="line-price">' + eur(p.price_cents * q) + '<button type="button" class="line-remove">Rimuovi</button></div>' +
        '</div>';
    }).join("");

    var sub = subtotal();
    var s = DATA.shipping;
    var missing = s.free_over_cents - sub;
    var pct = Math.min(100, Math.round((sub / s.free_over_cents) * 100));
    var shipTxt = missing > 0
      ? "Ti mancano <b>" + eur(missing) + "</b> per la spedizione gratuita"
      : "<b>Spedizione gratuita</b> inclusa nel tuo ordine";
    foot.querySelector(".ship-bar").innerHTML = shipTxt + '<div class="bar"><i style="width:' + pct + '%"></i></div>';
    foot.querySelector(".js-subtotal").textContent = eur(sub);
    foot.querySelector(".js-shipping").textContent = missing > 0 ? eur(s.flat_cents) : "Gratis";
  }

  function setQty(id, q) {
    q = parseInt(q, 10);
    if (!byId[id]) return;
    if (!q || q < 1) delete cart[id]; else cart[id] = Math.min(q, MAX_QTY);
    save(); render();
  }

  function add(id, q) {
    var p = byId[id];
    if (!p || !p.available) return;
    setQty(id, (cart[id] || 0) + (q || 1));
    toast(p.name + " aggiunto al carrello");
    open();
  }

  function open() { document.documentElement.classList.add("cart-open"); drawer && drawer.setAttribute("aria-hidden", "false"); }
  function close() { document.documentElement.classList.remove("cart-open"); drawer && drawer.setAttribute("aria-hidden", "true"); }

  var toastEl;
  function toast(msg) {
    if (!toastEl) { toastEl = document.createElement("div"); toastEl.className = "toast"; toastEl.setAttribute("role", "status"); document.body.appendChild(toastEl); }
    toastEl.textContent = msg; toastEl.classList.add("show");
    clearTimeout(toast.t); toast.t = setTimeout(function () { toastEl.classList.remove("show"); }, 2200);
  }

  document.addEventListener("click", function (e) {
    var t = e.target;
    var addBtn = t.closest("[data-add]");
    if (addBtn) {
      e.preventDefault();
      var qInput = addBtn.getAttribute("data-qty-from") && document.querySelector(addBtn.getAttribute("data-qty-from"));
      add(addBtn.getAttribute("data-add"), qInput ? parseInt(qInput.value, 10) || 1 : 1);
      return;
    }
    if (t.closest(".cart-btn")) { e.preventDefault(); open(); return; }
    if (t.closest(".drawer-close") || t.classList.contains("drawer-backdrop")) { close(); return; }
    var line = t.closest(".line");
    if (line) {
      var id = line.getAttribute("data-id");
      if (t.closest("[data-step]")) setQty(id, cart[id] + parseInt(t.closest("[data-step]").getAttribute("data-step"), 10));
      if (t.closest(".line-remove")) setQty(id, 0);
    }
    var stepper = t.closest(".pdp-buy .qty [data-step]");
    if (stepper) {
      var inp = stepper.parentNode.querySelector("input");
      inp.value = Math.max(1, Math.min(MAX_QTY, (parseInt(inp.value, 10) || 1) + parseInt(stepper.getAttribute("data-step"), 10)));
    }
    if (t.closest(".menu-btn")) {
      var nav = document.querySelector(".nav");
      var isOpen = nav.classList.toggle("open");
      t.closest(".menu-btn").setAttribute("aria-expanded", isOpen);
    }
  });

  document.addEventListener("change", function (e) {
    var line = e.target.closest(".line");
    if (line && e.target.matches("input")) setQty(line.getAttribute("data-id"), e.target.value);
  });
  document.addEventListener("keydown", function (e) { if (e.key === "Escape") close(); });

  // Checkout → Stripe
  var payBtn = document.getElementById("checkout-btn");
  if (payBtn) {
    payBtn.addEventListener("click", function () {
      var err = foot.querySelector(".cart-error");
      err.textContent = "";
      if (!document.getElementById("accept-terms").checked) {
        err.textContent = "Per procedere accetta le condizioni di vendita.";
        return;
      }
      payBtn.disabled = true;
      payBtn.textContent = "Attendi…";
      fetch("/api/checkout", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ items: Object.keys(cart).map(function (id) { return { id: id, qty: cart[id] }; }) })
      })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
          if (res.ok && res.j.url) { window.location.href = res.j.url; return; }
          var se = new Error(res.j.error || ""); se.fromServer = true; throw se;
        })
        .catch(function (e) {
          // Mostriamo solo i messaggi scritti da noi sul server, mai errori tecnici
          err.textContent = (e && e.fromServer && e.message ? e.message + " " : "") +
            "Non è stato possibile avviare il pagamento. Riprova o scrivici a info@giudittateresa.com.";
          payBtn.disabled = false;
          payBtn.textContent = "Vai al pagamento sicuro";
        });
    });
  }

  // Pagina di conferma: svuota il carrello
  if (document.body.hasAttribute("data-clear-cart")) { cart = {}; save(); }

  render();
})();
