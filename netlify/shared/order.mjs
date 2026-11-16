// Logica pura dell'ordine: valida il carrello contro il catalogo e costruisce
// i parametri per Stripe Checkout. Nessun I/O, così è testabile ovunque.

export const MAX_QTY = 50;
export const MAX_LINES = 20;

export class OrderError extends Error {}

/** Valida gli articoli del carrello e restituisce le righe con prezzo dal catalogo. */
export function priceCart(catalog, items) {
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
  const s = catalog.shipping;
  const shipping = subtotal >= s.free_over_cents ? 0 : s.flat_cents;
  return { lines, subtotal, shipping, total: subtotal + shipping };
}

/** Parametri form-encoded per POST /v1/checkout/sessions. */
export function checkoutParams(catalog, priced, siteUrl) {
  const p = new URLSearchParams();
  const s = catalog.shipping;
  p.append("mode", "payment");
  p.append("locale", "it");
  p.append("success_url", `${siteUrl}/grazie/?ordine={CHECKOUT_SESSION_ID}`);
  p.append("cancel_url", `${siteUrl}/shop/?annullato=1`);
  p.append("allow_promotion_codes", "true");
  p.append("billing_address_collection", "auto");
  p.append("phone_number_collection[enabled]", "true");
  // Le aziende possono inserire la Partita IVA per la fattura
  p.append("tax_id_collection[enabled]", "true");
  p.append("customer_creation", "always");
  s.countries.forEach((c, i) => p.append(`shipping_address_collection[allowed_countries][${i}]`, c));

  priced.lines.forEach((l, i) => {
    const pr = l.product;
    const k = `line_items[${i}]`;
    p.append(`${k}[quantity]`, String(l.qty));
    p.append(`${k}[price_data][currency]`, "eur");
    p.append(`${k}[price_data][unit_amount]`, String(pr.price_cents));
    p.append(`${k}[price_data][product_data][name]`, `${pr.name} ${pr.weight}`.trim());
    p.append(`${k}[price_data][product_data][description]`, pr.pack ? `Confezione: ${pr.pack}` : pr.short);
    p.append(`${k}[price_data][product_data][images][0]`, `${siteUrl}/img/${pr.image}`);
    p.append(`${k}[price_data][product_data][metadata][sku]`, pr.id);
  });

  const free = priced.shipping === 0;
  const so = "shipping_options[0][shipping_rate_data]";
  p.append(`${so}[type]`, "fixed_amount");
  p.append(`${so}[display_name]`, free ? `${s.label} — gratuita` : s.label);
  p.append(`${so}[fixed_amount][amount]`, String(priced.shipping));
  p.append(`${so}[fixed_amount][currency]`, "eur");
  p.append(`${so}[delivery_estimate][minimum][unit]`, "business_day");
  p.append(`${so}[delivery_estimate][minimum][value]`, String(s.days_min));
  p.append(`${so}[delivery_estimate][maximum][unit]`, "business_day");
  p.append(`${so}[delivery_estimate][maximum][value]`, String(s.days_max));

  // Biglietto d'auguri e note di consegna, visibili nel pannello Stripe
  p.append("custom_fields[0][key]", "biglietto");
  p.append("custom_fields[0][label][type]", "custom");
  p.append("custom_fields[0][label][custom]", "Biglietto d'auguri (facoltativo)");
  p.append("custom_fields[0][type]", "text");
  p.append("custom_fields[0][optional]", "true");
  p.append("custom_fields[0][text][maximum_length]", "200");
  p.append("custom_fields[1][key]", "consegna");
  p.append("custom_fields[1][label][type]", "custom");
  p.append("custom_fields[1][label][custom]", "Note per la consegna (facoltativo)");
  p.append("custom_fields[1][type]", "text");
  p.append("custom_fields[1][optional]", "true");
  p.append("custom_fields[1][text][maximum_length]", "200");

  p.append("custom_text[submit][message]", "Ogni dolce viene confezionato a mano nel nostro laboratorio di Azzida.");
  p.append("metadata[ordine]", priced.lines.map((l) => `${l.qty}x ${l.product.id}`).join(", ").slice(0, 490));
  return p;
}
