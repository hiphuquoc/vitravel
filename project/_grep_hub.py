import re, os
os.chdir("/var/www/html/vitravel.dev/project")
fs = ["seed_hihalong.php","seed_himuine.php","seed_hidalat.php","seed_hisapa.php","seed_hitamdao.php","seed_hihagiang.php"]
pat = re.compile(r"must-see|onboard|anti-crowd|2N1D|SGN |Positioning|Agri-tourism|sweet spot|cruises hub|weekend ")
ex = re.compile(r"'en'|tourCode|styles|slug|bio_html_en|short_bio_en|_en'")
out = []
for f in fs:
    for i, l in enumerate(open(f, encoding="utf-8", errors="replace"), 1):
        if pat.search(l) and not ex.search(l):
            out.append("%s:%d:%s" % (f, i, l.rstrip()))
print("\n".join(out[:30]))
