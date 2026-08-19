/**
 * Mở Booking.com bằng Chrome (Puppeteer) — chờ JS render như người dùng.
 *
 * Usage: node browser.cjs <input.json> <output.json>
 * input: { url, proxy?: { host, port, username, password }, timeout?: number }
 * output: { html, final_url, status_code } | { error }
 */
const fs = require('fs');
const path = require('path');

async function main() {
    const inputPath = process.argv[2];
    const outputPath = process.argv[3];
    if (!inputPath || !outputPath) {
        writeJson(outputPath || null, { error: 'Thiếu input/output JSON' });
        process.exit(1);
    }

    let input;
    try {
        input = JSON.parse(fs.readFileSync(inputPath, 'utf8'));
    } catch (e) {
        writeJson(outputPath, { error: 'Không đọc được input JSON: ' + e.message });
        process.exit(1);
    }

    const url = String(input.url || '').trim();
    if (!url.startsWith('http')) {
        writeJson(outputPath, { error: 'URL không hợp lệ' });
        process.exit(1);
    }

    const timeout = Math.max(20000, Number(input.timeout) || 90000);
    const proxy = input.proxy && input.proxy.host ? input.proxy : null;
    const proxyServer = proxy ? `${proxy.host}:${proxy.port || 80}` : null;

    let puppeteer;
    try {
        puppeteer = require('puppeteer');
    } catch {
        writeJson(outputPath, {
            error: 'Chưa cài Puppeteer. Chạy: cd scripts/stay-crawl && npm ci',
        });
        process.exit(1);
    }

    const { getLaunchOptions } = require('./chrome.cjs');
    let browser;
    try {
        browser = await puppeteer.launch(getLaunchOptions({ proxyServer, timeout: 60000 }));
        const page = await browser.newPage();

        if (proxy && proxy.username) {
            await page.authenticate({
                username: String(proxy.username),
                password: String(proxy.password || ''),
            });
        }

        await page.setViewport({ width: 1600, height: 1000, deviceScaleFactor: 1 });
        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        );
        await page.setExtraHTTPHeaders({
            Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language': 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
            'Upgrade-Insecure-Requests': '1',
        });

        await page.evaluateOnNewDocument(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
            Object.defineProperty(navigator, 'languages', { get: () => ['vi-VN', 'vi', 'en-US', 'en'] });
            Object.defineProperty(navigator, 'platform', { get: () => 'Win32' });
        });

        await page.setDefaultNavigationTimeout(timeout);
        const response = await page.goto(url, {
            waitUntil: 'domcontentloaded',
            timeout,
        });

        await dismissConsent(page);
        await waitForBooking(page, url, timeout);
        await scrollToBottom(page, url);
        await expandHotelBlocks(page, url);
        const pack = await collectHotelPack(page, url);
        await dismissConsent(page);

        let html = await page.content();
        html = injectStayPack(html, pack);
        const finalUrl = page.url();
        const statusCode = response ? response.status() : null;

        await browser.close();
        browser = null;

        if (!html || html.trim().length < 200) {
            writeJson(outputPath, { error: 'HTML rỗng sau khi Chrome render', final_url: finalUrl, status_code: statusCode });
            process.exit(1);
        }

        const slim = slimPack(pack);
        fs.writeFileSync(outputPath + '.html', html);
        fs.writeFileSync(outputPath + '.pack.json', JSON.stringify(slim));
        writeJson(outputPath, {
            url,
            pack: slim,
            final_url: finalUrl,
            status_code: statusCode,
            html_bytes: Buffer.byteLength(html),
            html_sidecar: true,
        });
        process.exit(0);
    } catch (error) {
        if (browser) {
            try {
                await browser.close();
            } catch {
                // ignore
            }
        }
        writeJson(outputPath, { error: error.message || String(error) });
        process.exit(1);
    }
}

async function dismissConsent(page) {
    const selectors = [
        '#onetrust-accept-btn-handler',
        'button#onetrust-accept-btn-handler',
        '[id*="onetrust"] button[accept]',
        'button[id*="accept"]',
        'button[data-testid="accept-cookies"]',
    ];
    for (const sel of selectors) {
        try {
            const el = await page.$(sel);
            if (el) {
                await el.click();
                await new Promise((r) => setTimeout(r, 400));
                return;
            }
        } catch {
            // ignore
        }
    }
}

async function waitForBooking(page, url, timeout) {
    const hotel = /\/hotel\/[a-z]{2}\//i.test(url);
    const selectors = hotel
        ? [
              '[data-testid="property-header"]',
              '#hp_hotel_name',
              'h2[class]',
              'script[type="application/ld+json"]',
              '[data-testid="title"]',
          ]
        : [
              '[data-testid="property-card"]',
              '[data-testid="title"]',
              'a[href*="/hotel/"]',
              '[data-testid="property-card-desktop"]',
          ];
    const deadline = Date.now() + Math.min(25000, timeout);
    for (const sel of selectors) {
        const left = deadline - Date.now();
        if (left < 500) break;
        try {
            await page.waitForSelector(sel, { timeout: Math.min(8000, left) });
            return;
        } catch {
            // try next
        }
    }
}

/**
 * Scroll trang dần xuống cuối để trigger lazy-load.
 * Trang danh mục: chờ thêm property-card xuất hiện.
 * Trang chi tiết: chờ các section (facilities, rooms, reviews) load.
 */
async function scrollToBottom(page, url) {
    const isListing = !/\/hotel\/[a-z]{2}\//i.test(url);
    const maxScrolls = isListing ? 60 : 30;
    const scrollStep = 800;
    const delay = isListing ? 400 : 300;

    let prevHeight = 0;
    let stableCount = 0;

    for (let i = 0; i < maxScrolls; i++) {
        const currentHeight = await page.evaluate(() => document.body.scrollHeight);
        await page.evaluate((step) => window.scrollBy(0, step), scrollStep);
        await new Promise((r) => setTimeout(r, delay));

        if (currentHeight === prevHeight) {
            stableCount++;
            if (stableCount >= 3) break;
        } else {
            stableCount = 0;
        }
        prevHeight = currentHeight;

        // Listing: click "Load more" / "Show more results" if present
        if (isListing && i % 5 === 4) {
            await page.evaluate(() => {
                const btns = document.querySelectorAll('button');
                for (const b of btns) {
                    const t = (b.textContent || '').toLowerCase();
                    if (t.includes('load more') || t.includes('xem thêm') || t.includes('show more')) {
                        b.click();
                        break;
                    }
                }
            });
            await new Promise((r) => setTimeout(r, 1200));
        }
    }

    // Scroll back up then down once more to ensure images/sections in middle load
    await page.evaluate(() => window.scrollTo(0, 0));
    await new Promise((r) => setTimeout(r, 300));
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await new Promise((r) => setTimeout(r, 800));
}

async function expandHotelBlocks(page, url) {
    if (!/\/hotel\/[a-z]{2}\//i.test(url)) {
        return;
    }
    try {
        await page.evaluate(() => {
            const re = /tất cả tiện nghi|all facilities|xem thêm tiện|show more amenities|tiện nghi chỗ nghỉ/i;
            for (const el of document.querySelectorAll('button, a, [role="button"]')) {
                const t = (el.textContent || '').replace(/\s+/g, ' ').trim();
                if (t.length < 80 && re.test(t)) {
                    el.click();
                }
            }
        });
    } catch {
        // ignore
    }
    await new Promise((r) => setTimeout(r, 1200));
    const selectors = [
        '[data-testid="rt-name-link"]',
        '[data-testid="facility-group-container"]',
        '#surroundings_block',
        '[data-testid="location-block-container"]',
        '#hotelPoliciesInc',
        '[data-testid="house-rules"]',
        '[data-testid="GalleryUnifiedDesktop-wrapper"] img',
    ];
    for (const sel of selectors) {
        try {
            await page.waitForSelector(sel, { timeout: 4000 });
        } catch {
            // section có thể lazy-fail trên một số chỗ nghỉ
        }
    }
}

function sleep(ms) {
    return new Promise((r) => setTimeout(r, ms));
}

function injectStayPack(html, pack) {
    const slim = slimPack(pack);
    const payload = JSON.stringify(slim);
    const safe = payload.replace(/</g, '\\u003c');
    const tag = `<script type="application/json" id="vt-stay-pack">${safe}</script>`;
    if (/<body\b[^>]*>/i.test(html)) {
        return html.replace(/<body\b[^>]*>/i, (open) => open + tag);
    }
    if (/<\/body>/i.test(html)) {
        return html.replace(/<\/body>/i, tag + '</body>');
    }
    return tag + html;
}

function slimPack(pack) {
    const rooms = (pack?.rooms || []).map((room) => {
        const copy = { ...room };
        delete copy.html;
        if (copy.text && copy.text.length > 1200) {
            copy.text = copy.text.slice(0, 1200);
        }
        return copy;
    });
    return {
        photos: pack?.photos || [],
        rooms,
        facilities_html: pack?.facilities_html || '',
        policies_html: pack?.policies_html || '',
        error: pack?.error || null,
        debug: pack?.debug || null,
    };
}

async function collectHotelPack(page, url) {
    if (!/\/hotel\/[a-z]{2}\//i.test(url)) {
        return { photos: [], rooms: [], facilities_html: '', policies_html: '', debug: { listing: true } };
    }
    const debug = { gallery_open: false, gallery_buttons: 0, harvested: 0, rooms_clicked: 0, rooms_ok: 0 };
    try {
        const harvested = await harvestHotelPhotos(page);
        debug.harvested = harvested.length;
        const modalPhotos = await collectGalleryPhotos(page);
        debug.gallery_open = modalPhotos.length > 0;
        debug.gallery_buttons = modalPhotos.length;
        const photos = mergePhotoLists(harvested, modalPhotos);
        await closeDialog(page);
        const facilitiesHtml = await collectFacilitiesHtml(page);
        await closeDialog(page);
        const policiesHtml = await collectPoliciesHtml(page);
        await closeDialog(page);
        const rooms = await collectRoomModals(page, debug);
        return slimPack({
            photos,
            rooms,
            facilities_html: facilitiesHtml,
            policies_html: policiesHtml,
            debug,
        });
    } catch (e) {
        debug.error = e.message || String(e);
        return slimPack({ photos: [], rooms: [], facilities_html: '', policies_html: '', error: debug.error, debug });
    }
}

function mergePhotoLists(...lists) {
    const seen = {};
    const out = [];
    for (const list of lists) {
        for (const img of list || []) {
            const url = String(img.url || img);
            const idm = url.match(/\/(\d+)\.jpe?g/i);
            const key = idm ? idm[1] : url;
            if (!url || seen[key]) continue;
            seen[key] = true;
            out.push({ url, alt: img.alt || '' });
        }
    }
    return out;
}

async function harvestHotelPhotos(page) {
    return page.evaluate(() => {
        const seen = {};
        const out = [];
        const push = (raw, alt) => {
            if (!raw) return;
            let url = String(raw).replace(/&amp;/g, '&');
            if (!/\/xdata\/images\/hotel\//.test(url)) return;
            url = url.replace(/\/hotel\/(max\d+|square\d+)\//, '/hotel/max1024x768/');
            const idm = url.match(/\/(\d+)\.jpe?g/i);
            const key = idm ? idm[1] : url.split('?')[0];
            if (seen[key]) return;
            seen[key] = true;
            if (idm && !/^https?:/i.test(url)) {
                url = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/' + idm[1] + '.jpg';
            }
            out.push({ url, alt: alt || '' });
        };
        const scan = (text) => {
            if (!text) return;
            const re = /\/xdata\/images\/hotel\/(?:max\d+|square\d+)\/(\d+)\.jpe?g/gi;
            let m;
            while ((m = re.exec(text))) {
                push('https://cf.bstatic.com/xdata/images/hotel/max1024x768/' + m[1] + '.jpg');
            }
        };
        scan(document.documentElement.innerHTML);
        document.querySelectorAll('img').forEach((img) => push(img.currentSrc || img.src, img.alt || ''));
        document.querySelectorAll('[style*="xdata/images/hotel"]').forEach((el) => {
            const m = (el.getAttribute('style') || '').match(/url\(["']?([^"')]+)/);
            if (m) push(m[1]);
        });
        return out;
    });
}

async function clickByText(page, pattern) {
    try {
        return await page.evaluate((source) => {
            const re = new RegExp(source, 'i');
            const nodes = document.querySelectorAll('button, a, [role="button"], [role="tab"]');
            for (const el of nodes) {
                const t = (el.textContent || '').replace(/\s+/g, ' ').trim();
                if (t.length < 90 && re.test(t)) {
                    el.click();
                    return t;
                }
            }
            return '';
        }, pattern);
    } catch {
        return '';
    }
}

async function closeDialog(page) {
    try {
        await page.evaluate(() => {
            const roomDlg = [...document.querySelectorAll('[role="dialog"]')].find((d) =>
                d.querySelector('[data-testid="rp-content"]'),
            );
            if (roomDlg) {
                const close = roomDlg.querySelector('button');
                if (close) {
                    close.click();
                    return;
                }
            }
            const btn =
                document.querySelector('[data-testid="sub-page--close-button"]') ||
                document.querySelector('[role="dialog"] button[aria-label*="Đóng" i]') ||
                document.querySelector('[role="dialog"] button[aria-label*="Close" i]');
            if (btn) btn.click();
        });
    } catch {
        // ignore
    }
    await sleep(200);
    try {
        await page.keyboard.press('Escape');
    } catch {
        // ignore
    }
    await sleep(250);
}

async function collectGalleryPhotos(page) {
    try {
        await page.evaluate(() => {
            const wrap =
                document.querySelector('[data-testid="GalleryUnifiedDesktop-wrapper"]') ||
                document.querySelector('#photo_wrapper');
            if (!wrap) return;
            wrap.scrollIntoView({ block: 'center' });
            const buttons = [...wrap.querySelectorAll('button')];
            const plus = buttons.find((b) => {
                const t = (b.innerText || '') + ' ' + (b.getAttribute('aria-label') || '');
                return /\+\s*\d+/.test(t) || /thư viện|xem tất cả|all photos|gallery/i.test(t);
            });
            const hit = plus || buttons[buttons.length - 1] || wrap.querySelector('img') || wrap;
            hit.click();
        });
    } catch {
        // ignore
    }
    await sleep(900);
    for (let attempt = 0; attempt < 4; attempt++) {
        const hasGrid = await page.$('[data-testid="gallery-modal-grid"]');
        if (hasGrid) {
            break;
        }
        try {
            const gridBtn = await page.$('[data-testid="return-to-grid-button"]');
            if (gridBtn) {
                await gridBtn.click();
            }
        } catch {
            // ignore
        }
        await clickByText(page, 'thư viện ảnh|xem tất cả ảnh|all photos');
        await sleep(500);
    }
    try {
        await page.waitForSelector(
            '[data-testid="gallery-modal-grid"], [data-testid="GalleryGridViewModal-wrapper"], [data-testid^="gallery-photo-thumb-"]',
            { timeout: 8000 },
        );
    } catch {
        // overlay chưa mở
    }
    await sleep(400);

    let last = 0;
    let stable = 0;
    for (let i = 0; i < 50; i++) {
        const info = await page.evaluate(() => {
            const grid =
                document.querySelector('[data-testid="gallery-modal-grid"]') ||
                document.querySelector('[data-testid="GalleryGridViewModal-wrapper"]');
            const buttons = [...document.querySelectorAll('[data-testid^="gallery-grid-photo-action-"]')];
            const lastBtn = buttons[buttons.length - 1];
            const lastThumb = [...document.querySelectorAll('[data-testid^="gallery-photo-thumb-"]')].pop();
            if (lastBtn) {
                lastBtn.scrollIntoView({ block: 'end' });
            } else if (lastThumb) {
                lastThumb.scrollIntoView({ block: 'end' });
            } else if (grid) {
                grid.scrollTop = grid.scrollHeight;
                const parent = grid.parentElement;
                if (parent) parent.scrollTop = parent.scrollHeight;
            }
            const label = buttons[0]?.getAttribute('aria-label') || '';
            const totalMatch = label.match(/\/\s*(\d+)/);
            return { count: buttons.length, total: totalMatch ? Number(totalMatch[1]) : 0 };
        });
        if (info.total && info.count >= info.total) {
            break;
        }
        if (info.count === last) {
            stable++;
            if (stable >= 4) break;
        } else {
            stable = 0;
        }
        last = info.count;
        await sleep(220);
    }

    const photos = await page.evaluate(() => {
        const out = [];
        const seen = {};
        const push = (url, alt) => {
            if (!url || seen[url]) return;
            if (!/\/xdata\/images\/hotel\//.test(url)) return;
            url = url.replace(/\/hotel\/(max\d+|square\d+)\//, '/hotel/max1024x768/');
            if (seen[url]) return;
            seen[url] = true;
            out.push({ url, alt: alt || '' });
        };
        const idFromTestid = (value) => {
            const m = String(value || '').match(/(\d{5,})$/);
            return m ? m[1] : '';
        };
        document.querySelectorAll('[data-testid^="gallery-grid-photo-action-"]').forEach((btn) => {
            const id = idFromTestid(btn.getAttribute('data-testid'));
            const img = btn.querySelector('img');
            const alt = img?.getAttribute('alt') || btn.getAttribute('aria-label') || '';
            if (img?.src) push(img.src, alt);
            if (id) push('https://cf.bstatic.com/xdata/images/hotel/max1024x768/' + id + '.jpg', alt);
        });
        document.querySelectorAll('[data-testid^="gallery-photo-thumb-"]').forEach((btn) => {
            const id = idFromTestid(btn.getAttribute('data-testid'));
            if (id) push('https://cf.bstatic.com/xdata/images/hotel/max1024x768/' + id + '.jpg', '');
        });
        return out;
    });
    await closeDialog(page);
    return photos;
}

async function collectFacilitiesHtml(page) {
    await clickByText(page, 'tất cả tiện nghi|all facilities|tiện nghi chỗ nghỉ|facilities');
    await sleep(800);
    try {
        await page.waitForSelector(
            '[data-testid="facility-group-container"], [data-testid="property-facilities-block-container"], #hp_facilities_box',
            { timeout: 5000 },
        );
    } catch {
        // ignore
    }
    return page.evaluate(() => {
        const el =
            document.querySelector('[data-testid="property-facilities-block-container"]') ||
            document.querySelector('[data-testid="facility-group-container"]')?.closest('section, div[data-testid]') ||
            document.querySelector('#hp_facilities_box');
        if (!el) {
            return [...document.querySelectorAll('[data-testid="facility-group-container"]')]
                .map((n) => n.outerHTML)
                .join('\n');
        }
        const parent = el.closest('[data-testid="property-facilities-block-container"]') || el.parentElement || el;
        const groups = parent.querySelectorAll('[data-testid="facility-group-container"]');
        if (groups.length) {
            return [...groups].map((n) => n.outerHTML).join('\n');
        }
        return el.outerHTML;
    });
}

async function collectPoliciesHtml(page) {
    await clickByText(
        page,
        'quy định chỗ nghỉ|house rules|nội quy|chính sách chỗ nghỉ|thông tin quan trọng|important information',
    );
    await sleep(800);
    try {
        await page.waitForSelector(
            '#hotelPoliciesInc, [data-testid="house-rules"], [data-testid="PropertyImportantInfo-wrapper"]',
            { timeout: 4000 },
        );
    } catch {
        // ignore
    }
    return page.evaluate(() => {
        const nodes = [
            document.querySelector('#hotelPoliciesInc'),
            document.querySelector('[data-testid="house-rules"]'),
            document.querySelector('[data-testid="house-rules-section"]'),
            document.querySelector('[data-testid="PropertyImportantInfo-wrapper"]'),
            document.querySelector('[class*="hp-policies"]'),
        ].filter(Boolean);
        return nodes.map((n) => n.outerHTML).join('\n');
    });
}

async function collectRoomModals(page, debug = {}) {
    const urlBefore = page.url();
    const count = await page
        .$$eval(
            '[data-testid="rt-name-link"], [data-testid="rt-name-no-room-page"]',
            (els) => els.length,
        )
        .catch(() => 0);
    debug.rooms_found = count;
    const max = Math.min(count, 16);
    const rooms = [];
    for (let i = 0; i < max; i++) {
        debug.rooms_clicked = i + 1;
        const name = await page.evaluate((idx) => {
            const els = [...document.querySelectorAll('[data-testid="rt-name-link"], [data-testid="rt-name-no-room-page"]')];
            const el = els[idx];
            if (!el) return '';
            el.scrollIntoView({ block: 'center' });
            return (el.innerText || '').replace(/\s+/g, ' ').trim();
        }, i);
        try {
            await page.evaluate((idx) => {
                const els = [...document.querySelectorAll('[data-testid="rt-name-link"], [data-testid="rt-name-no-room-page"]')];
                els[idx]?.click();
            }, i);
        } catch {
            continue;
        }
        try {
            await page.waitForSelector(
                '[data-testid="rp-content"], [role="dialog"][aria-label*="Thêm thông tin"], [role="dialog"][aria-label*="More information"]',
                { timeout: 8000 },
            );
        } catch {
            if (page.url() !== urlBefore) {
                try {
                    await page.goBack({ waitUntil: 'domcontentloaded', timeout: 15000 });
                    await sleep(600);
                } catch {
                    // ignore
                }
            }
            await closeDialog(page);
            continue;
        }
        await sleep(500);
        const data = await page.evaluate((fallbackName) => {
            const dialog = [...document.querySelectorAll('[role="dialog"]')].find((d) =>
                d.querySelector('[data-testid="rp-content"]'),
            );
            const root = dialog?.querySelector('[data-testid="rp-content"]') || document.querySelector('[data-testid="rp-content"]');
            if (!root) return null;
            const textOf = (sel) => (root.querySelector(sel)?.innerText || '').replace(/\s+/g, ' ').trim();
            const name = textOf('[data-testid="rp-room-title"]') || fallbackName;
            const sizeText = textOf('[data-testid="rp-room-size"]') || textOf('[data-testid="room-size-icon"]');
            const sizeMatch = sizeText.match(/(\d+)\s*m/i);
            const description = textOf('[data-testid="rp-description"]');
            const bed = (root.querySelector('[data-testid="bed-icon-double-bed"], [data-testid^="bed-icon"]')
                ?.closest('div')
                ?.innerText || '').replace(/\s+/g, ' ').trim();
            const smokingWrap = root.querySelector('[data-testid="rp-smoking-policy"]');
            const smoking = (smokingWrap?.parentElement?.innerText || smokingWrap?.innerText || '')
                .replace(/\s+/g, ' ')
                .trim();
            const highlights = [...root.querySelectorAll('[data-testid="rp-highlights-test"] [data-testid="property-unit-facility-badge-icon"]')]
                .map((el) => (el.innerText || '').replace(/\s+/g, ' ').trim())
                .filter((t) => t && t.length < 80);
            const amenity_groups = {};
            const amenities = [];
            root.querySelectorAll('[data-testid="rp-facilities"]').forEach((ul) => {
                const heading = (ul.closest('section')?.querySelector('h2')?.innerText || 'Tiện nghi')
                    .replace(/\s+/g, ' ')
                    .trim();
                const items = [...ul.querySelectorAll('li')]
                    .map((li) => (li.innerText || '').replace(/\s+/g, ' ').trim())
                    .filter((t) => t && t.length < 120);
                if (!amenity_groups[heading]) amenity_groups[heading] = [];
                amenity_groups[heading].push(...items);
                amenities.push(...items);
            });
            const photos = [];
            const seen = {};
            const pushPhoto = (url, alt) => {
                if (!url || !/\/xdata\/images\/hotel\//.test(url)) return;
                url = url.replace(/\/hotel\/(max\d+|square\d+)\//, '/hotel/max1024x768/');
                url = url.replace(/&amp;/g, '&');
                if (seen[url]) return;
                seen[url] = true;
                photos.push({ url, alt: alt || name });
            };
            const photoRoot = root.querySelector('[data-testid="roomPagePhotos"]') || root;
            photoRoot.querySelectorAll('[style*="background-image"]').forEach((el) => {
                const m = (el.getAttribute('style') || '').match(/url\(["']?([^"')]+)/);
                if (m) pushPhoto(m[1], name);
            });
            photoRoot.querySelectorAll('img').forEach((img) => {
                pushPhoto(img.currentSrc || img.src, img.alt || name);
            });
            return {
                name,
                text: (root.innerText || '').replace(/\s+/g, ' ').trim().slice(0, 1500),
                description,
                size_sqm: sizeMatch ? Number(sizeMatch[1]) : null,
                bed,
                smoking,
                highlights,
                amenity_groups,
                amenities: [...new Set(amenities)],
                photos,
            };
        }, name);
        if (data && data.name) {
            rooms.push(data);
            debug.rooms_ok = (debug.rooms_ok || 0) + 1;
        }
        await closeDialog(page);
        await sleep(250);
    }
    return rooms;
}

function writeJson(outputPath, data) {
    if (!outputPath) {
        process.stderr.write(JSON.stringify(data));
        return;
    }
    fs.mkdirSync(path.dirname(outputPath), { recursive: true });
    fs.writeFileSync(outputPath, JSON.stringify(data));
}

main();
