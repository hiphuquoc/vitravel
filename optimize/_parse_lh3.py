import json
from pathlib import Path

data = json.load(open("/var/www/html/vitravel.dev/optimize/hotel_detail_desktop.json", encoding="utf-8"))
audits = data["audits"]
out = []

def p(s=""):
    out.append(s)

def dump_raw(aid, depth=6):
    a = audits[aid]
    details = a.get("details")
    p("\n===== %s score=%s display=%r =====" % (aid, a.get("score"), a.get("displayValue")))
    # Pretty but truncated
    s = json.dumps(details, ensure_ascii=False, indent=2)
    lines = s.splitlines()
    if len(lines) > 120:
        p("\n".join(lines[:120]))
        p("... [%d more lines] ..." % (len(lines)-120))
    else:
        p(s)

for aid in [
    "document-latency-insight",
    "forced-reflow-insight",
    "lcp-breakdown-insight",
    "network-dependency-tree-insight",
    "aria-prohibited-attr",
    "cls-culprits-insight",
    "layout-shifts",
    "non-composited-animations",
    "font-display",
    "uses-rel-preconnect",
    "preload-lcp-image",
    "lcp-discovery-insight",
]:
    if aid in audits:
        dump_raw(aid)
    else:
        p("MISSING %s" % aid)

# bootup with times
p("\n===== bootup-time items =====")
for it in (audits.get("bootup-time", {}).get("details") or {}).get("items") or []:
    url = it.get("url")
    if url and str(url).startswith("chrome-extension"):
        continue
    p("%s scripting=%s scriptParse=%s total=%s" % (url, it.get("scripting"), it.get("scriptParseCompile"), it.get("total")))

# total byte weight top 15
p("\n===== total-byte-weight =====")
for it in ((audits.get("total-byte-weight", {}).get("details") or {}).get("items") or [])[:15]:
    p("%s %s" % (it.get("totalBytes"), it.get("url")))

# cache fonts with 0
p("\n===== cache-insight cacheLifetimeMs==0 (site) =====")
for it in (audits.get("cache-insight", {}).get("details") or {}).get("items") or []:
    url = it.get("url") or ""
    if it.get("cacheLifetimeMs") == 0 and not url.startswith("chrome-extension"):
        p("ttl=0 bytes=%s %s" % (it.get("totalBytes"), url))

# inspector issue type
p("\n===== inspector-issues raw =====")
p(json.dumps(audits.get("inspector-issues", {}).get("details"), ensure_ascii=False, indent=2)[:3000])

# metrics from lighthouse timing
p("\n===== timing =====")
p(json.dumps(data.get("timing"), indent=2)[:500])

# Check i18n score display as percentages
p("\n===== scores as percent =====")
for k,v in data["categories"].items():
    sc = v.get("score")
    p("%s: %s (%s)" % (k, int(round(sc*100)) if sc is not None else None, sc))

Path("/var/www/html/vitravel.dev/optimize/_lh_summary3.txt").write_text("\n".join(out), encoding="utf-8")
print("ok", len(out))
