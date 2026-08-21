/**
 * Chrome launch — cùng kiểu hoptackinhdoanh.dev (user-like, optional proxy).
 * Không hardcode proxy credentials.
 *
 * WSL: không gọi chrome.exe (lỗi vsock UtilBindVsockAnyPort). Dùng Chrome Linux + DISPLAY (WSLg).
 */
const fs = require('fs');
const path = require('path');

function isWsl() {
    if (process.env.WSL_DISTRO_NAME) return true;
    try {
        const v = fs.readFileSync('/proc/version', 'utf8');
        return /microsoft|wsl/i.test(v);
    } catch {
        return false;
    }
}

function getChromePath() {
    if (process.env.STAY_CRAWL_CHROME && fs.existsSync(process.env.STAY_CRAWL_CHROME)) {
        if (!/\.exe$/i.test(process.env.STAY_CRAWL_CHROME) || !isWsl()) {
            return process.env.STAY_CRAWL_CHROME;
        }
    }

    const puppeteerChrome = findPuppeteerChrome();
    if (puppeteerChrome) {
        return puppeteerChrome;
    }

    const systemPaths = [
        '/usr/bin/google-chrome-stable',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium',
    ];
    for (const chromePath of systemPaths) {
        if (fs.existsSync(chromePath)) {
            try {
                const real = fs.realpathSync(chromePath);
                if (real.includes('/snap/')) continue;
            } catch {
                // ignore
            }
            return chromePath;
        }
    }

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

function getBaseUserDataDir() {
    const fromEnv = process.env.STAY_CRAWL_USER_DATA_DIR;
    const dir = fromEnv && fromEnv.trim()
        ? fromEnv.trim()
        : path.resolve(__dirname, '../../storage/app/stay-crawl-chrome-profile');
    try {
        fs.mkdirSync(dir, { recursive: true });
        return dir;
    } catch {
        const fallback = path.join(process.env.HOME || '/tmp', '.cache', 'vitravel-stay-crawl-chrome');
        try {
            fs.mkdirSync(fallback, { recursive: true });
        } catch {
            // ignore
        }
        return fallback;
    }
}

/** Xóa SingletonLock — bước gallery/rooms sau basic dễ kẹt nếu Chrome trước chưa nhả profile. */
function clearProfileLocks(dir) {
    if (!dir) return;
    for (const name of ['SingletonLock', 'SingletonSocket', 'SingletonCookie', 'lockfile']) {
        try {
            fs.unlinkSync(path.join(dir, name));
        } catch {
            // ignore
        }
    }
}

function getUserDataDir({ ephemeral = false } = {}) {
    const base = getBaseUserDataDir();
    if (!ephemeral && process.env.STAY_CRAWL_EPHEMERAL_PROFILE !== '1') {
        clearProfileLocks(base);
        return base;
    }
    const unique = path.join(base, 'run-' + process.pid + '-' + Date.now());
    try {
        fs.mkdirSync(unique, { recursive: true });
    } catch {
        // ignore
    }
    return unique;
}

function getChromeArgs(proxyServer = null, { headed = false } = {}) {
    const args = [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--crash-dumps-dir=' + getCrashDumpsDir(),
        '--disable-infobars',
        '--no-default-browser-check',
        '--no-first-run',
        '--disable-blink-features=AutomationControlled',
        '--lang=vi-VN',
        '--window-size=1600,1000',
    ];
    // Headed + GPU: skeleton Booking hay kẹt nếu --disable-gpu (WSLg vẫn render được).
    if (!headed) {
        args.push('--disable-gpu');
    }
    if (headed) {
        args.push('--start-maximized');
        args.push('--window-position=40,40');
    }
    if (proxyServer) {
        args.push(`--proxy-server=${proxyServer}`);
    }
    return args;
}

function getLaunchOptions({
    proxyServer = null,
    timeout = 60000,
    headed = false,
    slowMo = 0,
    ephemeralProfile = false,
} = {}) {
    const chromePath = getChromePath();
    const env = { ...process.env };
    if (headed && isWsl() && !env.DISPLAY) {
        env.DISPLAY = ':0';
    }
    const launchOptions = {
        headless: headed ? false : true,
        args: getChromeArgs(proxyServer, { headed }),
        ignoreDefaultArgs: ['--enable-automation'],
        timeout,
        ignoreHTTPSErrors: true,
        defaultViewport: headed ? null : { width: 1600, height: 1000, deviceScaleFactor: 1 },
        slowMo: headed ? Math.max(25, Number(slowMo) || 50) : Number(slowMo) || 0,
        userDataDir: getUserDataDir({ ephemeral: ephemeralProfile }),
        env,
    };
    if (chromePath) {
        launchOptions.executablePath = chromePath;
    }
    return launchOptions;
}

module.exports = {
    getChromePath,
    getLaunchOptions,
    isWsl,
    clearProfileLocks,
    getBaseUserDataDir,
};
