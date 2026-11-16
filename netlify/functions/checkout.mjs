// POST /api/checkout  { items: [{ id, qty }] }  ->  { url }
// Crea una sessione Stripe Checkout. Nessuna dipendenza npm: usa l'API REST.
// Variabili d'ambiente (Netlify → Site configuration → Environment variables):
//   STRIPE_SECRET_KEY   sk_test_... per le prove, sk_live_... in produzione
//   SITE_URL            facoltativa, es. https://www.giudittateresa.com
import catalog from "../../catalog.json" with { type: "json" };
import { priceCart, checkoutParams, OrderError } from "../shared/order.mjs";

const json = (status, body) =>
  new Response(JSON.stringify(body), { status, headers: { "Content-Type": "application/json" } });

export default async (req) => {
  if (req.method !== "POST") return json(405, { error: "Metodo non consentito." });

  const key = process.env.STRIPE_SECRET_KEY;
  if (!key) return json(500, { error: "Pagamenti non configurati." });

  let body;
  try { body = await req.json(); } catch { return json(400, { error: "Richiesta non valida." }); }

  let priced;
  try {
    priced = priceCart(catalog, body && body.items);
  } catch (e) {
    if (e instanceof OrderError) return json(400, { error: e.message });
    throw e;
  }

  const siteUrl = (process.env.SITE_URL || process.env.URL || new URL(req.url).origin).replace(/\/$/, "");
  const res = await fetch("https://api.stripe.com/v1/checkout/sessions", {
    method: "POST",
    headers: {
      Authorization: `Bearer ${key}`,
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: checkoutParams(catalog, priced, siteUrl),
  });
  const data = await res.json();
  if (!res.ok) {
    console.error("Stripe error", res.status, data && data.error);
    return json(502, { error: "Il servizio di pagamento non risponde." });
  }
  return json(200, { url: data.url });
};

export const config = { path: "/api/checkout" };
