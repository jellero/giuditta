#!/usr/bin/env python3
"""Genera il sito statico in public/ a partire da catalog.json e src/.

Uso:  python tools/build.py
Nessuna dipendenza esterna (solo libreria standard di Python 3.8+).
"""
import html
import json
import shutil
import sys
from datetime import date
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "src"
OUT = ROOT / "public"
SITE = "https://www.giudittateresa.com"
CAT = json.loads((ROOT / "catalog.json").read_text(encoding="utf-8"))
PRODUCTS = CAT["products"]
ON_REQUEST = CAT["on_request"]
SHIP = CAT["shipping"]
TODAY = date.today().isoformat()

BIZ = {
    "name": "Giuditta Teresa",
    "street": "Via Algida, 43 — Località Azzida",
    "zip": "33049",
    "city": "San Pietro al Natisone",
    "prov": "UD",
    "phone": "+39 0432 727681",
    "phone_display": "0432 727681",
    "email": "info@giudittateresa.com",
    "vat": "02707840308",
    "facebook": "https://www.facebook.com/gubanagiudittateresa/",
    "instagram": "",  # es. "https://www.instagram.com/gubanagiudittateresa/"
    "maps": "https://www.google.com/maps/search/?api=1&query=Giuditta+Teresa+Gubana+Azzida+San+Pietro+al+Natisone",
}

AWARDS = [
    {"year": "2016", "title": "Primo premio al Gubana Day", "img": "premio-gubana-day.jpg",
     "text": "Tra 14 dolci dei principali gubanifici della regione e dei migliori produttori di panforte senese, scelti a caso dalla produzione normale, la giuria tecnica e quella popolare hanno premiato la nostra Gubana."},
    {"year": "2012", "title": "Vittoria al Gubana Day", "img": "stampa-quantobasta.jpg",
     "text": "La prima vittoria al Gubana Day, raccontata dalla rivista Quanto Basta, che ha dedicato la copertina a Silvana Chiabai."},
    {"year": "2018", "title": "Corriere della Sera", "img": "stampa-corriere.jpg",
     "text": "Il Corriere Extra racconta la Colombana, il dolce di Pasqua inventato da Silvana con il ripieno della Gubana."},
    {"year": "2016", "title": "Gusti di Frontiera, Gorizia", "img": "premio-raspelli.jpg",
     "text": "Abbiamo omaggiato con una nostra Gubana Edoardo Raspelli, premiato per aver fatto conoscere in Italia e nel mondo i prodotti di questa terra."},
]

FAQ = [
    ("Dove spedite?", f"In tutta Italia, compresi San Marino e Città del Vaticano, con corriere espresso. La consegna richiede di solito {SHIP['days_min']}–{SHIP['days_max']} giorni lavorativi dalla spedizione. Per l'estero scrivici: valutiamo ogni richiesta."),
    ("Quanto costa la spedizione?", f"{SHIP['flat_cents']/100:.0f} € per ordine, gratuita sopra i {SHIP['free_over_cents']/100:.0f} €."),
    ("Quando spedite?", "Spediamo dal lunedì al mercoledì, così i dolci non restano fermi in magazzino nel fine settimana e arrivano freschi come escono dal nostro forno."),
    ("Posso mandarla in regalo con un biglietto?", "Sì. Durante il pagamento puoi indicare un indirizzo di consegna diverso dal tuo e scrivere il testo del biglietto d'auguri: lo scriviamo a mano e lo mettiamo nel pacco."),
    ("Come si conserva la Gubana?", "In un luogo fresco e asciutto, lontano da fonti di calore, nella sua confezione. La data di scadenza è riportata sull'etichetta."),
    ("Come posso pagare?", "Con carta di credito o debito, Apple Pay, Google Pay e PayPal, su pagina sicura Stripe. Non vediamo né conserviamo i dati della tua carta."),
    ("Serve la fattura?", "Durante il pagamento puoi inserire la Partita IVA e i dati di fatturazione. Per ordini aziendali consulta la pagina dedicata alle aziende."),
]

e = html.escape


def eur(cents):
    return f"{cents/100:,.2f} €".replace(",", "X").replace(".", ",").replace("X", ".")


def product_url(p):
    return f"/prodotti/{p['id']}/"


def full_name(p):
    return f"{p['name']} {p['weight']}"


ICON = {
    "cart": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 8h14l-1.2 11.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>',
    "menu": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
    "award": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="m8.5 14-1.5 7 5-3 5 3-1.5-7"/></svg>',
    "hand": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 3c3 3 5 5.5 5 9a5 5 0 0 1-10 0c0-3.5 2-6 5-9Z"/><path d="M12 21v-6"/></svg>',
    "home": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/></svg>',
    "truck": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/></svg>',
    "fb": '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8v3h2.6V21h2.9Z"/></svg>',
    "ig": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r=".8" fill="currentColor"/></svg>',
}

NAV = [
    ("/shop/", "I nostri dolci"),
    ("/la-nostra-storia/", "La nostra storia"),
    ("/premi-e-stampa/", "Premi"),
    ("/aziende/", "Aziende e negozi"),
    ("/contatti/", "Contatti"),
]

ORG_LD = {
    "@context": "https://schema.org",
    "@type": "Bakery",
    "@id": SITE + "/#azienda",
    "name": BIZ["name"],
    "description": "Gubana artigianale delle Valli del Natisone, prodotta ad Azzida dal 1981.",
    "url": SITE + "/",
    "logo": SITE + "/img/logo.png",
    "image": SITE + "/img/hero.jpg",
    "telephone": BIZ["phone"],
    "email": BIZ["email"],
    "vatID": "IT" + BIZ["vat"],
    "foundingDate": "1981",
    "address": {"@type": "PostalAddress", "streetAddress": "Via Algida, 43, Località Azzida",
                "postalCode": BIZ["zip"], "addressLocality": BIZ["city"],
                "addressRegion": BIZ["prov"], "addressCountry": "IT"},
    "sameAs": [u for u in (BIZ["facebook"], BIZ["instagram"]) if u],
}


def ld(obj):
    return '<script type="application/ld+json">' + json.dumps(obj, ensure_ascii=False).replace("</", "<\\/") + "</script>"


def layout(path, title, desc, content, image="hero.jpg", extra_ld=(), body_attr="", noindex=False):
    current = ' aria-current="page"'
    nav = "".join(
        f'<a href="{u}"{current if path.startswith(u) else ""}>{e(t)}</a>' for u, t in NAV
    )
    js_data = {
        "products": [{k: p.get(k) for k in ("id", "name", "weight", "pack", "price_cents", "image", "available")} for p in PRODUCTS],
        "shipping": {k: SHIP[k] for k in ("flat_cents", "free_over_cents")},
    }
    lds = "".join(ld(x) for x in (ORG_LD, *extra_ld))
    social = "".join(
        f'<a href="{u}" rel="noopener" aria-label="{n}">{ICON[i]}</a>'
        for u, n, i in ((BIZ["facebook"], "Facebook", "fb"), (BIZ["instagram"], "Instagram", "ig")) if u
    )
    full_title = title if "Giuditta Teresa" in title else f"{title} | Giuditta Teresa"
    return f"""<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{e(full_title)}</title>
<meta name="description" content="{e(desc)}">
<link rel="canonical" href="{SITE}{path}">
{'<meta name="robots" content="noindex">' if noindex else ''}
<meta property="og:type" content="website">
<meta property="og:locale" content="it_IT">
<meta property="og:site_name" content="Giuditta Teresa">
<meta property="og:title" content="{e(full_title)}">
<meta property="og:description" content="{e(desc)}">
<meta property="og:url" content="{SITE}{path}">
<meta property="og:image" content="{SITE}/img/{image}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#2b3521">
<link rel="icon" href="/img/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;1,9..144,400;1,9..144,500&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css">
{lds}
</head>
<body{(' ' + body_attr) if body_attr else ''}>
<a class="skip" href="#main">Vai al contenuto</a>
<div class="topbar">Spedizione <strong>gratuita</strong> sopra i {SHIP['free_over_cents']//100} € · Fatta a mano ad Azzida, Valli del Natisone</div>
<header class="header">
  <div class="wrap">
    <button class="menu-btn" aria-label="Menu" aria-expanded="false">{ICON['menu']}</button>
    <a class="brand" href="/">Giuditta Teresa<small>Gubana dal 1981</small></a>
    <nav class="nav" aria-label="Principale">{nav}</nav>
    <button class="cart-btn" aria-label="Carrello">{ICON['cart']}<span class="cart-count" data-n="0">0</span></button>
  </div>
</header>
<main id="main">
{content}
</main>
<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <img class="footer-logo" src="/img/logo.png" alt="Giuditta Teresa" width="309" height="80" loading="lazy">
        <p>La Gubana delle Valli del Natisone, fatta a mano dalla nostra famiglia dal 1981.</p>
        <div class="social">{social}</div>
      </div>
      <div>
        <h4>Negozio</h4>
        <ul>
          <li><a href="/shop/">Tutti i dolci</a></li>
          <li><a href="/prodotti/gubana-1000/">La Gubana</a></li>
          <li><a href="/aziende/">Regali aziendali</a></li>
          <li><a href="/aziende/#rivenditori">Rivenditori</a></li>
        </ul>
      </div>
      <div>
        <h4>Informazioni</h4>
        <ul>
          <li><a href="/la-nostra-storia/">La nostra storia</a></li>
          <li><a href="/premi-e-stampa/">Premi e stampa</a></li>
          <li><a href="/condizioni-di-vendita/">Spedizioni e condizioni</a></li>
          <li><a href="https://www.iubenda.com/privacy-policy/21456511" rel="noopener">Privacy</a></li>
        </ul>
      </div>
      <div>
        <h4>Laboratorio</h4>
        <ul>
          <li>{e(BIZ['street'])}</li>
          <li>{BIZ['zip']} {e(BIZ['city'])} ({BIZ['prov']})</li>
          <li><a href="tel:{BIZ['phone'].replace(' ', '')}">{BIZ['phone_display']}</a></li>
          <li><a href="mailto:{BIZ['email']}">{BIZ['email']}</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© {date.today().year} Giuditta Teresa · P. IVA {BIZ['vat']}</span>
      <span>Pagamenti sicuri con Stripe</span>
    </div>
  </div>
</footer>

<div class="drawer-backdrop"></div>
<aside class="drawer" id="cart" aria-hidden="true" aria-label="Carrello">
  <div class="drawer-head"><h2>Il tuo carrello</h2><button class="drawer-close" aria-label="Chiudi">×</button></div>
  <div class="drawer-body"></div>
  <div class="drawer-foot" hidden>
    <div class="ship-bar"></div>
    <div class="totals"><span>Subtotale</span><span class="js-subtotal"></span></div>
    <div class="totals small"><span>Spedizione</span><span class="js-shipping"></span></div>
    <label class="terms"><input type="checkbox" id="accept-terms"> <span>Ho letto e accetto le <a href="/condizioni-di-vendita/" target="_blank">condizioni di vendita</a>.</span></label>
    <button class="btn btn-block" id="checkout-btn">Vai al pagamento sicuro</button>
    <div class="cart-error" role="alert"></div>
    <div class="pay-icons"><span>Carta</span><span>Apple Pay</span><span>Google Pay</span><span>PayPal</span></div>
  </div>
</aside>
<script>window.GT={json.dumps(js_data, ensure_ascii=False)};</script>
<script src="/assets/cart.js" defer></script>
</body>
</html>
"""


# ---------- Componenti ----------

def product_card(p, heading="h3"):
    badge = f'<span class="badge">{e(p["badge"])}</span>' if p.get("badge") else (
        f'<span class="badge">{e(p["seasonal"])}</span>' if p.get("seasonal") else "")
    meta = " · ".join(x for x in (p.get("subtitle"), p["weight"], p.get("pack")) if x)
    if p["available"]:
        cta = f'<button class="btn btn-sm" data-add="{p["id"]}">Aggiungi</button>'
    else:
        cta = '<span class="muted small">Non disponibile ora</span>'
    return f"""<article class="product">
  <div class="product-img">{badge}<img src="/img/{p['image']}" alt="{e(full_name(p))} Giuditta Teresa" width="855" height="570" loading="lazy"></div>
  <div class="product-body">
    <{heading}><a href="{product_url(p)}">{e(p['name'])}</a></{heading}>
    <div class="product-meta">{e(meta)}</div>
    <p class="product-short">{e(p['short'])}</p>
    <div class="product-foot"><span class="price">{eur(p['price_cents'])}</span>{cta}</div>
  </div>
</article>"""


def request_items():
    return "".join(
        f"""<div class="request-item"><img src="/img/{r['image']}" alt="{e(r['name'])}" width="855" height="570" loading="lazy">
<h3>{e(r['name'])}</h3><p>{e(r['short'])}</p></div>"""
        for r in ON_REQUEST
    )


def awards_html(items):
    return "".join(
        f"""<article class="award"><img src="/img/{a['img']}" alt="{e(a['title'])}" loading="lazy">
<div><span class="year">{a['year']}</span><h3>{e(a['title'])}</h3><p>{e(a['text'])}</p></div></article>"""
        for a in items
    )


def faq_html():
    items = "".join(f"<details><summary>{e(q)}</summary><div>{e(a)}</div></details>" for q, a in FAQ)
    return f'<div class="details faq">{items}</div>'


FAQ_LD = {
    "@context": "https://schema.org", "@type": "FAQPage",
    "mainEntity": [{"@type": "Question", "name": q, "acceptedAnswer": {"@type": "Answer", "text": a}} for q, a in FAQ],
}

STEPS = f"""<div class="steps">
  <div class="step"><img src="/img/fase-ripieno.jpg" alt="Preparazione del ripieno con i liquori" width="340" height="340" loading="lazy">
    <h3>Il ripieno</h3><p>Gli ingredienti, selezionati e puliti a mano, vengono mescolati e bagnati con i liquori secondo la nostra ricetta, fino alla giusta umidità e consistenza.</p></div>
  <div class="step"><img src="/img/fase-formatura.jpg" alt="Formatura a mano delle gubane" width="340" height="340" loading="lazy">
    <h3>La formatura</h3><p>La pasta viene stesa, farcita e arrotolata su se stessa, poi acciambellata nella formella. Ultima carezza prima del forno: l'impasto zuccherino.</p></div>
  <div class="step"><img src="/img/fase-forno.jpg" alt="Gubane appena sfornate" width="340" height="340" loading="lazy">
    <h3>La cottura</h3><p>Solo all'uscita dal forno si scopre se l'impasto era ben lievitato e il ripieno al punto giusto. Dopo tanti anni, ogni infornata è ancora una sfida.</p></div>
</div>"""


# ---------- Pagine ----------

def page_home():
    featured = [p for p in PRODUCTS if p.get("featured") and p["available"]][:4]
    content = f"""
<section class="hero">
  <img src="/img/hero.jpg" alt="Gubana e Strucchi Giuditta Teresa sulla tavola" width="1600" height="890" fetchpriority="high">
  <div class="wrap">
    <span class="eyebrow">Azzida · Valli del Natisone · dal 1981</span>
    <h1>La Gubana, come si fa da noi.</h1>
    <p>Un ripieno generoso, una ricetta di famiglia mai cambiata, ogni dolce formato a mano nel nostro piccolo laboratorio.</p>
    <div class="hero-actions">
      <a class="btn btn-light" href="/shop/">Scegli i tuoi dolci</a>
      <a class="btn btn-ghost" href="/la-nostra-storia/">La nostra storia</a>
    </div>
  </div>
</section>

<section class="trust" aria-label="Perché sceglierci">
  <ul class="wrap">
    <li>{ICON['award']}<span><b>Primo premio</b>Gubana Day 2012 e 2016</span></li>
    <li>{ICON['hand']}<span><b>Fatta a mano</b>Un dolce alla volta</span></li>
    <li>{ICON['home']}<span><b>Ricetta di famiglia</b>Dal 1981 ad Azzida</span></li>
    <li>{ICON['truck']}<span><b>Spedizione gratuita</b>sopra i {SHIP['free_over_cents']//100} €</span></li>
  </ul>
</section>

<section class="section">
  <div class="wrap split">
    <div>
      <span class="eyebrow">Noi crediamo nel</span>
      <h2>Segreto del ripieno</h2>
      <p class="lead">La ricetta si tramanda nella nostra famiglia da generazioni, e ancora oggi si basa su una promessa fatta a mamma Lidia: mantenere fede alla qualità.</p>
      <p>Perché è il ripieno a dare alla Gubana quel sapore ricco e pieno. Un tempo la sua abbondanza era un augurio di buona fortuna per la famiglia; per noi è una regola che non si discute.</p>
      <p><a href="/la-nostra-storia/">Scopri come nasce una Gubana →</a></p>
    </div>
    <img src="/img/lab-composizione.jpg" alt="Gubana tagliata con il ripieno in vista" width="1600" height="1089" loading="lazy">
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Dal nostro forno</span>
      <h2>I nostri dolci</h2>
      <p class="muted">Pochi dolci, fatti bene. Abbiamo cominciato con la Gubana e negli anni abbiamo aggiunto solo ciò che nasce dallo stesso ripieno e dalla stessa cura.</p>
    </div>
    <div class="grid">{''.join(product_card(p) for p in featured)}</div>
    <p class="center" style="margin-top:36px"><a class="btn btn-ghost" href="/shop/">Vedi tutti i dolci</a></p>
  </div>
</section>

<section class="section section-dark">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Nel laboratorio</span>
      <h2>Come nasce la regina dei dolci friulani</h2>
    </div>
    {STEPS}
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Premi e stampa</span>
      <h2>Scelta dalle giurie, e da chi la assaggia</h2>
    </div>
    <div class="awards">{awards_html(AWARDS[:3])}</div>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap split reverse">
    <div>
      <span class="eyebrow">Per aziende e negozi</span>
      <h2>Un regalo che racconta un territorio</h2>
      <p class="lead">Regali di Natale per clienti e collaboratori, confezioni per eventi, fornitura per botteghe, enoteche e hotel.</p>
      <p>Prepariamo ogni ordine a mano, con biglietto personalizzato e spedizione anche a più indirizzi.</p>
      <a class="btn" href="/aziende/">Richiedi il listino aziende</a>
    </div>
    <img src="/img/strucchi-scatola.jpg" alt="Confezione regalo di Strucchi" width="855" height="570" loading="lazy">
  </div>
</section>

<section class="section">
  <div class="wrap narrow center">
    <blockquote class="quote">“I dolci si mangiano alla fine del pasto, perché dopo il corpo è necessario nutrire l'anima.”<cite>Il nostro motto</cite></blockquote>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="section-head center center"><span class="eyebrow">Domande frequenti</span><h2>Prima di ordinare</h2></div>
    {faq_html()}
  </div>
</section>
"""
    return layout("/", "Gubana artigianale delle Valli del Natisone dal 1981 | Giuditta Teresa",
                  "Gubana, Strucchi e Colombana fatti a mano ad Azzida, Valli del Natisone, dal 1981. Primo premio al Gubana Day. Spedizione in tutta Italia, gratuita sopra 120 €.",
                  content, extra_ld=[FAQ_LD])


def page_shop():
    content = f"""
<section class="page-hero"><div class="wrap">
  <span class="eyebrow">Shop</span>
  <h1>I nostri dolci</h1>
  <p class="lead narrow">Tutto quello che esce dal nostro forno, preparato a mano e spedito in tutta Italia. Spedizione gratuita sopra i {SHIP['free_over_cents']//100} €.</p>
</div></section>
<section style="padding-bottom:80px"><div class="wrap">
  <div class="grid">{''.join(product_card(p, "h2") for p in PRODUCTS)}</div>
</div></section>
<section class="section section-alt"><div class="wrap">
  <div class="section-head">
    <span class="eyebrow">Su ordinazione</span>
    <h2>Da ritirare in laboratorio</h2>
    <p class="muted">Questi dolci li prepariamo solo su richiesta. Chiamaci al <a href="tel:{BIZ['phone'].replace(' ', '')}">{BIZ['phone_display']}</a> o <a href="/contatti/">scrivici</a> per prenotarli.</p>
  </div>
  <div class="request-list">{request_items()}</div>
</div></section>
"""
    item_list = {
        "@context": "https://schema.org", "@type": "ItemList",
        "itemListElement": [{"@type": "ListItem", "position": i + 1, "url": SITE + product_url(p), "name": full_name(p)}
                            for i, p in enumerate(PRODUCTS)],
    }
    return layout("/shop/", "Acquista Gubana online", "Acquista online Gubana, Strucchi, Gemme e Cuor di Gubana fatti a mano ad Azzida. Spedizione in tutta Italia, gratuita sopra 120 €.",
                  content, extra_ld=[item_list])


def page_product(p):
    others = [o for o in PRODUCTS if o["id"] != p["id"] and o["available"]][:4]
    notes = [
        f"Spedizione {eur(SHIP['flat_cents'])}, gratuita sopra {eur(SHIP['free_over_cents'])}",
        "Spediamo dal lunedì al mercoledì, per farla arrivare fresca",
        "Puoi aggiungere un biglietto d'auguri al pagamento",
    ]
    if p.get("seasonal"):
        notes.insert(0, f"Dolce di stagione: {p['seasonal']}")
    sections = [("Descrizione", p["description"]), ("Come gustarla", p.get("serve", ""))]
    if p.get("ingredients"):
        sections.append(("Ingredienti", p["ingredients"]))
    if p.get("allergens"):
        sections.append(("Allergeni", p["allergens"]))
    if p.get("shelf"):
        sections.append(("Conservazione", p["shelf"]))
    sections.append(("Produttore", f"Giuditta Teresa — {BIZ['street']}, {BIZ['zip']} {BIZ['city']} ({BIZ['prov']})"))
    details = "".join(
        f"<details{' open' if i == 0 else ''}><summary>{e(t)}</summary><div>{e(v)}</div></details>"
        for i, (t, v) in enumerate(sections) if v
    )
    buy = (f"""<div class="pdp-buy">
  <div class="qty"><button type="button" data-step="-1" aria-label="Diminuisci">−</button><input id="qty" type="number" min="1" max="50" value="1" aria-label="Quantità"><button type="button" data-step="1" aria-label="Aumenta">+</button></div>
  <button class="btn" data-add="{p['id']}" data-qty-from="#qty">Aggiungi al carrello</button>
</div>""" if p["available"] else '<p class="notice">Al momento non disponibile. <a href="/contatti/">Scrivici</a> per sapere quando tornerà.</p>')
    meta = " · ".join(x for x in (p.get("subtitle"), p["weight"], p.get("pack")) if x)
    content = f"""
<div class="wrap">
  <nav class="crumbs" aria-label="Percorso"><a href="/">Home</a> / <a href="/shop/">I nostri dolci</a> / {e(p['name'])}</nav>
  <div class="pdp">
    <div class="pdp-img"><img src="/img/{p['image']}" alt="{e(full_name(p))} Giuditta Teresa" width="855" height="570" fetchpriority="high"></div>
    <div>
      <span class="eyebrow">{e(p.get('badge') or 'Fatto a mano ad Azzida')}</span>
      <h1>{e(p['name'])}</h1>
      <div class="muted">{e(meta)}</div>
      <span class="price">{eur(p['price_cents'])}</span>
      <p>{e(p['short'])}</p>
      {buy}
      <ul class="pdp-notes">{''.join(f'<li>{e(n)}</li>' for n in notes)}</ul>
      <div class="details">{details}</div>
    </div>
  </div>
</div>
<section class="section section-alt"><div class="wrap">
  <div class="section-head"><h2>Potrebbero piacerti anche</h2></div>
  <div class="grid">{''.join(product_card(o) for o in others)}</div>
</div></section>
"""
    prod_ld = {
        "@context": "https://schema.org", "@type": "Product",
        "name": full_name(p), "description": p["short"],
        "image": f"{SITE}/img/{p['image']}", "sku": p["id"],
        "brand": {"@type": "Brand", "name": "Giuditta Teresa"},
        "manufacturer": {"@id": SITE + "/#azienda"},
        "offers": {
            "@type": "Offer", "url": SITE + product_url(p), "priceCurrency": "EUR",
            "price": f"{p['price_cents']/100:.2f}",
            "availability": "https://schema.org/InStock" if p["available"] else "https://schema.org/OutOfStock",
            "itemCondition": "https://schema.org/NewCondition",
            "shippingDetails": {
                "@type": "OfferShippingDetails",
                "shippingRate": {"@type": "MonetaryAmount", "value": f"{SHIP['flat_cents']/100:.2f}", "currency": "EUR"},
                "shippingDestination": {"@type": "DefinedRegion", "addressCountry": "IT"},
            },
        },
    }
    crumbs_ld = {
        "@context": "https://schema.org", "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Home", "item": SITE + "/"},
            {"@type": "ListItem", "position": 2, "name": "I nostri dolci", "item": SITE + "/shop/"},
            {"@type": "ListItem", "position": 3, "name": p["name"], "item": SITE + product_url(p)},
        ],
    }
    desc = f"{full_name(p)}: {p['short']} Fatto a mano ad Azzida, Valli del Natisone. {eur(p['price_cents'])}."
    return layout(product_url(p), f"{full_name(p)} artigianale", desc[:300], content, image=p["image"], extra_ld=[prod_ld, crumbs_ld])


def page_story():
    content = f"""
<section class="page-hero"><div class="wrap split">
  <div>
    <span class="eyebrow">La nostra storia</span>
    <h1>Una Gubana nel cuore</h1>
    <p class="lead">Ci sono mestieri che nascono dalla passione: fare dolci è uno di questi.</p>
    <p>La tradizione della Gubana si unisce alla storia della nostra famiglia dal 1981, quando rilevammo la ditta Giuditta Teresa ad Azzida, nelle Valli del Natisone. Un'avventura che è diventata la nostra missione, come se la Gubana stessa ci avesse chiamati a proseguire il percorso di bontà e generosità che ancora oggi caratterizza queste valli.</p>
    <p>Il cuore del laboratorio è Silvana Chiabai, pasticcera e titolare: è lei a custodire la ricetta di famiglia ed è lei ad aver inventato la Colombana.</p>
  </div>
  <img src="/img/gubana-scontornata.jpg" alt="Gubana Giuditta Teresa" width="500" height="369">
</div></section>

<section class="section section-alt"><div class="wrap split reverse">
  <div>
    <span class="eyebrow">La promessa</span>
    <h2>Il segreto del ripieno</h2>
    <p>L'abbondanza di farcitura è il segno distintivo della Gubana. Se oggi ne apprezziamo la complessità e la quantità, immaginiamo cosa significasse in tempi in cui imbandire una tavola non era facile come oggi: la generosità del ripieno era un augurio di buona fortuna, una festa per il palato e per la famiglia.</p>
    <p>La nostra ricetta si tramanda da generazioni e si basa su una promessa fatta a mamma Lidia: <em>mantenere fede alla qualità</em>. Per questo le noci le puliamo ancora a mano, e la bagnatura dei liquori la dosiamo noi, come abbiamo sempre fatto.</p>
  </div>
  <img src="/img/pelatura-noci.jpg" alt="Pulizia a mano delle noci" width="500" height="369" loading="lazy">
</div></section>

<section class="section section-dark"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Le fasi della lavorazione</span><h2>Come nasce una Gubana</h2></div>
  {STEPS}
</div></section>

<section class="section"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Nel laboratorio</span><h2>Mani, forno e pazienza</h2></div>
  <div class="gallery">
    <figure><img src="/img/lab-noci.jpg" alt="Pulizia manuale delle noci" loading="lazy"><figcaption>Le noci, pulite a mano</figcaption></figure>
    <figure><img src="/img/lab-impasto.jpg" alt="Impasto di nocciole" loading="lazy"><figcaption>Il ripieno</figcaption></figure>
    <figure><img src="/img/lab-frittura.jpg" alt="Frittura degli Strucchi" loading="lazy"><figcaption>La frittura degli Strucchi</figcaption></figure>
    <figure><img src="/img/lab-strucchi.jpg" alt="Asciugatura degli Strucchi" loading="lazy"><figcaption>Strucchi ad asciugare</figcaption></figure>
    <figure><img src="/img/lab-composizione.jpg" alt="Composizione con Gubana" loading="lazy"><figcaption>La Gubana</figcaption></figure>
    <figure><img src="/img/tronco.jpg" alt="Tronco di Gubana tagliato" loading="lazy"><figcaption>Il Tronco di Gubana</figcaption></figure>
  </div>
  <p class="small" style="margin-top:14px">Fotografie di Ennio Calice.</p>
</div></section>

<section class="section section-alt"><div class="wrap narrow center">
  <h2>Assaggiala a casa tua</h2>
  <p class="lead">Spediamo in tutta Italia, gratuitamente sopra i {SHIP['free_over_cents']//100} €.</p>
  <a class="btn" href="/shop/">Scegli i tuoi dolci</a>
</div></section>
"""
    return layout("/la-nostra-storia/", "La nostra storia: la Gubana di famiglia dal 1981",
                  "Dal 1981 la famiglia di Silvana Chiabai produce a mano la Gubana delle Valli del Natisone ad Azzida, con la ricetta segreta del ripieno tramandata da generazioni.",
                  content, image="lab-composizione.jpg")


def page_awards():
    content = f"""
<section class="page-hero"><div class="wrap">
  <span class="eyebrow">Premi e stampa</span>
  <h1>Dicono di noi</h1>
  <p class="lead narrow">Non facciamo concorsi per collezionare medaglie, ma quando la nostra Gubana viene scelta a caso dalla produzione di ogni giorno e vince, ne siamo orgogliosi.</p>
</div></section>
<section style="padding-bottom:80px"><div class="wrap">
  <div class="awards">{awards_html(AWARDS)}</div>
  <p class="muted" style="margin-top:36px">Siamo parte delle <strong>Strade del Vino e dei Sapori del Friuli Venezia Giulia</strong> e di <strong>Colli Orientali — Terre da vivere insieme</strong>. Di noi hanno scritto anche Il Golosario, LiveFVG, Vino e Vacanze e Prodotti-regionali.com.</p>
</div></section>
"""
    return layout("/premi-e-stampa/", "Premi e stampa", "Primo premio al Gubana Day 2012 e 2016, il Corriere della Sera, la rivista Quanto Basta: cosa dicono della Gubana Giuditta Teresa.", content, image="premio-gubana-day.jpg")


def page_business():
    content = f"""
<section class="page-hero"><div class="wrap split">
  <div>
    <span class="eyebrow">Aziende, negozi, ristorazione</span>
    <h1>Un regalo che racconta un territorio</h1>
    <p class="lead">Un dolce fatto a mano nelle Valli del Natisone è un regalo che si ricorda. Lavoriamo con aziende, botteghe, enoteche, hotel e ristoranti che cercano un prodotto autentico.</p>
    <a class="btn" href="#richiesta">Richiedi il listino</a>
  </div>
  <img src="/img/colombana.jpg" alt="Colombana Giuditta Teresa" width="855" height="570">
</div></section>

<section class="section section-dark"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Regali aziendali</span><h2>Per Natale, Pasqua e ogni occasione</h2></div>
  <ul class="b2b-points">
    <li><h3>Biglietto personalizzato</h3><p>Il vostro messaggio, scritto a mano su ogni confezione, con il vostro logo se lo desiderate.</p></li>
    <li><h3>Consegna a più indirizzi</h3><p>Ci mandate l'elenco dei destinatari, pensiamo noi a confezionare e spedire.</p></li>
    <li><h3>Confezioni su misura</h3><p>Gubana, Strucchi, Colombana: componiamo insieme la confezione giusta per il vostro budget.</p></li>
    <li><h3>Fattura e listino dedicato</h3><p>Prezzi riservati per quantità, fattura elettronica e un unico referente.</p></li>
  </ul>
  <p style="margin-top:28px" class="muted">Per Natale consigliamo di contattarci entro fine ottobre: ogni dolce è fatto a mano e la produzione non è infinita.</p>
</div></section>

<section class="section" id="rivenditori"><div class="wrap split">
  <div>
    <span class="eyebrow">Rivenditori</span>
    <h2>Vendi la nostra Gubana</h2>
    <p>Botteghe di prodotti tipici, enoteche, gastronomie, agriturismi e hotel: se cercate una Gubana artigianale premiata, con una storia vera da raccontare ai vostri clienti, scriveteci. Vi invieremo condizioni di fornitura e calendario delle consegne.</p>
    <p class="muted">Hotel e ristoranti: proponiamo anche formati per la colazione e per il fine pasto.</p>
  </div>
  <img src="/img/gemme.jpg" alt="Gemme di Gubana" width="855" height="570" loading="lazy">
</div></section>

<section class="section section-alt" id="richiesta"><div class="wrap narrow">
  <div class="section-head"><h2>Richiedi il listino</h2><p class="muted">Rispondiamo entro due giorni lavorativi. Preferisci il telefono? Chiamaci al <a href="tel:{BIZ['phone'].replace(' ', '')}">{BIZ['phone_display']}</a>.</p></div>
  <form class="form" name="aziende" method="POST" action="/grazie-richiesta/" data-netlify="true" netlify-honeypot="sito-web">
    <input type="hidden" name="form-name" value="aziende">
    <p class="hp"><label>Non compilare: <input name="sito-web"></label></p>
    <div><label for="f-azienda">Azienda *</label><input id="f-azienda" name="azienda" required autocomplete="organization"></div>
    <div><label for="f-piva">Partita IVA</label><input id="f-piva" name="partita_iva"></div>
    <div><label for="f-nome">Nome e cognome *</label><input id="f-nome" name="nome" required autocomplete="name"></div>
    <div><label for="f-tel">Telefono</label><input id="f-tel" name="telefono" type="tel" autocomplete="tel"></div>
    <div class="full"><label for="f-email">Email *</label><input id="f-email" name="email" type="email" required autocomplete="email"></div>
    <div><label for="f-tipo">Di cosa avete bisogno? *</label>
      <select id="f-tipo" name="tipo" required>
        <option value="">Scegli…</option>
        <option>Regali aziendali di Natale</option>
        <option>Regali aziendali di Pasqua</option>
        <option>Rivendita in negozio</option>
        <option>Hotel / ristorazione</option>
        <option>Evento o matrimonio</option>
        <option>Altro</option>
      </select></div>
    <div><label for="f-qta">Quantità indicativa</label>
      <select id="f-qta" name="quantita"><option>10–30 confezioni</option><option>30–100 confezioni</option><option>Oltre 100 confezioni</option><option>Fornitura continuativa</option></select></div>
    <div class="full"><label for="f-data">Entro quando?</label><input id="f-data" name="data_consegna" placeholder="es. entro il 15 dicembre"></div>
    <div class="full"><label for="f-msg">Raccontateci di più</label><textarea id="f-msg" name="messaggio"></textarea></div>
    <label class="check full"><input type="checkbox" name="privacy" required> <span>Acconsento al trattamento dei dati per ricevere una risposta, secondo l'<a href="https://www.iubenda.com/privacy-policy/21456511" target="_blank" rel="noopener">informativa privacy</a>. *</span></label>
    <div class="full"><button class="btn" type="submit">Invia richiesta</button></div>
  </form>
</div></section>
"""
    return layout("/aziende/", "Regali aziendali e fornitura per negozi",
                  "Regali aziendali di Natale e Pasqua con Gubana artigianale delle Valli del Natisone: biglietto personalizzato, consegna a più indirizzi, listino dedicato. Fornitura per botteghe, enoteche e hotel.",
                  content, image="colombana.jpg")


def page_contacts():
    content = f"""
<section class="page-hero"><div class="wrap split" style="align-items:start">
  <div>
    <span class="eyebrow">Contatti</span>
    <h1>Venite a trovarci</h1>
    <p class="lead">Il nostro laboratorio è ad Azzida, alle porte delle Valli del Natisone, a pochi minuti da Cividale del Friuli.</p>
    <p><strong>Giuditta Teresa</strong><br>{e(BIZ['street'])}<br>{BIZ['zip']} {e(BIZ['city'])} ({BIZ['prov']})</p>
    <p>Telefono: <a href="tel:{BIZ['phone'].replace(' ', '')}">{BIZ['phone_display']}</a><br>Email: <a href="mailto:{BIZ['email']}">{BIZ['email']}</a></p>
    <p><a class="btn btn-ghost btn-sm" href="{BIZ['maps']}" rel="noopener" target="_blank">Apri in Google Maps</a></p>
    <p class="small">Prima di passare per ritirare un dolce su ordinazione, chiamaci per conferma.</p>
  </div>
  <form class="form" name="contatti" method="POST" action="/grazie-richiesta/" data-netlify="true" netlify-honeypot="sito-web">
    <input type="hidden" name="form-name" value="contatti">
    <p class="hp"><label>Non compilare: <input name="sito-web"></label></p>
    <div class="full"><label for="c-nome">Nome *</label><input id="c-nome" name="nome" required autocomplete="name"></div>
    <div class="full"><label for="c-email">Email *</label><input id="c-email" name="email" type="email" required autocomplete="email"></div>
    <div class="full"><label for="c-msg">Messaggio *</label><textarea id="c-msg" name="messaggio" required></textarea></div>
    <label class="check full"><input type="checkbox" name="privacy" required> <span>Acconsento al trattamento dei dati secondo l'<a href="https://www.iubenda.com/privacy-policy/21456511" target="_blank" rel="noopener">informativa privacy</a>. *</span></label>
    <div class="full"><button class="btn" type="submit">Invia</button></div>
  </form>
</div></section>
"""
    return layout("/contatti/", "Contatti", "Giuditta Teresa, Via Algida 43, Azzida — San Pietro al Natisone (UD). Tel. 0432 727681, info@giudittateresa.com.", content)


def page_simple(path, title, desc, inner, body_attr="", noindex=True):
    return layout(path, title, desc, f'<section class="section"><div class="wrap narrow center">{inner}</div></section>', body_attr=body_attr, noindex=noindex)


def page_terms():
    content = f"""
<section class="page-hero"><div class="wrap prose">
  <span class="eyebrow">Informazioni</span>
  <h1>Spedizioni e condizioni di vendita</h1>
  <p class="notice">Testo di base da far verificare al proprio consulente prima della pubblicazione.</p>

  <h2>Venditore</h2>
  <p>Giuditta Teresa — {e(BIZ['street'])}, {BIZ['zip']} {e(BIZ['city'])} ({BIZ['prov']}) — P. IVA {BIZ['vat']} — {BIZ['email']} — tel. {BIZ['phone_display']}.</p>

  <h2>Prodotti e prezzi</h2>
  <p>I prezzi sono in euro e comprensivi di IVA. Le spese di spedizione sono indicate nel carrello prima del pagamento. Le immagini dei prodotti sono indicative: ogni dolce è fatto a mano e può differire leggermente nell'aspetto. Ingredienti e allergeni sono riportati nella scheda di ogni prodotto e sull'etichetta.</p>

  <h2>Ordine e pagamento</h2>
  <p>Il contratto si conclude con la conferma del pagamento, effettuato su pagina sicura di Stripe Payments Europe Ltd. con carta, Apple Pay, Google Pay o PayPal. Riceverai la conferma via email. Non riceviamo né conserviamo i dati della tua carta.</p>

  <h2>Spedizione</h2>
  <ul>
    <li>Spediamo in Italia, San Marino e Città del Vaticano con corriere espresso.</li>
    <li>Costo: {eur(SHIP['flat_cents'])} per ordine, gratuita per ordini da {eur(SHIP['free_over_cents'])} in su.</li>
    <li>Spediamo dal lunedì al mercoledì per evitare soste nel fine settimana. Consegna di solito in {SHIP['days_min']}–{SHIP['days_max']} giorni lavorativi dalla spedizione.</li>
    <li>Nei periodi di Natale e Pasqua i tempi possono allungarsi: ti consigliamo di ordinare con anticipo.</li>
  </ul>

  <h2>Diritto di recesso</h2>
  <p>Trattandosi di prodotti alimentari deperibili, il diritto di recesso è escluso ai sensi dell'art. 59, comma 1, lett. d) ed e) del Codice del Consumo (D.Lgs. 206/2005).</p>

  <h2>Prodotti danneggiati o non conformi</h2>
  <p>Se il pacco arriva danneggiato, accettalo con riserva specifica al corriere e scrivici entro 48 ore a {BIZ['email']} con alcune foto. Sostituiremo il prodotto o ti rimborseremo.</p>

  <h2>Reclami e controversie</h2>
  <p>Per qualsiasi problema scrivici: rispondiamo personalmente. Piattaforma europea ODR: <a href="https://ec.europa.eu/consumers/odr" rel="noopener">ec.europa.eu/consumers/odr</a>. Si applica la legge italiana.</p>

  <h2>Privacy</h2>
  <p>I dati sono trattati solo per evadere l'ordine, secondo la nostra <a href="https://www.iubenda.com/privacy-policy/21456511" rel="noopener">informativa privacy</a>.</p>
</div></section>
"""
    return layout("/condizioni-di-vendita/", "Spedizioni e condizioni di vendita", "Spedizioni, pagamenti, condizioni di vendita del negozio online Giuditta Teresa.", content)


# ---------- Build ----------

def write(rel, text):
    path = OUT / rel
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(text, encoding="utf-8")


def make_favicon():
    try:
        from PIL import Image
        src = SRC / "img" / "logo.png"
        im = Image.open(src).convert("RGBA")
        icon = im.crop((0, 0, im.height, im.height)).resize((64, 64))  # la nocciola
        icon.save(OUT / "img" / "favicon.png")
    except Exception as ex:  # Pillow non installato: favicon opzionale
        print("  (favicon non generata:", ex, ")")


def main():
    warnings = []
    for p in PRODUCTS:
        if p.get("_verificare_prezzo"):
            warnings.append(f"Prezzo da verificare: {full_name(p)} ({eur(p['price_cents'])})")
        if p["available"] and not (p.get("ingredients") and p.get("allergens")):
            warnings.append(f"Ingredienti/allergeni mancanti (obbligatori per legge): {full_name(p)}")
        if not (SRC / "img" / p["image"]).exists():
            sys.exit(f"Immagine mancante: {p['image']}")

    if OUT.exists():
        shutil.rmtree(OUT)
    shutil.copytree(SRC / "assets", OUT / "assets")
    shutil.copytree(SRC / "img", OUT / "img")
    make_favicon()

    pages = {
        "index.html": page_home(),
        "shop/index.html": page_shop(),
        "la-nostra-storia/index.html": page_story(),
        "premi-e-stampa/index.html": page_awards(),
        "aziende/index.html": page_business(),
        "contatti/index.html": page_contacts(),
        "condizioni-di-vendita/index.html": page_terms(),
        "grazie/index.html": page_simple("/grazie/", "Grazie per il tuo ordine", "Ordine ricevuto.",
            f"""<span class="eyebrow">Ordine ricevuto</span><h1>Grazie!</h1>
<p class="lead">Abbiamo ricevuto il tuo ordine e ti abbiamo inviato una email di conferma. Prepareremo i tuoi dolci a mano e li spediremo al più presto.</p>
<p>Per qualsiasi domanda: <a href="mailto:{BIZ['email']}">{BIZ['email']}</a> · {BIZ['phone_display']}</p>
<p style="margin-top:28px"><a class="btn btn-ghost" href="/">Torna alla home</a></p>""", body_attr="data-clear-cart"),
        "grazie-richiesta/index.html": page_simple("/grazie-richiesta/", "Richiesta inviata", "Richiesta inviata.",
            """<span class="eyebrow">Messaggio inviato</span><h1>Grazie, ti risponderemo presto</h1>
<p class="lead">Leggiamo personalmente ogni messaggio e rispondiamo di solito entro due giorni lavorativi.</p>
<p style="margin-top:28px"><a class="btn" href="/shop/">Intanto, scopri i nostri dolci</a></p>"""),
        "404.html": page_simple("/404.html", "Pagina non trovata", "Pagina non trovata.",
            """<span class="eyebrow">Errore 404</span><h1>Questa pagina non c'è</h1>
<p class="lead">Forse è stata spostata con il nuovo sito.</p><p><a class="btn" href="/shop/">Vai ai nostri dolci</a></p>"""),
    }
    for p in PRODUCTS:
        pages[f"prodotti/{p['id']}/index.html"] = page_product(p)
    for rel, text in pages.items():
        write(rel, text)

    indexable = ["/", "/shop/", "/la-nostra-storia/", "/premi-e-stampa/", "/aziende/", "/contatti/", "/condizioni-di-vendita/"] + [product_url(p) for p in PRODUCTS]
    sitemap = "".join(f"<url><loc>{SITE}{u}</loc><lastmod>{TODAY}</lastmod></url>" for u in indexable)
    write("sitemap.xml", f'<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">{sitemap}</urlset>\n')
    write("robots.txt", f"User-agent: *\nAllow: /\nDisallow: /grazie/\nDisallow: /grazie-richiesta/\n\nSitemap: {SITE}/sitemap.xml\n")

    print(f"Generate {len(pages)} pagine in {OUT.relative_to(ROOT)}/")
    if warnings:
        print("\nATTENZIONE prima di andare online:")
        for w in warnings:
            print("  -", w)


if __name__ == "__main__":
    main()
