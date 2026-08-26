import json
data=json.load(open("/var/www/html/vitravel.dev/optimize/hotel_detail_desktop.json",encoding="utf-8"))
audits=data["audits"]

# aria full
a=audits["aria-prohibited-attr"]
for it in (a.get("details") or {}).get("items") or []:
    n=it.get("node") or {}
    print("ARIA:", n.get("selector"))
    print("  snippet:", n.get("snippet"))
    print("  label:", n.get("nodeLabel"))
    print("  explanation:", n.get("explanation"))

# full LCP / gallery URLs from network
print("\nGCS gallery URLs:")
for it in (audits.get("network-requests",{}).get("details") or {}).get("items") or []:
    u=it.get("url") or ""
    if "gallery" in u and "maison-hai" in u:
        print(it.get("transferSize"), it.get("resourceSize"), u)

# cls insight
for aid in audits:
    if "cls" in aid.lower() or "shift" in aid.lower():
        print("AUDIT", aid, "score", audits[aid].get("score"), "display", audits[aid].get("displayValue"))

# preconnect from network dependency truncated part
nd=audits["network-dependency-tree-insight"]["details"]
import json as J
# find preconnect items
s=J.dumps(nd)
# print preconnect table items only
items=nd["items"]
for sec in items:
    if sec.get("title")=="Preconnected origins":
        print("\nPRECONNECT:")
        print(J.dumps(sec["value"].get("items"), ensure_ascii=False, indent=2)[:2000])
