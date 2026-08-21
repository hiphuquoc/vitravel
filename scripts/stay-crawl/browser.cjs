/**
 * Mở Booking.com bằng Chrome (Puppeteer) — chờ JS render như người dùng.
 *
 * Usage: node browser.cjs <input.json> <output.json>
 * input: { url, mode?: basic|gallery|rooms_list|room|list, room_index?: number,
 *          skip_html?: boolean, download_images?: boolean, images_dir?: string,
 *          max_images?: number, proxy?, timeout? }
 * output: { pack, final_url, status_code } | { error }
 *         pack.photos[] giữ url nguồn; khi download_images=true thêm local_path (file trên disk).
 *         listing: pack.hotel_urls[] + debug.load_more_* (cùng header/UA với hotel detail).
 */
const fs = require('fs');
const path = require('path');

/** Booking CDN cần URL có chữ ký ?k=&o= — không được strip query khi tải. */
function decodeHtmlUrl(raw) {
    return String(raw || '').replace(/&amp;/g, '&').trim();
}

function hotelImageId(url) {
    const m = String(url).match(/\/(\d{5,})\.jpe?g/i);
    return m ? m[1] : '';
}

function pushHotelPhoto(out, seen, raw, alt = '') {
    let url = decodeHtmlUrl(raw);
    if (!url || !/\/xdata\/images\/hotel\//.test(url)) {
        return;
    }
    if (url.startsWith('//')) {
        url = 'https:' + url;
    }
    url = url.replace(/\/hotel\/(?:max\d+|square\d+)\//, '/hotel/max1024x768/');
    const id = hotelImageId(url);
    const key = id || url.split('?')[0];
    const hasSig = /[?&]k=/.test(url);
    if (seen[key]) {
        const prev = out[seen[key].idx];
        const prevSig = /[?&]k=/.test(prev.url);
        if (hasSig && !prevSig) {
            out[seen[key].idx] = { url, alt: alt || prev.alt || '' };
            seen[key].hasSig = true;
        }
        return;
    }
    seen[key] = { idx: out.length, hasSig };
    out.push({ url, alt: alt || '' });
}

function normalizePhotoList(photos) {
    const out = [];
    const seen = {};
    for (const raw of photos || []) {
        if (typeof raw === 'string') {
            pushHotelPhoto(out, seen, raw, '');
        } else if (raw && raw.url) {
            pushHotelPhoto(out, seen, raw.url, raw.alt || '');
        }
    }
    return out;
}

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

    const { getLaunchOptions, clearProfileLocks, getBaseUserDataDir } = require('./chrome.cjs');
    const wantHeaded = input.headless === false || input.headless === 'false' || input.headless === 0;
    const slowMo = Number(input.slow_mo) || 0;
    let headed = wantHeaded;
    let browser;
    try {
        const launchOnce = (opts) => puppeteer.launch(getLaunchOptions({
            proxyServer,
            timeout: 60000,
            headed,
            slowMo,
            ...opts,
        }));
        try {
            clearProfileLocks(getBaseUserDataDir());
            browser = await launchOnce();
        } catch (launchError) {
            const msg = String(launchError && launchError.message ? launchError.message : launchError);
            if (/SingletonLock|user data directory|Failed to launch|profile|already in use/i.test(msg)) {
                clearProfileLocks(getBaseUserDataDir());
                try {
                    browser = await launchOnce({ ephemeralProfile: true });
                } catch (retryErr) {
                    if (headed && /Failed to launch|vsock|socket failed|DISPLAY/i.test(String(retryErr))) {
                        headed = false;
                        browser = await puppeteer.launch(getLaunchOptions({
                            proxyServer,
                            timeout: 60000,
                            headed: false,
                            slowMo: 0,
                            ephemeralProfile: true,
                        }));
                    } else {
                        throw retryErr;
                    }
                }
            } else if (headed && /Failed to launch|vsock|socket failed|DISPLAY/i.test(msg)) {
                headed = false;
                browser = await puppeteer.launch(getLaunchOptions({
                    proxyServer,
                    timeout: 60000,
                    headed: false,
                    slowMo: 0,
                }));
            } else {
                throw launchError;
            }
        }
        const page = await browser.newPage();
        page.setDefaultTimeout(timeout);
        if (headed) {
            await page.bringToFront();
        }

        if (proxy && proxy.username) {
            await page.authenticate({
                username: String(proxy.username),
                password: String(proxy.password || ''),
            });
        }

        await page.emulateTimezone('Asia/Ho_Chi_Minh');
        await page.setViewport({ width: 1600, height: 1000, deviceScaleFactor: 1 });
        // Không setExtraHTTPHeaders(sec-ch-ua): đụng Client Hints thật của Chrome → Booking giữ skeleton.
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
        });
        await applyChromeIdentity(page);
        await page.evaluateOnNewDocument(installStealth, process.platform === 'linux');
        const net = attachNetworkProbe(page);

        const mode = String(input.mode || 'basic').toLowerCase();
        const skipHtml = Boolean(input.skip_html);
        const downloadImages = input.download_images === true || input.download_images === 1 || input.download_images === 'true';
        const imagesDir = String(input.images_dir || '').trim();
        const maxImages = Math.max(1, Number(input.max_images) || 120);
        const downloadOpts = {
            downloadImages: downloadImages && imagesDir !== '',
            imagesDir,
            maxImages,
            concurrency: Math.max(1, Number(input.download_concurrency) || 8),
        };
        const isHotel = /\/hotel\/[a-z]{2}\//i.test(url);
        const isListing = !isHotel || mode === 'list';
        // Cùng ngày/khách + lang với hotel detail — listing thiếu checkin hay ra skeleton / card mỏng.
        let gotoUrl = withStayDates(url);
        if (isHotel && mode === 'gallery') {
            gotoUrl = withActiveTab(gotoUrl, 'photosGallery');
        }

        await page.setDefaultNavigationTimeout(timeout);
        let response;
        try {
            response = await page.goto(gotoUrl, {
                waitUntil: 'domcontentloaded',
                timeout,
            });
        } catch {
            response = null;
        }
        await waitQuiet(page, 450);

        await dismissConsent(page);
        await waitForBooking(page, url, timeout);
        await waitForBookingApis(net, isListing ? 8000 : 5500);
        await waitQuiet(page, 350);

        let pack;
        if (isListing) {
            const expandDebug = await expandListingResults(page);
            pack = await collectListingPack(page, expandDebug);
        } else if (mode === 'gallery') {
            pack = await collectGalleryPack(page, downloadOpts);
        } else if (mode === 'rooms_list') {
            await scrollUntilRooms(page);
            pack = await collectRoomsListPack(page);
        } else if (mode === 'room') {
            await scrollUntilRooms(page);
            pack = await collectOneRoomPack(
                page,
                Number(input.room_index) || 0,
                String(input.room_name || ''),
                downloadOpts,
                String(input.room_hash || ''),
            );
        } else {
            await scrollToBottom(page, url);
            pack = await collectBasicPack(page, url);
        }
        await dismissConsent(page);

        pack.debug = {
            ...(pack.debug || {}),
            headed,
            proxy: Boolean(proxy),
            network: net.snapshot(),
        };
        const slim = slimPack(pack);
        const finalUrl = page.url();
        const statusCode = response ? response.status() : null;

        let html = '';
        if (!skipHtml) {
            html = await page.content();
            html = injectStayPack(html, slim);
            if (!html || html.trim().length < 200) {
                await browser.close();
                writeJson(outputPath, { error: 'HTML rỗng sau khi Chrome render', final_url: finalUrl, status_code: statusCode });
                process.exit(1);
            }
            fs.writeFileSync(outputPath + '.html', html);
        }

        if (headed) {
            await sleep(400);
        }
        await browser.close();
        browser = null;

        fs.writeFileSync(outputPath + '.pack.json', JSON.stringify(slim));
        writeJson(outputPath, {
            url,
            pack: slim,
            final_url: finalUrl,
            status_code: statusCode,
            html_bytes: Buffer.byteLength(html),
            html_sidecar: html !== '',
            mode,
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

function chromeVersion() {
    const fromPath = String(process.env.STAY_CRAWL_CHROME || '');
    const m = fromPath.match(/(\d+)\.(\d+)\.(\d+)\.(\d+)/);
    if (m) {
        return { major: m[1], full: `${m[1]}.${m[2]}.${m[3]}.${m[4]}` };
    }
    return { major: '142', full: '142.0.7444.175' };
}

function realUserAgent() {
    const v = chromeVersion();
    if (process.platform === 'linux') {
        return `Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/${v.full} Safari/537.36`;
    }
    return `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/${v.full} Safari/537.36`;
}

async function applyChromeIdentity(page) {
    const v = chromeVersion();
    const ua = realUserAgent();
    const platform = process.platform === 'linux' ? 'Linux' : 'Windows';
    const brands = [
        { brand: 'Not:A-Brand', version: '24' },
        { brand: 'Chromium', version: v.major },
        { brand: 'Google Chrome', version: v.major },
    ];
    try {
        const client = await page.createCDPSession();
        await client.send('Network.setUserAgentOverride', {
            userAgent: ua,
            acceptLanguage: 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
            platform: process.platform === 'linux' ? 'Linux x86_64' : 'Win32',
            userAgentMetadata: {
                brands,
                fullVersionList: [
                    { brand: 'Not:A-Brand', version: '10.0.0.0' },
                    { brand: 'Chromium', version: v.full },
                    { brand: 'Google Chrome', version: v.full },
                ],
                platform,
                platformVersion: '',
                architecture: 'x86',
                model: '',
                mobile: false,
                bitness: '64',
                wow64: false,
            },
        });
    } catch {
        await page.setUserAgent(ua);
    }
}

function attachNetworkProbe(page) {
    const stats = {
        document: 0,
        graphql_ok: 0,
        graphql_blocked: 0,
        graphql_other: 0,
        xhr_ok: 0,
        xhr_blocked: 0,
        samples: [],
        challenge: false,
    };
    const track = (url, status, type) => {
        const u = String(url || '');
        const isGraphql = /\/dml\/graphql|\/dml\/|graphql/i.test(u);
        const isDoc =
            type === 'document' ||
            /booking\.com\/hotel\//i.test(u) ||
            /booking\.com\/searchresults/i.test(u) ||
            /booking\.com\/(?:city|region|district|landmark)\//i.test(u);
        if (status === 403 || status === 429 || /challenge|captcha/i.test(u)) {
            stats.challenge = true;
        }
        if (isGraphql) {
            if (status >= 200 && status < 300) stats.graphql_ok += 1;
            else if (status === 403 || status === 429) stats.graphql_blocked += 1;
            else stats.graphql_other += 1;
            if (stats.samples.length < 8) {
                stats.samples.push({ status, path: u.replace(/\?.*$/, '').slice(-80) });
            }
        } else if (!isDoc && /booking\.com/i.test(u) && (type === 'xhr' || type === 'fetch')) {
            if (status >= 200 && status < 300) stats.xhr_ok += 1;
            else if (status === 403 || status === 429) stats.xhr_blocked += 1;
        }
        if (isDoc && status) stats.document = status;
    };
    page.on('response', (res) => {
        try {
            const req = res.request();
            track(res.url(), res.status(), req.resourceType());
        } catch {
            // ignore
        }
    });
    return {
        snapshot() {
            let hint = 'ok';
            if (stats.graphql_blocked > 0 || (stats.challenge && stats.graphql_ok === 0)) {
                hint = 'proxy_or_ip';
            } else if (stats.graphql_ok === 0 && stats.xhr_ok === 0) {
                hint = 'fingerprint_or_lazy';
            } else if (stats.graphql_ok > 0) {
                hint = 'api_ok_wait_dom';
            }
            return { ...stats, hint };
        },
    };
}

async function waitForBookingApis(net, timeoutMs = 12000) {
    const deadline = Date.now() + timeoutMs;
    while (Date.now() < deadline) {
        const s = net.snapshot();
        if (s.graphql_ok > 0 || s.graphql_blocked > 0) return;
        await sleep(400);
    }
}

/**
 * Ngày crawl cố định để #hprt-table hiện đủ hạng phòng + rate options.
 * Tháng kế tiếp: check-in = Thứ 2 đầu tiên, check-out = Thứ 4 cùng tuần (2 đêm).
 * Luôn ghi đè checkin/checkout + guest params (không giữ ngày cũ trên URL).
 */
function computeStayCrawlDates(now = new Date()) {
    const y = now.getFullYear();
    const m = now.getMonth(); // 0-based
    let ny = y;
    let nm = m + 1; // next month index
    if (nm > 11) {
        nm = 0;
        ny += 1;
    }
    let d = new Date(Date.UTC(ny, nm, 1));
    while (d.getUTCDay() !== 1) {
        d = new Date(Date.UTC(ny, nm, d.getUTCDate() + 1));
    }
    const checkin = d.toISOString().slice(0, 10);
    const cout = new Date(d);
    cout.setUTCDate(cout.getUTCDate() + 2);
    return {
        checkin,
        checkout: cout.toISOString().slice(0, 10),
        nights: 2,
        group_adults: 2,
        req_adults: 2,
        no_rooms: 1,
        group_children: 0,
        req_children: 0,
    };
}

function withStayDates(url) {
    try {
        const u = new URL(url);
        if (!/booking\.com$/i.test(u.hostname) && !/\.booking\.com$/i.test(u.hostname)) {
            return url;
        }
        const path = String(u.pathname || '').toLowerCase();
        const isHotelPath = /\/hotel\/[a-z]{2}\//i.test(path);
        const isListingPath =
            path.includes('searchresults') ||
            path.includes('/city/') ||
            path.includes('/region/') ||
            path.includes('/district/') ||
            path.includes('/landmark/') ||
            path.includes('/place/') ||
            path.includes('/airport/') ||
            path.includes('/country/');
        if (!isHotelPath && !isListingPath) {
            return url;
        }
        const dates = computeStayCrawlDates();
        u.searchParams.set('checkin', dates.checkin);
        u.searchParams.set('checkout', dates.checkout);
        u.searchParams.set('group_adults', String(dates.group_adults));
        u.searchParams.set('req_adults', String(dates.req_adults));
        u.searchParams.set('no_rooms', String(dates.no_rooms));
        u.searchParams.set('group_children', String(dates.group_children));
        u.searchParams.set('req_children', String(dates.req_children));
        if (!u.searchParams.get('lang')) {
            u.searchParams.set('lang', 'vi');
        }
        return u.toString();
    } catch {
        return url;
    }
}

function installStealth(isLinux) {
    try {
        Object.defineProperty(Navigator.prototype, 'webdriver', { get: () => undefined });
    } catch {
        // ignore
    }
    Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
    Object.defineProperty(navigator, 'languages', { get: () => ['vi-VN', 'vi', 'en-US', 'en'] });
    Object.defineProperty(navigator, 'platform', { get: () => (isLinux ? 'Linux x86_64' : 'Win32') });
    Object.defineProperty(navigator, 'hardwareConcurrency', { get: () => 8 });
    Object.defineProperty(navigator, 'deviceMemory', { get: () => 8 });
    Object.defineProperty(navigator, 'maxTouchPoints', { get: () => 0 });
    Object.defineProperty(navigator, 'plugins', {
        get: () => [{ name: 'PDF Viewer' }, { name: 'Chrome PDF Viewer' }, { name: 'Chromium PDF Viewer' }],
    });
    window.chrome = window.chrome || { runtime: {}, loadTimes: function () {}, csi: function () {} };
}

async function waitQuiet(page, idleMs = 700) {
    try {
        await page.waitForNetworkIdle({ idleTime: Math.min(500, idleMs), timeout: idleMs + 3500 });
    } catch {
        // trang Booking vẫn có ping — không chặn crawler
    }
    await sleep(Math.min(350, idleMs));
}

async function scrollToBottom(page, url) {
    // Chỉ dùng cho hotel detail — listing dùng expandListingResults (scroll + «Tải thêm kết quả»).
    let prevHeight = 0;
    let stable = 0;
    const max = 16;
    for (let i = 0; i < max; i++) {
        await page.evaluate(() => {
            window.scrollBy({ top: Math.round(window.innerHeight * 0.9), behavior: 'smooth' });
        });
        await sleep(240);
        const info = await page.evaluate(() => ({
            height: document.body.scrollHeight,
            atBottom: window.innerHeight + window.scrollY >= document.body.scrollHeight - 120,
        }));
        if (info.height === prevHeight && info.atBottom) {
            stable++;
            if (stable >= 2) break;
        } else {
            stable = 0;
        }
        prevHeight = info.height;
    }
    await waitQuiet(page, 450);
    await page.evaluate(() => {
        const el =
            document.querySelector('[data-testid="property-facilities-block-container"]') ||
            document.querySelector('[data-testid="PropertySectionsBelowRoomsTable-wrapper"]') ||
            document.querySelector('.hp--popular_facilities') ||
            document.querySelector('[data-testid="property-most-popular-facilities-wrapper"]');
        if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    });
    await sleep(400);
}

/** Nút infinite-scroll Booking: «Tải thêm kết quả» / Load more results — phải click đến khi mất. */
async function listingMetrics(page) {
    return page.evaluate(() => {
        const cards = document.querySelectorAll(
            '[data-testid="property-card"], [data-testid="property-card-container"], [data-testid="sr-property-card"]',
        ).length;
        const hotelLinks = document.querySelectorAll('a[href*="/hotel/"]').length;
        return {
            height: document.body.scrollHeight,
            atBottom: window.innerHeight + window.scrollY >= document.body.scrollHeight - 160,
            cards: Math.max(cards, 0),
            hotel_links: hotelLinks,
        };
    });
}

async function clickListingLoadMore(page) {
    return page.evaluate(() => {
        const match = (text) => {
            const t = String(text || '')
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();
            if (!t || t.length > 80) return false;
            return (
                t.includes('tải thêm kết quả') ||
                t.includes('load more results') ||
                t.includes('show more results') ||
                t.includes('xem thêm kết quả') ||
                t.includes('hiển thị thêm kết quả') ||
                (t.includes('tải thêm') && t.includes('kết quả')) ||
                (t.includes('load more') && (t.includes('result') || t.includes('propert'))) ||
                t === 'load more' ||
                t === 'tải thêm'
            );
        };
        const nodes = document.querySelectorAll('button, a[role="button"], [role="button"]');
        for (const el of nodes) {
            if (!(el instanceof HTMLElement)) continue;
            if (el.disabled || el.getAttribute('aria-disabled') === 'true') continue;
            const style = window.getComputedStyle(el);
            if (style.display === 'none' || style.visibility === 'hidden') continue;
            const label = (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();
            if (!match(label)) continue;
            el.scrollIntoView({ block: 'center', inline: 'nearest' });
            el.click();
            return true;
        }
        return false;
    });
}

async function listingHasLoadMore(page) {
    return page.evaluate(() => {
        const match = (text) => {
            const t = String(text || '')
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();
            if (!t || t.length > 80) return false;
            return (
                t.includes('tải thêm kết quả') ||
                t.includes('load more results') ||
                t.includes('show more results') ||
                t.includes('xem thêm kết quả') ||
                t.includes('hiển thị thêm kết quả') ||
                (t.includes('tải thêm') && t.includes('kết quả')) ||
                (t.includes('load more') && (t.includes('result') || t.includes('propert'))) ||
                t === 'load more' ||
                t === 'tải thêm'
            );
        };
        const nodes = document.querySelectorAll('button, a[role="button"], [role="button"]');
        for (const el of nodes) {
            if (!(el instanceof HTMLElement)) continue;
            if (el.disabled || el.getAttribute('aria-disabled') === 'true') continue;
            const style = window.getComputedStyle(el);
            if (style.display === 'none' || style.visibility === 'hidden') continue;
            const label = (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();
            if (match(label)) return true;
        }
        return false;
    });
}

/**
 * Listing Booking: scroll lazy-load + click «Tải thêm kết quả» đến khi nút biến mất và DOM ổn định.
 */
async function expandListingResults(page) {
    const debug = {
        scrolls: 0,
        load_more_clicks: 0,
        cards_start: 0,
        cards_end: 0,
        hotel_links_end: 0,
        stopped: null,
        rounds: 0,
    };
    const start = await listingMetrics(page);
    debug.cards_start = start.cards;

    const maxRounds = 90;
    let stagnant = 0;

    for (let round = 0; round < maxRounds; round++) {
        debug.rounds = round + 1;
        const before = await listingMetrics(page);

        if (await clickListingLoadMore(page)) {
            debug.load_more_clicks += 1;
            stagnant = 0;
            await waitQuiet(page, 900);
            await sleep(700);
            continue;
        }

        await page.evaluate(() => {
            window.scrollBy({ top: Math.round(window.innerHeight * 0.92), behavior: 'smooth' });
        });
        debug.scrolls += 1;
        await sleep(380);

        if (await clickListingLoadMore(page)) {
            debug.load_more_clicks += 1;
            stagnant = 0;
            await waitQuiet(page, 900);
            await sleep(700);
            continue;
        }

        const after = await listingMetrics(page);
        const grew =
            after.cards > before.cards ||
            after.hotel_links > before.hotel_links ||
            after.height > before.height + 40;

        if (grew) {
            stagnant = 0;
            continue;
        }

        stagnant += 1;
        const stillHasBtn = await listingHasLoadMore(page);
        if (!stillHasBtn && after.atBottom && stagnant >= 3) {
            debug.stopped = 'exhausted';
            break;
        }
        if (stillHasBtn && stagnant >= 2) {
            // Nút còn nhưng click fail (overlay) — thử lại click thô.
            await page.evaluate(() => {
                const btns = document.querySelectorAll('button');
                for (const b of btns) {
                    const t = (b.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
                    if (t.includes('tải thêm kết quả') || t.includes('load more results')) {
                        b.scrollIntoView({ block: 'center' });
                        b.click();
                        break;
                    }
                }
            });
            await waitQuiet(page, 800);
            await sleep(600);
        }
        if (stagnant >= 8) {
            debug.stopped = stillHasBtn ? 'load_more_stuck' : 'stagnant';
            break;
        }
    }

    // Cuối cùng: còn nút thì click đến khi hết (an toàn).
    for (let i = 0; i < 40; i++) {
        if (!(await clickListingLoadMore(page))) {
            break;
        }
        debug.load_more_clicks += 1;
        await waitQuiet(page, 900);
        await sleep(650);
    }

    const end = await listingMetrics(page);
    debug.cards_end = end.cards;
    debug.hotel_links_end = end.hotel_links;
    if (!debug.stopped) {
        debug.stopped = (await listingHasLoadMore(page)) ? 'max_rounds' : 'complete';
    }
    await waitQuiet(page, 500);
    return debug;
}

async function collectListingPack(page, expandDebug) {
    const hotelUrls = await page.evaluate(() => {
        const out = [];
        const seen = {};
        const push = (href) => {
            if (!href) return;
            try {
                const u = new URL(href, location.origin);
                if (!/\/hotel\/[a-z]{2}\/[^/]+\.html/i.test(u.pathname)) return;
                u.hash = '';
                // Bỏ tracking query nhưng giữ path; canonicalize phía PHP.
                const key = u.origin + u.pathname.replace(/\.(en-gb|en-us|vi|fr|de)(\.html)$/i, '$2').toLowerCase();
                if (seen[key]) return;
                seen[key] = true;
                out.push(u.origin + u.pathname + (u.search || ''));
            } catch {
                // ignore
            }
        };
        const sels = [
            '[data-testid="property-card"] a[href*="/hotel/"]',
            '[data-testid="property-card-container"] a[href*="/hotel/"]',
            '[data-testid="title-link"]',
            'a[data-testid="title-link"]',
            'a[href*="/hotel/"][data-testid]',
            'a[href*="booking.com/hotel/"]',
            'a[href^="/hotel/"]',
        ];
        for (const sel of sels) {
            document.querySelectorAll(sel).forEach((a) => push(a.href || a.getAttribute('href')));
        }
        document.querySelectorAll('a[href*="/hotel/"]').forEach((a) => push(a.href || a.getAttribute('href')));
        return out;
    });

    return {
        photos: [],
        rooms: [],
        hotel_urls: hotelUrls,
        facilities_html: '',
        policies_html: '',
        crawl_dates: computeStayCrawlDates(),
        debug: {
            listing: true,
            mode: 'list',
            ...(expandDebug || {}),
            hotel_url_count: hotelUrls.length,
        },
    };
}

const ROOM_NAME_SEL =
    '[data-testid="rt-name-link"], [data-testid="rt-name-no-room-page"], a[id^="room_type_id"], a.hprt-roomtype-link';

async function scrollUntilRooms(page) {
    await clickByText(page, 'chọn phòng|select rooms|xem phòng|see availability|kiểm tra tình trạng');
    for (let i = 0; i < 14; i++) {
        const found = await page.$$eval(ROOM_NAME_SEL, (els) => els.length).catch(() => 0);
        if (found > 0) {
            await page.evaluate((sel) => {
                const el =
                    document.querySelector(sel) ||
                    document.querySelector('#rooms_table') ||
                    document.querySelector('#available_rooms') ||
                    document.querySelector('[data-testid="PropertyRoomsList"]');
                if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }, ROOM_NAME_SEL);
            await sleep(500);
            return;
        }
        await page.evaluate(() => {
            window.scrollBy({ top: Math.round(window.innerHeight * 0.8), behavior: 'smooth' });
        });
        await sleep(300);
    }
    await waitForRoomLinks(page, 12000);
}

async function waitForRoomLinks(page, maxWait = 12000) {
    const deadline = Date.now() + maxWait;
    while (Date.now() < deadline) {
        const n = await page.$$eval(ROOM_NAME_SEL, (els) => els.length).catch(() => 0);
        if (n > 0) return n;
        await sleep(300);
    }
    return 0;
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
    await new Promise((r) => setTimeout(r, 700));
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
            await page.waitForSelector(sel, { timeout: 3000 });
        } catch {
            // section có thể lazy-fail trên một số chỗ nghỉ
        }
    }
}

function withActiveTab(url, tab) {
    try {
        const u = new URL(url);
        u.searchParams.set('activeTab', tab);
        return u.toString();
    } catch {
        return url;
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
        hotel_urls: Array.isArray(pack?.hotel_urls) ? pack.hotel_urls : [],
        room_index: pack?.room_index ?? null,
        room_hash: pack?.room_hash ?? null,
        facilities_html: pack?.facilities_html || '',
        policies_html: pack?.policies_html || '',
        hprt_html: pack?.hprt_html || '',
        crawl_dates: pack?.crawl_dates || null,
        error: pack?.error || null,
        debug: pack?.debug || null,
    };
}

async function expandAllFacilities(page) {
    await page.evaluate(() => {
        const el =
            document.querySelector('[data-testid="property-facilities-block-container"]') ||
            document.querySelector('.hp--popular_facilities') ||
            document.querySelector('[data-testid="property-most-popular-facilities-wrapper"]');
        if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    });
    await sleep(350);
    await clickByText(page, 'tiện nghi chỗ nghỉ|facilities of the property|property facilities');
    await sleep(280);
    for (let i = 0; i < 8; i++) {
        const clicked = await clickByText(
            page,
            'tất cả tiện nghi|all facilities|xem thêm tiện|show more amenities|hiển thị thêm tiện|show all facilities|xem tất cả tiện|see all facilities',
        );
        if (!clicked) {
            break;
        }
        await sleep(500);
    }
    await waitForFacilityGroups(page, 10000);
}

async function waitForFacilityGroups(page, maxWait = 10000) {
    const deadline = Date.now() + maxWait;
    while (Date.now() < deadline) {
        const n = await page
            .$$eval('[data-testid="facility-group-container"]', (els) => els.length)
            .catch(() => 0);
        if (n >= 3) {
            return n;
        }
        await sleep(350);
    }
    return 0;
}

async function collectBasicPack(page, url) {
    const debug = { mode: 'basic', harvested: 0, facility_html: 0 };
    try {
        await expandHotelBlocks(page, url);
        await expandAllFacilities(page);
        const facilitiesHtml = await collectFacilitiesHtml(page);
        await closeDialog(page);
        const policiesHtml = await collectPoliciesHtml(page);
        await closeDialog(page);
        const photos = await harvestHotelPhotos(page);
        debug.harvested = photos.length;
        debug.facility_html = facilitiesHtml.length;
        debug.facility_groups = (facilitiesHtml.match(/facility-group-container/g) || []).length;
        return slimPack({
            photos,
            rooms: [],
            facilities_html: facilitiesHtml,
            policies_html: policiesHtml,
            debug,
        });
    } catch (e) {
        debug.error = e.message || String(e);
        return slimPack({ photos: [], rooms: [], facilities_html: '', policies_html: '', error: debug.error, debug });
    }
}

async function collectGalleryPack(page, downloadOpts = {}) {
    const debug = { mode: 'gallery' };
    try {
        const opened = await openPhotosGallery(page);
        debug.gallery_open = opened;
        let photos = await collectGalleryPhotos(page);
        debug.gallery_buttons = photos.length;
        debug.gallery_open = opened || photos.length > 0;
        debug.active_tab = (() => {
            try {
                return new URL(page.url()).searchParams.get('activeTab');
            } catch {
                return null;
            }
        })();
        if (downloadOpts.downloadImages) {
            const dl = await downloadPhotoList(
                page,
                normalizePhotoList(photos),
                downloadOpts.imagesDir,
                downloadOpts.maxImages,
                downloadOpts.concurrency || 8,
            );
            photos = dl.photos;
            debug.download = {
                requested: dl.requested,
                downloaded: dl.downloaded,
                failed: dl.failed,
                images_dir: downloadOpts.imagesDir,
            };
        }
        return slimPack({ photos, rooms: [], facilities_html: '', policies_html: '', debug });
    } catch (e) {
        debug.error = e.message || String(e);
        return slimPack({ photos: [], rooms: [], facilities_html: '', policies_html: '', error: debug.error, debug });
    }
}

async function collectRoomsListPack(page) {
    const debug = { mode: 'rooms_list' };
    try {
        await scrollRoomsIntoView(page);
        debug.rooms_found_wait = await waitForRoomLinks(page, 12000);
        const crawlDates = computeStayCrawlDates();
        const hprtHtml = await collectHprtHtml(page);
        const rooms = await listRoomNames(page);
        debug.rooms_found = rooms.length;
        debug.hprt_html = hprtHtml.length;
        debug.crawl_dates = crawlDates;
        return slimPack({
            photos: [],
            rooms,
            room_index: null,
            facilities_html: '',
            policies_html: '',
            hprt_html: hprtHtml,
            crawl_dates: crawlDates,
            debug,
        });
    } catch (e) {
        debug.error = e.message || String(e);
        return slimPack({ photos: [], rooms: [], facilities_html: '', policies_html: '', error: debug.error, debug });
    }
}

async function collectHprtHtml(page) {
    return page.evaluate(() => {
        const t = document.querySelector('#hprt-table, table.hprt-table');
        return t ? t.outerHTML : '';
    }).catch(() => '');
}

async function collectOneRoomPack(page, index, fallbackName = '', downloadOpts = {}, roomHash = '') {
    const debug = { mode: 'room', room_index: index, room_hash: roomHash || null, rooms_clicked: 0, rooms_ok: 0 };
    try {
        let rooms = [];
        if (roomHash) {
            const opened = await openRoomByHash(page, roomHash, fallbackName, downloadOpts, debug);
            if (opened) {
                rooms = [opened];
                debug.rooms_ok = 1;
            } else {
                debug.hash_failed = true;
            }
        }
        if (rooms.length === 0) {
            await scrollRoomsIntoView(page);
            rooms = await collectRoomModals(page, debug, index, 1);
            if (fallbackName && rooms[0] && !rooms[0].name) {
                rooms[0].name = fallbackName;
            }
            if (downloadOpts.downloadImages && rooms[0] && Array.isArray(rooms[0].photos)) {
                const roomDir = path.join(downloadOpts.imagesDir, 'room-' + String(index));
                const dl = await downloadPhotoList(
                    page,
                    normalizePhotoList(rooms[0].photos),
                    roomDir,
                    Math.min(downloadOpts.maxImages || 40, 40),
                    downloadOpts.concurrency || 6,
                );
                rooms[0].photos = dl.photos;
                debug.download = {
                    requested: dl.requested,
                    downloaded: dl.downloaded,
                    failed: dl.failed,
                    images_dir: roomDir,
                };
            }
            if (rooms[0]) {
                debug.rooms_ok = 1;
                debug.open_via = debug.hash_failed ? 'click_fallback' : 'click';
            }
        }
        return slimPack({
            photos: [],
            rooms,
            room_index: index,
            room_hash: roomHash || null,
            facilities_html: '',
            policies_html: '',
            debug,
        });
    } catch (e) {
        debug.error = e.message || String(e);
        return slimPack({ photos: [], rooms: [], facilities_html: '', policies_html: '', error: debug.error, debug });
    }
}

/**
 * Mở modal phòng: vào trang hotel → click link #RD… trên bảng phòng.
 * goto(url#RD…) thường không mở SPA overlay Booking.
 */
async function openRoomByHash(page, hash, fallbackName, downloadOpts = {}, debug = {}) {
    const h = hash.startsWith('#') ? hash : '#' + hash;
    const id = h.replace(/^#/, '');
    debug.room_hash = h;

    const withLang = page.url().split('#')[0];
    try {
        await page.goto(withLang, { waitUntil: 'domcontentloaded', timeout: 45000 });
    } catch {
        // keep current
    }
    await dismissConsent(page);
    await scrollRoomsIntoView(page);
    await sleep(350);

    let clicked = await page.evaluate((targetHash, targetId, nameHint) => {
        const wantId = String(targetId || '').toLowerCase();
        const links = [...document.querySelectorAll('a[data-testid="rt-name-link"], a[href*="#RD"], a[href^="#RD"]')];
        const match =
            links.find((a) => {
                const href = String(a.getAttribute('href') || '');
                return href === targetHash || href.endsWith(targetHash) || (wantId && href.includes(wantId));
            }) ||
            links.find((a) => {
                const row = a.closest('[id^="RD"], tr[id^="RD"], [data-block-id^="RD"]');
                const rid = (row?.id || row?.getAttribute('data-block-id') || '').toLowerCase();
                return rid && rid === wantId;
            }) ||
            (nameHint
                ? links.find((a) =>
                    (a.innerText || '')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .toLowerCase()
                        .includes(String(nameHint).toLowerCase().slice(0, 24)),
                )
                : null);
        if (!match) return false;
        match.scrollIntoView({ block: 'center' });
        match.click();
        return true;
    }, h, id, fallbackName || '');
    debug.room_clicked = clicked;

    if (!clicked) {
        await page.evaluate((target) => {
            window.location.hash = target;
        }, h);
        debug.room_hash_set = true;
        await sleep(500);
    }

    await sleep(400);
    let opened = await waitForRoomDetail(page, 14000);
    debug.room_modal = opened;

    if (!opened && fallbackName) {
        clicked = await page.evaluate((nameHint, sel) => {
            const want = String(nameHint || '').toLowerCase();
            if (!want) return false;
            const els = [...document.querySelectorAll(sel)];
            const el = els.find((n) =>
                (n.innerText || '').replace(/\s+/g, ' ').trim().toLowerCase().includes(want.slice(0, 28)),
            );
            if (!el) return false;
            el.scrollIntoView({ block: 'center' });
            el.click();
            return true;
        }, fallbackName, ROOM_NAME_SEL);
        debug.name_clicked = clicked;
        if (clicked) {
            opened = await waitForRoomDetail(page, 14000);
            debug.room_modal = opened;
        }
    }

    if (!opened) {
        return null;
    }
    try {
        await page.waitForSelector('[data-testid="rp-content"]', { timeout: 12000 });
    } catch {
        // modal có thể thiếu wrapper nhưng vẫn có dữ liệu
    }
    await expandRoomModal(page);
    try {
        await page.waitForSelector(
            '[data-testid="roomPagePhotos"] [style*="background-image"], [data-testid="roomPagePhotos"] img, [data-testid="rp-facilities"] li',
            { timeout: 10000 },
        );
    } catch {
        // ignore
    }
    let data = await scrapeRoomModal(page, fallbackName);
    if (!data) {
        return null;
    }
    data.photos = normalizePhotoList(data.photos);
    if (downloadOpts.downloadImages && data.photos.length) {
        const roomDir = path.join(downloadOpts.imagesDir, 'room-' + h.replace(/[^a-zA-Z0-9]/g, ''));
        fs.mkdirSync(roomDir, { recursive: true });
        const dl = await downloadPhotoList(
            page,
            data.photos,
            roomDir,
            Math.min(downloadOpts.maxImages || 40, 40),
            downloadOpts.concurrency || 6,
        );
        data.photos = dl.photos;
        debug.download = {
            requested: dl.requested,
            downloaded: dl.downloaded,
            failed: dl.failed,
            images_dir: roomDir,
        };
    }
    debug.room_photos = data.photos?.length || 0;
    debug.room_amenities = data.amenities?.length || 0;
    debug.open_via = clicked ? 'link_click' : 'hash';
    data.hash = h;
    const idMatch = id.match(/^RD(\d+)$/i) || id.match(/(\d{6,})/);
    if (idMatch) {
        data.room_id = idMatch[1];
    }
    return data;
}

/**
 * Tải ảnh qua phiên Chrome (cookie + URL có chữ ký ?k=).
 * Song song theo batch để ~100 ảnh không quá chậm.
 */
async function downloadPhotoList(page, photos, imagesDir, max = 120, concurrency = 8) {
    const list = normalizePhotoList(Array.isArray(photos) ? photos : []);
    const limit = Math.min(list.length, Math.max(1, max));
    fs.mkdirSync(imagesDir, { recursive: true });
    let downloaded = 0;
    let failed = 0;
    const out = new Array(list.length);
    const referer = (() => {
        try {
            return page.url() || 'https://www.booking.com/';
        } catch {
            return 'https://www.booking.com/';
        }
    })();

    async function downloadOne(photo, index) {
        const url = String(photo.url || '').trim();
        if (!url) {
            out[index] = photo;
            return;
        }
        const idm = url.match(/\/(\d{5,})\.jpe?g/i);
        const stem = idm ? idm[1] : String(index + 1).padStart(4, '0');
        const dest = path.join(imagesDir, stem + '.jpg');
        const row = { ...photo };
        try {
            if (fs.existsSync(dest) && fs.statSync(dest).size >= 800) {
                row.local_path = dest;
                row.bytes = fs.statSync(dest).size;
                downloaded++;
                out[index] = row;
                return;
            }
            const response = await page.request.get(url, {
                headers: {
                    Accept: 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    Referer: referer,
                },
                timeout: 35000,
            });
            let body = null;
            if (response.ok()) {
                body = Buffer.from(await response.body());
            } else {
                body = await fetchImageInPage(page, url, referer);
            }
            if (!body || body.length < 800) {
                failed++;
                out[index] = row;
                return;
            }
            fs.writeFileSync(dest, body);
            row.local_path = dest;
            row.bytes = body.length;
            downloaded++;
            out[index] = row;
        } catch {
            failed++;
            out[index] = row;
        }
    }

    const batchSize = Math.max(1, Math.min(concurrency, 12));
    for (let i = 0; i < limit; i += batchSize) {
        const jobs = [];
        for (let j = i; j < Math.min(i + batchSize, limit); j++) {
            jobs.push(downloadOne(list[j], j));
        }
        await Promise.all(jobs);
    }
    for (let i = limit; i < list.length; i++) {
        out[i] = list[i];
    }

    return {
        photos: out.filter(Boolean),
        requested: limit,
        downloaded,
        failed,
    };
}

/** Fallback: fetch trong context trang (cookie + CORS như trình duyệt). */
async function fetchImageInPage(page, url, referer) {
    try {
        const bytes = await page.evaluate(async (imgUrl, ref) => {
            const res = await fetch(imgUrl, {
                credentials: 'include',
                headers: { Accept: 'image/*', Referer: ref },
            });
            if (!res.ok) return null;
            const buf = await res.arrayBuffer();
            if (buf.byteLength < 800) return null;
            return Array.from(new Uint8Array(buf));
        }, url, referer);
        if (!bytes || !bytes.length) return null;
        return Buffer.from(bytes);
    } catch {
        return null;
    }
}

async function scrollRoomsIntoView(page) {
    await page.evaluate((sel) => {
        const el =
            document.querySelector(sel) ||
            document.querySelector('#rooms_table') ||
            document.querySelector('#available_rooms') ||
            document.querySelector('[data-testid="PropertyRoomsList"]');
        if (el) el.scrollIntoView({ block: 'center' });
    }, ROOM_NAME_SEL);
    await sleep(350);
    await waitForRoomLinks(page, 12000);
}

async function listRoomNames(page) {
    return page.evaluate((sel) => {
        const els = [...document.querySelectorAll(sel)];
        const seen = {};
        const out = [];
        els.forEach((el, index) => {
            const name = (el.innerText || '').replace(/\s+/g, ' ').trim();
            let hash = '';
            const href = String(el.getAttribute('href') || '');
            if (href.startsWith('#')) {
                hash = href;
            } else if (href.includes('#')) {
                hash = '#' + href.split('#').pop();
            }
            if (!hash) {
                const row = el.closest('[id^="RD"], tr[id^="RD"], [data-block-id^="RD"]');
                if (row?.id?.startsWith('RD')) {
                    hash = '#' + row.id;
                }
            }
            if (!hash) {
                const wrap = el.closest('[data-block-id]');
                if (wrap?.getAttribute('data-block-id')?.startsWith('RD')) {
                    hash = '#' + wrap.getAttribute('data-block-id');
                }
            }
            let room_id = String(el.getAttribute('data-room-id') || '').trim();
            const idAttr = String(el.getAttribute('id') || '');
            const idm = idAttr.match(/room_type_id_(\d+)/);
            if (!room_id && idm) {
                room_id = idm[1];
            }
            if (!room_id && hash) {
                const hm = hash.match(/#?RD(\d+)/i);
                if (hm) room_id = hm[1];
            }
            if (!hash && room_id) {
                hash = '#RD' + room_id;
            }
            const key = room_id || hash || name;
            if (!name || seen[key]) return;
            seen[key] = true;
            out.push({ index, name, hash, room_id });
        });
        return out;
    }, ROOM_NAME_SEL);
}

function mergePhotoLists(...lists) {
    const flat = [];
    for (const list of lists) {
        for (const img of list || []) {
            if (typeof img === 'string') {
                flat.push({ url: img, alt: '' });
            } else if (img?.url) {
                flat.push({ url: img.url, alt: img.alt || '' });
            }
        }
    }
    return normalizePhotoList(flat);
}

async function harvestHotelPhotos(page) {
    const raw = await page.evaluate(() => {
        const out = [];
        const push = (rawUrl, alt) => {
            const url = String(rawUrl || '').replace(/&amp;/g, '&');
            if (!url || !/\/xdata\/images\/hotel\//.test(url)) return;
            out.push({ url, alt: alt || '' });
        };
        document.querySelectorAll('img').forEach((img) => push(img.currentSrc || img.src, img.alt || ''));
        document.querySelectorAll('[style*="xdata/images/hotel"]').forEach((el) => {
            const m = (el.getAttribute('style') || '').match(/url\(["']?([^"')]+)/);
            if (m) push(m[1]);
        });
        return out;
    });
    return normalizePhotoList(raw);
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
    const opened = await openPhotosGallery(page);
    await sleep(opened ? 600 : 200);

    let last = 0;
    let stable = 0;
    for (let i = 0; i < 60; i++) {
        const info = await page.evaluate(() => {
            const grid =
                document.querySelector('[data-testid="gallery-modal-grid"]') ||
                document.querySelector('[data-testid="GalleryGridViewModal-wrapper"]');
            const buttons = [...document.querySelectorAll('[data-testid^="gallery-grid-photo-action-"]')];
            const lastBtn = buttons[buttons.length - 1];
            if (lastBtn) {
                lastBtn.scrollIntoView({ block: 'end' });
            } else if (grid) {
                grid.scrollTop = grid.scrollHeight;
                const scroller = grid.closest('[class]') || grid.parentElement;
                if (scroller) scroller.scrollTop = scroller.scrollHeight;
            }
            const label = buttons[0]?.getAttribute('aria-label') || buttons[buttons.length - 1]?.getAttribute('aria-label') || '';
            const totalMatch = label.match(/\/\s*(\d+)/);
            return { count: buttons.length, total: totalMatch ? Number(totalMatch[1]) : 0 };
        });
        if (info.total && info.count >= info.total) {
            break;
        }
        if (info.count === last) {
            stable++;
            if (stable >= 5) break;
        } else {
            stable = 0;
        }
        last = info.count;
        await sleep(250);
    }

    const photos = normalizePhotoList(await page.evaluate(() => {
        const out = [];
        const push = (raw, alt) => {
            const url = String(raw || '').replace(/&amp;/g, '&');
            if (!url || !/\/xdata\/images\/hotel\//.test(url)) return;
            out.push({ url, alt: alt || '' });
        };
        document.querySelectorAll('[data-testid^="gallery-grid-photo-action-"]').forEach((btn) => {
            const img = btn.querySelector('img');
            const alt = img?.getAttribute('alt') || btn.getAttribute('aria-label') || '';
            if (img?.currentSrc) push(img.currentSrc, alt);
            else if (img?.src) push(img.src, alt);
        });
        document.querySelectorAll('[data-testid="gallery-modal-grid"] picture img, [data-testid="lazy-image-image"] img').forEach((img) => {
            push(img.currentSrc || img.src, img.alt || '');
        });
        document.querySelectorAll('[data-testid="gallery-modal-grid"] [style*="background-image"]').forEach((el) => {
            const m = (el.getAttribute('style') || '').match(/url\(["']?([^"')]+)/);
            if (m) push(m[1], '');
        });
        return out;
    }));
    return photos;
}

async function openPhotosGallery(page) {
    const already = await page.$('[data-testid="gallery-modal-grid"]');
    if (already) {
        return true;
    }
    try {
        const wrap = await page.$('#photo_wrapper');
        if (wrap) {
            await wrap.evaluate((el) => el.scrollIntoView({ block: 'center' }));
            await sleep(200);
            await wrap.click();
        }
    } catch {
        try {
            await page.evaluate(() => {
                const wrap = document.querySelector('#photo_wrapper');
                if (wrap) wrap.click();
            });
        } catch {
            // ignore
        }
    }
    await sleep(500);
    if (await page.$('[data-testid="gallery-modal-grid"]')) {
        return true;
    }
    try {
        const u = new URL(page.url());
        if (u.searchParams.get('activeTab') !== 'photosGallery') {
            u.searchParams.set('activeTab', 'photosGallery');
            await page.goto(u.toString(), { waitUntil: 'domcontentloaded', timeout: 45000 });
            await dismissConsent(page);
            await sleep(500);
        }
    } catch {
        // ignore
    }
    for (let attempt = 0; attempt < 3; attempt++) {
        if (await page.$('[data-testid="gallery-modal-grid"]')) {
            return true;
        }
        try {
            const gridBtn = await page.$('[data-testid="return-to-grid-button"]');
            if (gridBtn) await gridBtn.click();
        } catch {
            // ignore
        }
        await clickByText(page, 'thư viện ảnh|xem tất cả ảnh|all photos');
        await sleep(350);
    }
    try {
        await page.waitForSelector('[data-testid="gallery-modal-grid"]', { timeout: 10000 });
        return true;
    } catch {
        return false;
    }
}

async function collectFacilitiesHtml(page) {
    await page.evaluate(() => {
        const el =
            document.querySelector('#hp_facilities_box') ||
            document.querySelector('[data-testid="property-facilities-block-container"]') ||
            document.querySelector('[role="dialog"]');
        if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    });
    await sleep(350);
    try {
        await page.waitForSelector(
            '[data-testid="facility-group-container"], [data-testid="property-facilities-block-container"], #hp_facilities_box, [role="dialog"] [data-testid="facility-group-container"]',
            { timeout: 8000 },
        );
    } catch {
        // ignore
    }
    return page.evaluate(() => {
        const groups = [];
        const seen = new Set();
        const pushGroup = (n) => {
            if (!n || seen.has(n)) return;
            seen.add(n);
            groups.push(n.outerHTML);
        };
        const roots = [
            document.querySelector('[role="dialog"]'),
            document.querySelector('[data-testid="property-facilities-block-container"]'),
            document.querySelector('#hp_facilities_box'),
            document.querySelector('[data-testid="PropertySectionsBelowRoomsTable-wrapper"]'),
        ].filter(Boolean);
        for (const root of roots) {
            root.querySelectorAll('[data-testid="facility-group-container"]').forEach(pushGroup);
        }
        document.querySelectorAll('[data-testid="facility-group-container"]').forEach(pushGroup);
        if (groups.length) {
            return groups.join('\n');
        }
        for (const root of roots) {
            if (root && (root.innerHTML || '').length > 200) {
                return root.outerHTML;
            }
        }
        return '';
    });
}

async function collectPoliciesHtml(page) {
    await page.evaluate(() => {
        document.querySelector('#policies, section#policies')?.scrollIntoView({ block: 'center' });
    });
    await sleep(400);
    await clickByText(
        page,
        'quy tắc chung|quy định chỗ nghỉ|house rules|nội quy|chính sách chỗ nghỉ|thông tin quan trọng|important information',
    );
    await sleep(500);
    try {
        await page.waitForSelector(
            '#policies, #hotelPoliciesInc, [data-testid="house-rules"], [data-testid="PropertyImportantInfo-wrapper"]',
            { timeout: 3500 },
        );
    } catch {
        // ignore
    }
    return page.evaluate(() => {
        /** Text with separators between block nodes (Booking often glues adjacent divs). */
        const blockText = (el) => {
            if (!el) return '';
            const clone = el.cloneNode(true);
            clone.querySelectorAll('svg, script, style, noscript').forEach((n) => n.remove());
            clone.querySelectorAll('br').forEach((br) => br.replaceWith('\n'));
            clone.querySelectorAll('p, div, li, h3, h4, tr, section, article').forEach((n) => {
                if (n.parentNode) {
                    n.parentNode.insertBefore(document.createTextNode('\n'), n);
                }
            });
            return (clone.innerText || '')
                .replace(/\u00a0/g, ' ')
                .replace(/[ \t]+\n/g, '\n')
                .replace(/\n[ \t]+/g, '\n')
                .replace(/\n{2,}/g, '\n')
                .replace(/[ \t]{2,}/g, ' ')
                .trim();
        };

        const flattenPolicyBody = (raw) => {
            let body = String(raw || '')
                .replace(/\n+/g, '. ')
                .replace(/\s+/g, ' ')
                .replace(/\.\s*\./g, '.')
                .trim();
            // "trở lênCó giường" / "đêm.Số lượng" style joins
            body = body.replace(/([\p{Ll}\p{N}])(\p{Lu})/gu, '$1 $2');
            body = body.replace(/([.!?…])([^\s\d])/gu, '$1 $2');
            return body.replace(/\s+/g, ' ').trim();
        };

        const titleFromCol = (col) => {
            const lines = blockText(col)
                .split(/\n+/)
                .map((s) => s.trim())
                .filter(Boolean);
            if (!lines.length) return '';
            const known = lines.find((l) =>
                /^(Nhận phòng|Trả phòng|Hủy|Huỷ|Trẻ em|Không giới hạn|Vật nuôi|Thú cưng|Các phương thức|Thanh toán|Hút thuốc|Giấy tờ)/i.test(
                    l,
                ),
            );
            if (known) return known.replace(/\s+/g, ' ').trim();
            // Icon column is usually a short label (1–2 lines)
            if (lines.length <= 2 && lines[0].length <= 100) {
                return lines.join(' ').replace(/\s+/g, ' ').trim();
            }
            return lines[0].replace(/\s+/g, ' ').trim();
        };

        /** Two-column house-rule row: [icon+title] [body] — not the outer list wrapper. */
        const isPolicyRow = (el) => {
            if (!el || el.tagName !== 'DIV') return false;
            const cols = [...el.children].filter((c) => c.tagName === 'DIV' && blockText(c) !== '');
            if (cols.length !== 2) return false;
            const title = titleFromCol(cols[0]);
            if (title.length < 2 || title.length > 120) return false;
            if (blockText(cols[1]).length < 2) return false;
            const hasSvg = !!cols[0].querySelector('svg');
            const titleLines = blockText(cols[0]).split(/\n/).filter(Boolean);
            return hasSvg || titleLines.length <= 3;
        };

        const collectPolicyRows = (container) => {
            const blocks = [];
            if (!container) return blocks;
            for (const child of [...container.children]) {
                if (!child || child.tagName === 'HR') continue;
                if (child.tagName !== 'DIV') continue;
                if (isPolicyRow(child)) {
                    const cols = [...child.children].filter(
                        (c) => c.tagName === 'DIV' && blockText(c) !== '',
                    );
                    const title = titleFromCol(cols[0]);
                    const body = flattenPolicyBody(blockText(cols[1]));
                    if (title.length >= 2 && body.length >= 2) {
                        blocks.push({ title, body });
                    }
                } else {
                    blocks.push(...collectPolicyRows(child));
                }
            }
            return blocks;
        };

        const normalizePolicyBlocks = (root) => {
            const content =
                root.querySelector('[data-testid="property-section--content"]') ||
                root.querySelector('[data-testid="PropertyImportantInfo-wrapper"]') ||
                root;
            const blocks = collectPolicyRows(content);
            if (!blocks.length) {
                return '';
            }
            const esc = (s) =>
                String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            return (
                '<div id="vt-policies">' +
                blocks
                    .map(
                        (b) =>
                            `<div data-vt-policy><h3>${esc(b.title)}</h3><div>${esc(b.body)}</div></div>`,
                    )
                    .join('') +
                '</div>'
            );
        };

        const section = document.querySelector('#policies, section#policies');
        if (section) {
            const normalized = normalizePolicyBlocks(section);
            if (normalized) {
                return normalized;
            }
            return section.outerHTML;
        }

        const nodes = [
            document.querySelector('#hotelPoliciesInc'),
            document.querySelector('[data-testid="house-rules"]'),
            document.querySelector('[data-testid="house-rules-section"]'),
            document.querySelector('[data-testid="PropertyImportantInfo-wrapper"]'),
            document.querySelector('[class*="hp-policies"]'),
        ].filter(Boolean);
        for (const n of nodes) {
            const normalized = normalizePolicyBlocks(n);
            if (normalized) {
                return normalized;
            }
        }
        return nodes.map((n) => n.outerHTML).join('\n');
    });
}

async function collectRoomModals(page, debug = {}, startIndex = 0, limit = 16) {
    const urlBefore = page.url();
    const count = await page
        .$$eval(
            ROOM_NAME_SEL,
            (els) => els.length,
        )
        .catch(() => 0);
    debug.rooms_found = count;
    const max = Math.min(count, startIndex + Math.max(1, limit));
    const rooms = [];
    for (let i = startIndex; i < max; i++) {
        debug.rooms_clicked = i + 1;
        const name = await page.evaluate((idx, sel) => {
            const els = [...document.querySelectorAll(sel)];
            const el = els[idx];
            if (!el) return '';
            el.scrollIntoView({ block: 'center' });
            return (el.innerText || '').replace(/\s+/g, ' ').trim();
        }, i, ROOM_NAME_SEL);
        debug.room_name = name;
        const clicked = await clickRoomAt(page, i);
        debug.room_clicked = clicked;
        const opened = await waitForRoomDetail(page, 14000);
        debug.room_modal = opened;
        if (!opened) {
            debug.room_url_after = page.url();
            await closeDialog(page);
            if (page.url() !== urlBefore) {
                try {
                    await page.goto(urlBefore, { waitUntil: 'domcontentloaded', timeout: 20000 });
                    await dismissConsent(page);
                    await scrollRoomsIntoView(page);
                } catch {
                    // ignore
                }
            }
            continue;
        }
        await sleep(650);
        await expandRoomModal(page);
        try {
            await page.waitForSelector(
                '[data-testid="roomPagePhotos"] img, [data-testid="roomPagePhotos"] [style*="background-image"], [data-testid="rp-facilities"] li',
                { timeout: 8000 },
            );
        } catch {
            // modal có thể thiếu ảnh
        }
        const data = await scrapeRoomModal(page, name);
        if (data?.photos) {
            data.photos = normalizePhotoList(data.photos);
        }
        debug.room_photos = data?.photos?.length || 0;
        debug.room_amenities = data?.amenities?.length || 0;
        if (data && data.name) {
            rooms.push(data);
            debug.rooms_ok = (debug.rooms_ok || 0) + 1;
        } else {
            debug.scrape_empty = true;
        }
        await closeDialog(page);
        if (page.url() !== urlBefore) {
            try {
                await page.goto(urlBefore, { waitUntil: 'domcontentloaded', timeout: 20000 });
                await dismissConsent(page);
                await scrollRoomsIntoView(page);
            } catch {
                // ignore
            }
        }
        await sleep(300);
    }
    return rooms;
}

async function clickRoomAt(page, index) {
    const selector = ROOM_NAME_SEL;
    try {
        await page.waitForSelector(selector, { timeout: 8000 });
        const els = await page.$$(selector);
        const el = els[index];
        if (!el) return false;
        await el.evaluate((node) => node.scrollIntoView({ block: 'center' }));
        await sleep(250);
        try {
            await el.click({ delay: 40 });
            return true;
        } catch {
            await page.evaluate((idx, sel) => {
                const nodes = [...document.querySelectorAll(sel)];
                const node = nodes[idx];
                if (!node) return;
                node.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window }));
            }, index, selector);
            return true;
        }
    } catch {
        return false;
    }
}

async function waitForRoomDetail(page, timeout = 12000) {
    const deadline = Date.now() + timeout;
    const sels = [
        '[data-testid="rp-content"]',
        '[data-testid="rp-room-title"]',
        '[data-testid="roomPagePhotos"]',
        '[role="dialog"][aria-label*="Thêm thông tin"]',
        '[role="dialog"][aria-label*="More information"]',
        '[role="dialog"] [data-testid="rp-facilities"]',
    ];
    while (Date.now() < deadline) {
        for (const sel of sels) {
            try {
                if (await page.$(sel)) return true;
            } catch {
                // ignore
            }
        }
        await sleep(250);
    }
    return false;
}

async function expandRoomModal(page) {
    try {
        await page.evaluate(() => {
            const dialog = [...document.querySelectorAll('[role="dialog"]')].find((d) =>
                d.querySelector('[data-testid="rp-content"]'),
            );
            const root = dialog || document.querySelector('[data-testid="rp-content"]');
            if (!root) return;
            const re = /xem thêm|show more|tất cả tiện|all amenities/i;
            for (const el of root.querySelectorAll('button, a, [role="button"]')) {
                const t = (el.textContent || '').replace(/\s+/g, ' ').trim();
                if (t.length < 80 && re.test(t)) el.click();
            }
            const photos = root.querySelector('[data-testid="roomPagePhotos"]');
            if (photos) {
                const hit = photos.querySelector('button, [role="button"], [style*="background-image"]');
                if (hit) hit.click();
            }
        });
    } catch {
        // ignore
    }
    await sleep(600);
}

async function scrapeRoomModal(page, fallbackName) {
    return page.evaluate((nameFallback) => {
            const dialog = [...document.querySelectorAll('[role="dialog"]')].find((d) =>
                d.querySelector('[data-testid="rp-content"], [data-testid="rp-facilities"], [data-testid="roomPagePhotos"], [data-testid="rp-room-title"]'),
            );
            const root =
                dialog?.querySelector('[data-testid="rp-content"]') ||
                document.querySelector('[data-testid="rp-content"]') ||
                dialog ||
                document.querySelector('[data-testid="roomPagePhotos"]')?.closest('[role="dialog"]') ||
                document.body;
            if (!root) return null;
            const textOf = (sel) => (root.querySelector(sel)?.innerText || '').replace(/\s+/g, ' ').trim();
            const name = textOf('[data-testid="rp-room-title"]') || nameFallback;
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
                url = String(url).replace(/&amp;/g, '&');
                const idm = url.match(/\/(\d+)\.jpe?g/i);
                const key = idm ? idm[1] : url.split('?')[0];
                if (seen[key]) return;
                seen[key] = true;
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
            root.querySelectorAll('[data-testid^="gallery-grid-photo-action-"], [data-testid^="gallery-photo-thumb-"]').forEach((btn) => {
                const img = btn.querySelector('img');
                if (img?.currentSrc) pushPhoto(img.currentSrc, name);
                else if (img?.src) pushPhoto(img.src, name);
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
        }, fallbackName);
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
