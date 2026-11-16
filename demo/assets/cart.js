/* Carrello Giuditta Teresa — nessuna dipendenza.
   Prezzi, zone e date qui servono solo per la visualizzazione: tutto viene
   ricontrollato dal server (netlify/shared/order.mjs). */
(function () {
  "use strict";
  var DATA = window.GT || { products: [], shipping: {} };
  var KEY = "gt_cart_v1", DKEY = "gt_delivery_v1";
  var MAX_QTY = 50;
  var GIORNI = ["domenica", "lunedì", "martedì", "mercoledì", "giovedì", "venerdì", "sabato"];
  var MESI = ["gennaio", "febbraio", "marzo", "aprile", "maggio", "giugno", "luglio", "agosto", "settembre", "ottobre", "novembre", "dicembre"];
  var byId = {};
  DATA.products.forEach(function (p) { byId[p.id] = p; });

  function read(k, d) { try { return JSON.parse(localStorage.getItem(k) || "null") || d; } catch (e) { return d; } }
  function write(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }

  var cart = (function () {
    var raw = read(KEY, {}), clean = {};
    Object.keys(raw).forEach(function (id) {
      var q = parseInt(raw[id], 10);
      if (byId[id] && byId[id].available && q > 0) clean[id] = Math.min(q, MAX_QTY);
    });
    return clean;
  })();
  var delivery = read(DKEY, { mode: "ship", cap: "", date: "" });
  function save() { write(KEY, cart); write(DKEY, delivery); }

  function eur(c) { return (c / 100).toLocaleString("it-IT", { style: "currency", currency: "EUR" }); }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]; }); }
  function count() { return Object.keys(cart).reduce(function (n, id) { return n + cart[id]; }, 0); }
  function subtotal() { return Object.keys(cart).reduce(function (s, id) { return s + byId[id].price_cents * cart[id]; }, 0); }
  function hasFresh() { return Object.keys(cart).some(function (id) { return byId[id].local_only; }); }

  // ---- Date nel fuso di Roma (come sul server) ----
  function romeToday() {
    try { return new Intl.DateTimeFormat("en-CA", { timeZone: "Europe/Rome", year: "numeric", month: "2-digit", day: "2-digit" }).format(new Date()); }
    catch (e) { return new Date().toISOString().slice(0, 10); }
  }
  function addDays(iso, n) { var d = new Date(iso + "T12:00:00Z"); d.setUTCDate(d.getUTCDate() + n); return d.toISOString().slice(0, 10); }
  function fmt(iso) { var d = new Date(iso + "T12:00:00Z"); return GIORNI[d.getUTCDay()] + " " + d.getUTCDate() + " " + MESI[d.getUTCMonth()]; }
  function dates(rule, days, n) {
    var out = [], today = romeToday(), closed = DATA.closed_dates || [];
    for (var i = rule.lead_days; i <= rule.max_days && out.length < n; i++) {
      var iso = addDays(today, i);
      if (days.indexOf(new Date(iso + "T12:00:00Z").getUTCDay()) > -1 && closed.indexOf(iso) < 0) out.push(iso);
    }
    return out;
  }
  function zoneFor(cap) {
    var ld = DATA.local;
    if (!ld || !/^\d{5}$/.test(cap || "")) return null;
    for (var i = 0; i < ld.zones.length; i++) if (ld.zones[i].caps.indexOf(cap) > -1) return ld.zones[i];
    return null;
  }

  function shippingCost() {
    var sub = subtotal();
    if (delivery.mode === "pickup") return 0;
    if (delivery.mode === "local") return sub >= DATA.local.free_over_cents ? 0 : DATA.local.fee_cents;
    return sub >= DATA.shipping.free_over_cents ? 0 : DATA.shipping.flat_cents;
  }

  var drawer = document.getElementById("cart");
  var body = drawer && drawer.querySelector(".drawer-body");
  var foot = drawer && drawer.querySelector(".drawer-foot");
  var dl = foot && foot.querySelector(".delivery");

  // ---- Blocco consegna (costruito una volta) ----
  function buildDelivery() {
    if (!dl) return;
    var s = DATA.shipping, ld = DATA.local, pk = DATA.pickup;
    var h = '<fieldset><legend>Come vuoi ricevere i tuoi dolci?</legend>';
    h += '<label class="dl-opt"><input type="radio" name="dl" value="ship"><span><b>Corriere in tutta Italia</b><small>' + eur(s.flat_cents) + ' · gratis sopra ' + eur(s.free_over_cents) + '</small></span></label>';
    if (ld) {
      h += '<label class="dl-opt"><input type="radio" name="dl" value="local"><span><b>Consegna in Friuli: te la portiamo noi</b><small>' + eur(ld.fee_cents) + ' · gratis sopra ' + eur(ld.free_over_cents) + ' · su prenotazione</small></span></label>' +
        '<div class="dl-panel" data-for="local"><label class="sr" for="dl-cap">CAP di consegna</label>' +
        '<input id="dl-cap" inputmode="numeric" maxlength="5" autocomplete="postal-code" placeholder="Il tuo CAP">' +
        '<div class="dl-zone small"></div><select class="dl-date" id="dl-date-local" aria-label="Giorno di consegna"></select></div>';
    }
    if (pk) {
      h += '<label class="dl-opt"><input type="radio" name="dl" value="pickup"><span><b>Ritiro in laboratorio ad Azzida</b><small>Gratis · Via Algida 43</small></span></label>' +
        '<div class="dl-panel" data-for="pickup"><select class="dl-date" id="dl-date-pickup" aria-label="Giorno di ritiro"></select></div>';
    }
    dl.innerHTML = h + "</fieldset>";
    if ((delivery.mode === "local" && !ld) || (delivery.mode === "pickup" && !pk)) delivery.mode = "ship";
    var r = dl.querySelector('input[value="' + delivery.mode + '"]'); if (r) r.checked = true;
    var cap = dl.querySelector("#dl-cap"); if (cap) cap.value = delivery.cap || "";

    dl.addEventListener("change", function (e) {
      if (e.target.name === "dl") { delivery.mode = e.target.value; delivery.date = ""; }
      if (e.target.classList.contains("dl-date")) delivery.date = e.target.value;
      save(); render();
    });
    dl.addEventListener("input", function (e) {
      if (e.target.id === "dl-cap") {
        e.target.value = e.target.value.replace(/\D/g, "").slice(0, 5);
        delivery.cap = e.target.value; delivery.date = ""; save(); render();
      }
    });
  }

  function fillDates(sel, list, label) {
    var cur = delivery.date;
    if (list.indexOf(cur) < 0) cur = list[0] || "";
    delivery.date = cur;
    sel.innerHTML = list.map(function (d) { return '<option value="' + d + '"' + (d === cur ? " selected" : "") + ">" + label + " " + fmt(d) + "</option>"; }).join("");
    sel.hidden = !list.length;
  }

  function renderDelivery() {
    if (!dl) return { ok: true };
    var mode = delivery.mode, state = { ok: true, msg: "" };
    dl.querySelectorAll(".dl-panel").forEach(function (p) { p.hidden = p.getAttribute("data-for") !== mode; });
    if (mode === "local") {
      var zEl = dl.querySelector(".dl-zone"), sel = dl.querySelector("#dl-date-local");
      var z = zoneFor(delivery.cap);
      if (!delivery.cap || delivery.cap.length < 5) {
        zEl.textContent = "Inserisci il CAP per vedere i giorni di consegna."; sel.hidden = true; delivery.date = "";
        state = { ok: false, msg: "Inserisci il CAP di consegna." };
      } else if (!z) {
        zEl.innerHTML = 'Per questo CAP non abbiamo ancora un giro di consegna. <a href="consegna-in-friuli.php">Vedi le zone</a> o scegli il corriere.';
        sel.hidden = true; delivery.date = "";
        state = { ok: false, msg: "CAP fuori dalle nostre zone di consegna." };
      } else {
        zEl.textContent = "Zona: " + z.name + ". Scegli il giorno:";
        fillDates(sel, dates(DATA.local, z.days, 8), "Consegna");
      }
    } else if (mode === "pickup") {
      fillDates(dl.querySelector("#dl-date-pickup"), dates(DATA.pickup, DATA.pickup.days, 10), "Ritiro");
    }
    if (mode === "ship" && hasFresh()) state = { ok: false, msg: "Nel carrello ci sono dolci freschi disponibili solo con consegna in Friuli o ritiro." };
    if (mode !== "ship" && !delivery.date && state.ok) state = { ok: false, msg: "Scegli il giorno." };
    save();
    return state;
  }

  var dState = { ok: true };
  function render() {
    document.querySelectorAll(".cart-count").forEach(function (el) { var n = count(); el.textContent = n; el.setAttribute("data-n", n); });
    if (!drawer) return;
    var ids = Object.keys(cart);
    if (!ids.length) {
      body.innerHTML = '<div class="cart-empty"><p>Il carrello è vuoto.</p><a class="btn btn-sm" href="shop.php">Scopri i nostri dolci</a></div>';
      foot.hidden = true;
      return;
    }
    foot.hidden = false;
    body.innerHTML = ids.map(function (id) {
      var p = byId[id], q = cart[id];
      var meta = [p.weight, p.pack].filter(Boolean).join(" · ") + (p.local_only ? " · solo Friuli" : "");
      return '<div class="line" data-id="' + id + '">' +
        '<img src="img/' + esc(p.image) + '" alt="" width="72" height="56">' +
        '<div><h4>' + esc(p.name) + '</h4><div class="muted">' + esc(meta) + '</div>' +
        '<div class="qty"><button type="button" data-step="-1" aria-label="Diminuisci">−</button>' +
        '<input type="number" min="1" max="' + MAX_QTY + '" value="' + q + '" aria-label="Quantità">' +
        '<button type="button" data-step="1" aria-label="Aumenta">+</button></div></div>' +
        '<div class="line-price">' + eur(p.price_cents * q) + '<button type="button" class="line-remove">Rimuovi</button></div>' +
        '</div>';
    }).join("");

    dState = renderDelivery();
    var sub = subtotal(), ship = shippingCost();
    var bar = foot.querySelector(".ship-bar");
    var thr = delivery.mode === "local" ? DATA.local.free_over_cents : delivery.mode === "ship" ? DATA.shipping.free_over_cents : 0;
    if (!thr) { bar.innerHTML = "<b>Ritiro gratuito</b> nel nostro laboratorio"; }
    else {
      var missing = thr - sub, pct = Math.min(100, Math.round(sub / thr * 100));
      bar.innerHTML = (missing > 0 ? "Ti mancano <b>" + eur(missing) + "</b> per la consegna gratuita" : "<b>Consegna gratuita</b> inclusa") +
        '<div class="bar"><i style="width:' + pct + '%"></i></div>';
    }
    foot.querySelector(".js-subtotal").textContent = eur(sub);
    foot.querySelector(".js-shipping-label").textContent = delivery.mode === "pickup" ? "Ritiro" : delivery.mode === "local" ? "Consegna" : "Spedizione";
    foot.querySelector(".js-shipping").textContent = ship ? eur(ship) : "Gratis";
    foot.querySelector(".js-total").textContent = eur(sub + ship);
    var err = foot.querySelector(".cart-error");
    if (err.getAttribute("data-auto") === "1" || dState.ok) { err.textContent = dState.ok ? "" : dState.msg; err.setAttribute("data-auto", dState.ok ? "" : "1"); }
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
    if (p.local_only && delivery.mode === "ship") delivery.mode = DATA.local ? "local" : "pickup";
    var r = dl && dl.querySelector('input[value="' + delivery.mode + '"]'); if (r) r.checked = true;
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
    if (t.closest(".cart-btn") || t.closest("[data-open-cart]")) { e.preventDefault(); open(); return; }
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

  // ---- Checkout → Stripe ----
  var payBtn = document.getElementById("checkout-btn");
  if (payBtn) {
    payBtn.addEventListener("click", function () {
      var err = foot.querySelector(".cart-error");
      err.setAttribute("data-auto", "");
      if (!dState.ok) { err.textContent = dState.msg; return; }
      if (!document.getElementById("accept-terms").checked) { err.textContent = "Per procedere accetta le condizioni di vendita."; return; }
      err.textContent = "";
      payBtn.disabled = true;
      payBtn.textContent = "Attendi…";
      fetch("checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          items: Object.keys(cart).map(function (id) { return { id: id, qty: cart[id] }; }),
          delivery: { mode: delivery.mode, cap: delivery.mode === "local" ? delivery.cap : undefined, date: delivery.mode === "ship" ? undefined : delivery.date }
        })
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

  if (document.body.hasAttribute("data-clear-cart")) { cart = {}; save(); }

  // Verifica CAP nelle pagine (Consegna in Friuli, home)
  document.querySelectorAll("[data-cap-check]").forEach(function (btn) {
    var inp = document.getElementById(btn.getAttribute("data-cap-check"));
    var out = document.getElementById(btn.getAttribute("data-cap-check") + "-result");
    if (!inp || !out) return;
    function check() {
      var cap = inp.value.replace(/\D/g, "").slice(0, 5); inp.value = cap;
      if (cap.length < 5) { out.className = "cap-result"; out.textContent = ""; return; }
      var z = zoneFor(cap);
      if (!z) {
        out.className = "cap-result";
        out.innerHTML = "Per il CAP " + cap + ' non abbiamo ancora un giro di consegna: puoi ordinare con il corriere o <a href="contatti.php">chiederci</a> se passiamo dalle tue parti.';
        return;
      }
      var d = dates(DATA.local, z.days, 3).map(fmt);
      out.className = "cap-result ok";
      out.innerHTML = "Sì, consegniamo a casa tua! Zona <b>" + esc(z.name) + "</b>. Prossime consegne: " + d.join(", ") + '. <a href="shop.php">Scegli i tuoi dolci →</a>';
    }
    inp.addEventListener("input", check);
    btn.addEventListener("click", check);
  });

  buildDelivery();
  render();
})();
