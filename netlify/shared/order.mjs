// Logica pura dell'ordine: valida carrello e consegna contro il catalogo e
// costruisce i parametri per Stripe Checkout. Nessun I/O: testabile ovunque.

export const MAX_QTY = 50;
export const MAX_LINES = 20;
const GIORNI = ["domenica", "lunedì", "martedì", "mercoledì", "giovedì", "venerdì", "sabato"];
const MESI = ["gennaio", "febbraio", "marzo", "aprile", "maggio", "giugno", "luglio", "agosto", "settembre", "ottobre", "novembre", "dicembre"];

export class OrderError extends Error {}

// ---------- Date (sempre nel fuso di Roma) ----------

export function romeToday(now = new Date()) {
  return new Intl.DateTimeFormat("en-CA", { timeZone: "Europe/Rome", year: "numeric", month: "2-digit", day: "2-digit" }).format(now);
}
function addDays(iso, n) {
  const d = new Date(iso + "T12:00:00Z");
  d.setUTCDate(d.getUTCDate() + n);
  return d.toISOString().slice(0, 10);
}
function weekday(iso) { return new Date(iso + "T12:00:00Z").getUTCDay(); }
function daysBetween(a, b) { return Math.round((new Date(b + "T12:00:00Z") - new Date(a + "T12:00:00Z")) / 86400000); }

export function formatDate(iso) {
  const d = new Date(iso + "T12:00:00Z");
  return `${GIORNI[d.getUTCDay()]} ${d.getUTCDate()} ${MESI[d.getUTCMonth()]}`;
}

/** true se la data è prenotabile: giorno giusto, preavviso rispettato, non chiusa. */
export function isBookable(catalog, iso, rule, now = new Date()) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(iso || "")) return false;
  const diff = daysBetween(romeToday(now), iso);
  if (diff < rule.lead_days || diff > rule.max_days) return false;
  if ((catalog.closed_dates || []).includes(iso)) return false;
  return rule.days.includes(weekday(iso));
}

/** Prossime date prenotabili (per test e per la pagina). */
export function nextDates(catalog, rule, count = 6, now = new Date()) {
  const out = [];
  const today = romeToday(now);
  for (let i = rule.lead_days; i <= rule.max_days && out.length < count; i++) {
    const iso = addDays(today, i);
    if (isBookable(catalog, iso, rule, now)) out.push(iso);
  }
  return out;
}

export function zoneForCap(catalog, cap) {
  const ld = catalog.local_delivery;
  if (!ld || !ld.enabled || !/^\d{5}$/.test(String(cap || ""))) return null;
  return ld.zones.find((z) => z.caps.includes(String(cap))) || null;
}

// ---------- Prezzi ----------

/**
 * items:    [{ id, qty }]
 * delivery: { mode: "ship" | "local" | "pickup", cap?, date? }
 */
export function priceCart(catalog, items, delivery = { mode: "ship" }, now = new Date()) {
  if (!Array.isArray(items) || items.length === 0) throw new OrderError("Il carrello è vuoto.");
  if (items.length > MAX_LINES) throw new OrderError("Troppi articoli nel carrello.");
  const byId = new Map(catalog.products.map((p) => [p.id, p]));
  const merged = new Map();
  for (const it of items) {
    const p = byId.get(it && it.id);
    if (!p || !p.available || !Number.isInteger(p.price_cents)) {
      throw new OrderError("Un prodotto nel carrello non è più disponibile. Aggiorna la pagina.");
    }
    const qty = Number(it.qty);
    if (!Number.isInteger(qty) || qty < 1) throw new OrderError("Quantità non valida.");
    merged.set(p.id, Math.min(MAX_QTY, (merged.get(p.id) || 0) + qty));
  }
  const lines = [...merged].map(([id, qty]) => ({ product: byId.get(id), qty }));
  const subtotal = lines.reduce((s, l) => s + l.product.price_cents * l.qty, 0);

  const mode = (delivery && delivery.mode) || "ship";
  let shipping, info;
  if (mode === "ship") {
    const fresh = lines.find((l) => l.product.local_only);
    if (fresh) throw new OrderError(`${fresh.product.name} è disponibile solo con consegna in Friuli o ritiro in laboratorio.`);
    const s = catalog.shipping;
    shipping = subtotal >= s.free_over_cents ? 0 : s.flat_cents;
    info = { mode };
  } else if (mode === "local") {
    const ld = catalog.local_delivery;
    if (!ld || !ld.enabled) throw new OrderError("La consegna in Friuli non è attiva.");
    const zone = zoneForCap(catalog, delivery.cap);
    if (!zone) throw new OrderError("Per questo CAP non abbiamo ancora un giro di consegna. Scegli la spedizione con corriere o il ritiro.");
    if (!isBookable(catalog, delivery.date, { ...ld, days: zone.days }, now)) {
      throw new OrderError("La data di consegna scelta non è più disponibile. Scegline un'altra.");
    }
    shipping = subtotal >= ld.free_over_cents ? 0 : ld.fee_cents;
    info = { mode, cap: String(delivery.cap), zone, date: delivery.date };
  } else if (mode === "pickup") {
    const pk = catalog.pickup;
    if (!pk || !pk.enabled) throw new OrderError("Il ritiro in laboratorio non è attivo.");
    if (!isBookable(catalog, delivery.date, pk, now)) throw new OrderError("Il giorno di ritiro scelto non è più disponibile. Scegline un altro.");
    shipping = 0;
    info = { mode, date: delivery.date };
  } else {
    throw new OrderError("Modalità di consegna non valida.");
  }
  return { lines, subtotal, shipping, total: subtotal + shipping, delivery: info };
}

// ---------- Stripe ----------

/** Parametri form-encoded per POST /v1/checkout/sessions. */
export function checkoutParams(catalog, priced, siteUrl) {
  const p = new URLSearchParams();
  const d = priced.delivery;
  p.append("mode", "payment");
  p.append("locale", "it");
  p.append("success_url", `${siteUrl}/grazie/?ordine={CHECKOUT_SESSION_ID}`);
  p.append("cancel_url", `${siteUrl}/shop/?annullato=1`);
  p.append("allow_promotion_codes", "true");
  p.append("billing_address_collection", "auto");
  p.append("phone_number_collection[enabled]", "true");
  p.append("tax_id_collection[enabled]", "true"); // Partita IVA per la fattura
  p.append("customer_creation", "always");

  priced.lines.forEach((l, i) => {
    const pr = l.product;
    const k = `line_items[${i}]`;
    p.append(`${k}[quantity]`, String(l.qty));
    p.append(`${k}[price_data][currency]`, "eur");
    p.append(`${k}[price_data][unit_amount]`, String(pr.price_cents));
    p.append(`${k}[price_data][product_data][name]`, `${pr.name} ${pr.weight || ""}`.trim());
    p.append(`${k}[price_data][product_data][description]`, pr.pack ? `Confezione: ${pr.pack}` : pr.short);
    p.append(`${k}[price_data][product_data][images][0]`, `${siteUrl}/img/${pr.image}`);
    p.append(`${k}[price_data][product_data][metadata][sku]`, pr.id);
  });

  const so = "shipping_options[0][shipping_rate_data]";
  const free = priced.shipping === 0;
  if (d.mode === "ship") {
    const s = catalog.shipping;
    s.countries.forEach((c, i) => p.append(`shipping_address_collection[allowed_countries][${i}]`, c));
    p.append(`${so}[type]`, "fixed_amount");
    p.append(`${so}[display_name]`, free ? `${s.label} — gratuita` : s.label);
    p.append(`${so}[fixed_amount][amount]`, String(priced.shipping));
    p.append(`${so}[fixed_amount][currency]`, "eur");
    p.append(`${so}[delivery_estimate][minimum][unit]`, "business_day");
    p.append(`${so}[delivery_estimate][minimum][value]`, String(s.days_min));
    p.append(`${so}[delivery_estimate][maximum][unit]`, "business_day");
    p.append(`${so}[delivery_estimate][maximum][value]`, String(s.days_max));
    p.append("custom_text[submit][message]", "Ogni dolce viene confezionato a mano nel nostro laboratorio di Azzida.");
  } else if (d.mode === "local") {
    p.append("shipping_address_collection[allowed_countries][0]", "IT");
    p.append(`${so}[type]`, "fixed_amount");
    p.append(`${so}[display_name]`, `Consegna diretta ${formatDate(d.date)}${free ? " — gratuita" : ""}`);
    p.append(`${so}[fixed_amount][amount]`, String(priced.shipping));
    p.append(`${so}[fixed_amount][currency]`, "eur");
    p.append("custom_text[shipping_address][message]", `Te la portiamo noi ${formatDate(d.date)}. Consegniamo al CAP ${d.cap} (${d.zone.name}) indicato nel carrello.`);
    p.append("custom_text[submit][message]", "Ti chiameremo il giorno della consegna per concordare l'orario.");
  } else {
    p.append("custom_text[submit][message]", `Ritiro in laboratorio ad Azzida, Via Algida 43, ${formatDate(d.date)}.`);
  }

  // Biglietto d'auguri e note, visibili nel pannello Stripe
  p.append("custom_fields[0][key]", "biglietto");
  p.append("custom_fields[0][label][type]", "custom");
  p.append("custom_fields[0][label][custom]", "Biglietto d'auguri (facoltativo)");
  p.append("custom_fields[0][type]", "text");
  p.append("custom_fields[0][optional]", "true");
  p.append("custom_fields[0][text][maximum_length]", "200");
  p.append("custom_fields[1][key]", "note");
  p.append("custom_fields[1][label][type]", "custom");
  p.append("custom_fields[1][label][custom]", d.mode === "pickup" ? "Orario preferito di ritiro (facoltativo)" : "Note per la consegna (facoltativo)");
  p.append("custom_fields[1][type]", "text");
  p.append("custom_fields[1][optional]", "true");
  p.append("custom_fields[1][text][maximum_length]", "200");

  const tipo = { ship: "Corriere", local: "Consegna diretta Friuli", pickup: "Ritiro in laboratorio" }[d.mode];
  p.append("metadata[consegna]", tipo);
  if (d.date) p.append("metadata[data]", `${d.date} (${formatDate(d.date)})`);
  if (d.mode === "local") {
    p.append("metadata[cap]", d.cap);
    p.append("metadata[zona]", d.zone.name);
  }
  p.append("metadata[ordine]", priced.lines.map((l) => `${l.qty}x ${l.product.id}`).join(", ").slice(0, 490));
  return p;
}
