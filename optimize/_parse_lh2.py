import json
from pathlib import Path

data = json.load(open("/var/www/html/vitravel.dev/optimize/hotel_detail_desktop.json", encoding="utf-8"))
audits = data["audits"]
out = []

def p(s=""):
    out.append(s)

def dump_audit(aid, max_items=15):
    a = audits.get(aid)
    if not a:
        p("MISSING: %s" % aid)
        return
    details = a.get("details") or {}
    p("\n#### %s score=%s display=%r type=%s ####" % (aid, a.get("score"), a.get("displayValue"), details.get("type")))
    # overview / debug data
    for k in ("overallSavingsMs", "overallSavingsBytes", "headings"):
        if k in details:
            p("  %s: %s" % (k, details[k] if k != "headings" else [h.get("key") for h in details[k]]))
    items = details.get("items") or []
    # filter chrome-extension for some
    site_items = [it for it in items if not (isinstance(it, dict) and str(it.get("url") or "").startswith("chrome-extension://"))]
    # also skip extension in nested
    use = site_items if site_items else items
    for i, it in enumerate(use[:max_items]):
        if not isinstance(it, dict):
            p("  [%d] %r" % (i+1, it))
            continue
        # compact dump of useful fields
        keys_of_interest = [
            "url", "src", "name", "label", "selector", "nodeLabel", "snippet", "path",
            "wastedBytes", "wastedMs", "totalBytes", "transferSize", "resourceSize",
            "duration", "groupLabel", "mainThreadTime", "blockingTime", "cacheLifetimeMs",
            "subpart", "timing", "phase", "value", "responseTime", "protocol",
            "isMainDocument", "isRenderBlocking", "priority", "mimeType",
            "width", "height", "displayedWidth", "displayedHeight",
            "reason", "failureReason", "entity", "transferSize", "mainThreadTime",
            "tbtImpact", "cls", "score",
        ]
        parts = []
        for k in keys_of_interest:
            if it.get(k) is not None and k not in [x.split("=")[0] for x in parts]:
                val = it[k]
                if isinstance(val, dict):
                    # node-like
                    sub = []
                    for sk in ("selector", "snippet", "nodeLabel", "path", "type"):
                        if val.get(sk):
                            sub.append("%s=%s" % (sk, val[sk]))
                    if not sub:
                        # maybe timing object
                        sub.append(json.dumps(val)[:180])
                    parts.append("%s={%s}" % (k, "; ".join(sub)))
                else:
                    parts.append("%s=%s" % (k, val))
        node = it.get("node")
        if isinstance(node, dict):
            for sk in ("selector", "snippet", "nodeLabel", "path"):
                if node.get(sk):
                    parts.append("node.%s=%s" % (sk, node[sk]))
        # subItems
        if it.get("subItems"):
            parts.append("subItems_count=%s" % len(it["subItems"].get("items") or it["subItems"] if isinstance(it["subItems"], list) else []))
        p("  [%d] %s" % (i+1, " | ".join(parts)[:500]))
        # expand subItems
        si = it.get("subItems")
        if isinstance(si, dict):
            for j, sub in enumerate((si.get("items") or [])[:8]):
                sp = []
                for k in keys_of_interest:
                    if isinstance(sub, dict) and sub.get(k) is not None:
                        sp.append("%s=%s" % (k, sub[k] if not isinstance(sub[k], dict) else json.dumps(sub[k])[:120]))
                if isinstance(sub, dict) and sub.get("node"):
                    n = sub["node"]
                    for sk in ("selector", "snippet", "nodeLabel"):
                        if n.get(sk):
                            sp.append("node.%s=%s" % (sk, n[sk]))
                p("      sub[%d] %s" % (j+1, " | ".join(sp)[:400]))
        # list value nesting (insights)
        if details.get("type") == "list" and "value" in it:
            val = it["value"]
            if isinstance(val, dict):
                p("      value.type=%s keys=%s" % (val.get("type"), list(val.keys())[:15]))
                for j, sub in enumerate((val.get("items") or [])[:10]):
                    if isinstance(sub, dict):
                        sp = []
                        for k in keys_of_interest + ["text", "type"]:
                            if sub.get(k) is not None:
                                vv = sub[k]
                                if isinstance(vv, dict):
                                    sp.append("%s=%s" % (k, json.dumps(vv)[:150]))
                                else:
                                    sp.append("%s=%s" % (k, vv))
                        node = sub.get("node")
                        if isinstance(node, dict):
                            for sk in ("selector", "snippet", "nodeLabel", "path"):
                                if node.get(sk):
                                    sp.append("node.%s=%s" % (sk, node[sk]))
                        p("      item[%d] %s" % (j+1, " | ".join(sp)[:450]))

# Insights + classic
for aid in [
    "unused-javascript", "unused-css-rules",
    "document-latency-insight", "forced-reflow-insight", "image-delivery-insight",
    "lcp-breakdown-insight", "network-dependency-tree-insight", "cache-insight",
    "render-blocking-insight", "server-response-time",
    "largest-contentful-paint-element", "prioritize-lcp-image", "lcp-lazy-loaded",
    "layout-shift-elements", "unsized-images",
    "render-blocking-resources", "uses-responsive-images", "modern-image-formats",
    "uses-optimized-images", "offscreen-images", "total-byte-weight",
    "uses-long-cache-ttl", "third-party-summary", "font-display",
    "dom-size", "critical-request-chains", "bootup-time",
    "legacy-javascript", "duplicated-javascript",
    "aria-prohibited-attr", "color-contrast", "label-content-name-mismatch",
    "meta-description", "inspector-issues",
]:
    dump_audit(aid)

# Also list all audit ids with score < 1 site-related display
p("\n=== ALL AUDIT IDS score not 1 (non-metric) with display ===")
for aid, a in sorted(audits.items()):
    sc = a.get("score")
    if sc is None or sc >= 1:
        continue
    if aid in ("speed-index", "total-blocking-time", "largest-contentful-paint", "cumulative-layout-shift", "interactive", "max-potential-fid", "first-contentful-paint"):
        continue
    p("%s | score=%s | %r" % (aid, sc, a.get("displayValue")))

# Network requests: top transfer size site only
p("\n=== TOP NETWORK REQUESTS (site, by transferSize) ===")
nr = audits.get("network-requests", {})
items = (nr.get("details") or {}).get("items") or []
site = [it for it in items if isinstance(it, dict) and str(it.get("url","")).startswith("https://culaocham.net")]
site.sort(key=lambda x: x.get("transferSize") or 0, reverse=True)
for i, it in enumerate(site[:20]):
    p("  [%d] %s bytes=%s resource=%s mime=%s priority=%s" % (
        i+1, it.get("url"), it.get("transferSize"), it.get("resourceSize"), it.get("mimeType"), it.get("priority")))

# third parties non-extension
p("\n=== THIRD PARTY (non-extension) ===")
tp = audits.get("third-party-summary", {})
for it in ((tp.get("details") or {}).get("items") or [])[:15]:
    ent = it.get("entity")
    if isinstance(ent, dict):
        ent = ent.get("text") or ent.get("url") or str(ent)
    p("  entity=%s transfer=%s mainThread=%s blocking=%s" % (
        ent, it.get("transferSize"), it.get("mainThreadTime"), it.get("blockingTime")))

Path("/var/www/html/vitravel.dev/optimize/_lh_summary2.txt").write_text("\n".join(out), encoding="utf-8")
print("lines", len(out))
