import json
import re
from pathlib import Path

path = Path("/var/www/html/vitravel.dev/optimize/hotel_detail_desktop.json")
with path.open(encoding="utf-8") as f:
    data = json.load(f)

audits = data.get("audits", {})
cats = data.get("categories", {})
out = []

def p(s=""):
    out.append(s)

p("=== CATEGORIES ===")
for k, v in cats.items():
    p("%s: score=%s title=%s" % (k, v.get("score"), v.get("title")))

p("\n=== URL ===")
p("requestedUrl: %s" % data.get("requestedUrl"))
p("finalUrl: %s" % data.get("finalUrl"))
p("fetchTime: %s" % data.get("fetchTime"))
p("lighthouseVersion: %s" % data.get("lighthouseVersion"))
config = data.get("configSettings") or {}
p("formFactor: %s" % config.get("formFactor"))

metric_ids = [
    "first-contentful-paint",
    "largest-contentful-paint",
    "total-blocking-time",
    "cumulative-layout-shift",
    "speed-index",
    "interactive",
    "max-potential-fid",
    "server-response-time",
]
p("\n=== METRICS ===")
for mid in metric_ids:
    a = audits.get(mid)
    if not a:
        p("%s: MISSING" % mid)
        continue
    p("%s: score=%s numericValue=%s displayValue=%r title=%s" % (
        mid, a.get("score"), a.get("numericValue"), a.get("displayValue"), a.get("title")))

def strip_md(s):
    return re.sub(r"\[([^\]]+)\]\([^)]+\)", r"\1", s or "")

perf = cats.get("performance", {})
refs = perf.get("auditRefs", []) or []
items = []
for r in refs:
    aid = r["id"]
    a = audits.get(aid)
    if not a:
        continue
    sc = a.get("score")
    if sc is None or sc >= 1:
        continue
    items.append({
        "id": aid,
        "title": a.get("title"),
        "score": sc,
        "displayValue": a.get("displayValue"),
        "numericValue": a.get("numericValue"),
        "group": r.get("group"),
        "weight": r.get("weight", 0),
        "details_type": (a.get("details") or {}).get("type"),
        "description": strip_md(a.get("description") or "")[:220],
    })

def sort_key(x):
    gprio = 0 if x["group"] == "opportunities" else (1 if x["group"] == "diagnostics" else 2)
    sav = x["numericValue"] if x["numericValue"] is not None else 0
    return (gprio, -(sav if x["group"] == "opportunities" else 0), x["score"] if x["score"] is not None else 1)

items.sort(key=sort_key)
p("\n=== PERF FAILING AUDITS (score < 1) ===")
p("count: %d" % len(items))
for it in items:
    p("--- %s ---" % it["id"])
    p("  title=%s" % it["title"])
    p("  score=%s group=%s weight=%s" % (it["score"], it["group"], it["weight"]))
    p("  displayValue=%r numericValue=%s" % (it["displayValue"], it["numericValue"]))
    p("  details_type=%s" % it["details_type"])
    p("  description=%r" % it["description"])

KEY_AUDITS = [
    "unused-javascript",
    "unused-css-rules",
    "render-blocking-resources",
    "largest-contentful-paint-element",
    "uses-responsive-images",
    "uses-optimized-images",
    "modern-image-formats",
    "offscreen-images",
    "total-byte-weight",
    "uses-long-cache-ttl",
    "efficient-animated-content",
    "unminified-javascript",
    "unminified-css",
    "uses-text-compression",
    "redirects",
    "server-response-time",
    "legacy-javascript",
    "duplicated-javascript",
    "bootup-time",
    "mainthread-work-breakdown",
    "dom-size",
    "third-party-summary",
    "font-display",
    "prioritize-lcp-image",
    "lcp-lazy-loaded",
    "uses-rel-preconnect",
    "network-requests",
    "critical-request-chains",
    "layout-shift-elements",
    "unsized-images",
    "non-composited-animations",
    "uses-http2",
    "uses-passive-event-listeners",
    "viewport",
]

def row_to_hint(item):
    if not isinstance(item, dict):
        return str(item)[:200]
    parts = []
    for key in ("url", "src", "name", "selector", "nodeLabel", "snippet", "path", "label"):
        if item.get(key):
            parts.append("%s=%s" % (key, item[key]))
    node = item.get("node")
    if isinstance(node, dict):
        for key in ("selector", "snippet", "nodeLabel", "path"):
            if node.get(key):
                parts.append("node.%s=%s" % (key, node[key]))
    for key in ("wastedBytes", "wastedMs", "totalBytes", "transferSize", "resourceSize", "duration", "groupLabel", "mainThreadTime", "blockingTime", "cacheLifetimeMs", "score"):
        if item.get(key) is not None:
            parts.append("%s=%s" % (key, item[key]))
    return " | ".join(parts) if parts else json.dumps(item)[:250]

p("\n=== RESOURCE DETAILS (top items) ===")
for aid in KEY_AUDITS:
    a = audits.get(aid)
    if not a:
        continue
    sc = a.get("score")
    always = {
        "largest-contentful-paint-element", "dom-size", "bootup-time",
        "mainthread-work-breakdown", "third-party-summary",
        "critical-request-chains", "layout-shift-elements",
    }
    if sc is not None and sc >= 1 and aid not in always:
        continue
    details = a.get("details") or {}
    dtype = details.get("type")
    p("\n#### %s (score=%s display=%r type=%s) ####" % (aid, sc, a.get("displayValue"), dtype))
    items_list = details.get("items") or []
    if dtype in ("opportunity", "table"):
        def item_sav(it):
            return it.get("wastedBytes") or it.get("wastedMs") or it.get("totalBytes") or it.get("transferSize") or it.get("mainThreadTime") or it.get("blockingTime") or 0
        sorted_items = sorted(items_list, key=item_sav, reverse=True)
        for i, it in enumerate(sorted_items[:10]):
            p("  [%d] %s" % (i + 1, row_to_hint(it)))
    elif dtype == "list":
        for i, it in enumerate(items_list[:10]):
            if isinstance(it, dict) and "value" in it:
                val = it["value"]
                p("  [%d] %s" % (i + 1, row_to_hint(val if isinstance(val, dict) else it)))
                if isinstance(val, dict) and val.get("items"):
                    for j, sub in enumerate(val["items"][:8]):
                        p("      sub[%d] %s" % (j + 1, row_to_hint(sub)))
            else:
                p("  [%d] %s" % (i + 1, row_to_hint(it)))
    elif dtype == "criticalrequestchain":
        chains = details.get("chains") or {}
        p("  longestChain: %s" % details.get("longestChain"))
        def walk(chain, depth=0, acc=None):
            if acc is None:
                acc = []
            req = chain.get("request") or {}
            acc.append((depth, req.get("url"), req.get("transferSize")))
            for ch in (chain.get("children") or {}).values():
                walk(ch, depth + 1, acc)
            return acc
        flat = []
        for ch in chains.values():
            flat.extend(walk(ch))
        for i, (depth, url, size) in enumerate(flat[:15]):
            p("  [%d] depth=%s size=%s url=%s" % (i + 1, depth, size, url))
    else:
        for i, it in enumerate(items_list[:10]):
            p("  [%d] %s" % (i + 1, row_to_hint(it)))
        if not items_list and details:
            p("  detail_keys: %s" % list(details.keys())[:20])

p("\n=== OTHER CATEGORIES FAILING ===")
for cat_id, cat in cats.items():
    if cat_id == "performance":
        continue
    failing = []
    for r in cat.get("auditRefs") or []:
        a = audits.get(r["id"])
        if not a:
            continue
        sc = a.get("score")
        if sc is not None and sc < 1:
            failing.append((r["id"], a.get("title"), sc, a.get("displayValue")))
    if failing:
        p("[%s] score=%s" % (cat_id, cat.get("score")))
        for fid, title, sc, dv in failing[:20]:
            p("  - %s | %s | score=%s | %r" % (fid, title, sc, dv))

p("\n=== DONE ===")
Path("/var/www/html/vitravel.dev/optimize/_lh_summary.txt").write_text("\n".join(out), encoding="utf-8")
print("Wrote", len(out), "lines")
