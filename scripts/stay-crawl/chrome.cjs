/**
 * Chrome launch — cùng kiểu hoptackinhdoanh.dev (user-like, optional proxy).
 * Không hardcode proxy credentials.
 */
const fs = require('fs');
const path = require('path');

function getChromePath() {
    // Env override (VPS có Chrome custom path)
    if (process.env.STAY_CRAWL_CHROME && fs.existsSync(process.env.STAY_CRAWL_CHROME)) {
        return process.env.STAY_CRAWL_CHROME;
    }

    // Puppeteer cached Chrome (download khi npm install — ưu tiên vì không bị snap)
    const puppeteerChrome = findPuppeteerChrome();
    if (puppeteerChrome) {
        return puppeteerChrome;
    }

    // System Chrome (NON-snap) — snap chromium trên Ubuntu không hoạt động trong headless/WSL
    const systemPaths = [
        '/usr/bin/google-chrome-stable',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium',
    ];
    for (const chromePath of systemPaths) {
        if (fs.existsSync(chromePath)) {
            // Kiểm tra snap: nếu là symlink vào /snap → bỏ qua
            try {
                const real = fs.realpathSync(chromePath);
                if (real.includes('/snap/')) continue;
            } catch {
                // ignore
            }
            return chromePath;
        }
    }

    // Fallback: null → Puppeteer tự tìm bundled Chrome
    return null;
}

function findPuppeteerChrome() {
    const homeDir = process.env.HOME || '/root';
    const cacheDir = path.join(homeDir, '.cache', 'puppeteer', 'chrome');
    if (!fs.existsSync(cacheDir)) return null;

    try {
        const versions = fs.readdirSync(cacheDir).sort().reverse();
        for (const ver of versions) {
            const candidates = [
                path.join(cacheDir, ver, 'chrome-linux64', 'chrome'),
                path.join(cacheDir, ver, 'chrome-linux', 'chrome'),
            ];
            for (const p of candidates) {
                if (fs.existsSync(p)) return p;
            }
        }
    } catch {
        // ignore
    }
    return null;
}

function getCrashDumpsDir() {
    const homeDir = process.env.HOME || '/tmp';
    const crashDumpsDir = path.join(homeDir, '.chrome-crash-dumps');
    try {
        if (!fs.existsSync(crashDumpsDir)) {
            fs.mkdirSync(crashDumpsDir, { recursive: true });
        }
    } catch {
        // ignore
    }
    return crashDumpsDir;
}

function getChromeArgs(proxyServer = null) {
    const args = [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--crash-dumps-dir=' + getCrashDumpsDir(),
        '--disable-breakpad',
        '--disable-crash-reporter',
        '--disable-extensions',
        '--disable-background-networking',
        '--disable-background-timer-throttling',
        '--disable-backgrounding-occluded-windows',
        '--disable-component-extensions-with-background-pages',
        '--disable-features=TranslateUI,AutomationControlled',
        '--disable-ipc-flooding-protection',
        '--disable-renderer-backgrounding',
        '--disable-blink-features=AutomationControlled',
        '--force-color-profile=srgb',
        '--metrics-recording-only',
        '--mute-audio',
        '--no-first-run',
        '--password-store=basic',
        '--use-mock-keychain',
        '--window-size=1600,1000',
        '--lang=vi-VN',
    ];
    if (proxyServer) {
        args.push(`--proxy-server=${proxyServer}`);
    }
    return args;
}

function getLaunchOptions({ proxyServer = null, timeout = 60000 } = {}) {
    const launchOptions = {
        headless: true,
        args: getChromeArgs(proxyServer),
        timeout,
        ignoreHTTPSErrors: true,
        defaultViewport: { width: 1600, height: 1000 },
    };
    const chromePath = getChromePath();
    if (chromePath) {
        launchOptions.executablePath = chromePath;
    }
    return launchOptions;
}

module.exports = { getChromePath, getLaunchOptions };
