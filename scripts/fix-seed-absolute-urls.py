#!/usr/bin/env python3
"""Rewrite first-party absolute site URLs in seed files to relative paths."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
FILES = [
    ROOT / "project/seed_phuquy.php",
    ROOT / "project/seed_hicatba.php",
    ROOT / "project/seed_vitravel.php",
]

HOSTS = [
    r"phuquy\.dev",
    r"www\.phuquy\.dev",
    r"phuquy\.net",
    r"www\.phuquy\.net",
    r"vitravel\.dev",
    r"www\.vitravel\.dev",
    r"vitravel\.net",
    r"www\.vitravel\.net",
    r"catbahub\.dev",
    r"www\.catbahub\.dev",
    r"hicatba\.dev",
    r"www\.hicatba\.dev",
    r"hicatba\.com",
    r"www\.hicatba\.com",
]
HOST_RE = "|".join(HOSTS)
PATTERN = re.compile(rf"https?://(?:{HOST_RE})(/[^\s\"'\)]*)?")


def main() -> None:
    for path in FILES:
        text = path.read_text(encoding="utf-8")

        def repl(m: re.Match[str]) -> str:
            return m.group(1) or "/"

        new, n = PATTERN.subn(repl, text)
        path.write_text(new, encoding="utf-8")
        leftovers = PATTERN.findall(new)
        print(f"{path.name}: {n} replacements, leftovers={len(leftovers)}")


if __name__ == "__main__":
    main()
