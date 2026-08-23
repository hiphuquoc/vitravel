/**
 * Chrome launch — tối ưu đa luồng (Multi-worker & Multi-process safe).
 * Tự động cấp phát profile riêng biệt theo Process PID / Token để không bao giờ bị đụng độ SingletonLock.
 * Tự động tạo thư mục tạm nếu /www/.local hoặc HOME bị chặn quyền.
 */
const fs = require('fs');
const path = require('path');
const os = require('os');

function isWsl() {
    if (process.env.WSL_DISTRO_NAME) return true;
    try {
        const v = fs.readFileSync('/proc/version', 'utf8');
        return /microsoft|wsl/i.test(v);
    } catch {
        return false;
    }
}

function ensureWritableDir(preferredPath, fallbackPath) {
    try {
        if (!fs.existsSync(preferredPath)) {
            fs.mkdirSync(preferredPath, { recursive: true });
        }
        // Test write permission
        const testFile = path.join(preferredPath, '.perm_test_' + process.pid);
        fs.writeFileSync(testFile, '1');
        fs.unlinkSync(testFile);
        return preferredPath;
    } catch {
        try {
            if (!fs.existsSync(fallbackPath)) {
                fs.mkdirSync(fallbackPath, { recursive: true });
            }
            return fallbackPath;
        } catch {
            return os.tmpdir();
        }
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
    const candidateHomes = [
        process.env.HOME,
        '/home/phupv',
        '/root',
        '/tmp',
    ].filter(Boolean);

    for (const h of candidateHomes) {
        const cacheDir = path.join(h, '.cache', 'puppeteer', 'chrome');
        if (!fs.existsSync(cacheDir)) continue;
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
    }
    return null;
}

function getCrashDumpsDir() {
    const preferred = path.join(process.env.HOME || '/tmp', '.chrome-crash-dumps');
    const fallback = path.join(os.tmpdir(), 'chrome-crash-dumps');
    return ensureWritableDir(preferred, fallback);
}

function getBaseUserDataDir() {
    const fromEnv = process.env.STAY_CRAWL_USER_DATA_DIR;
    const preferred = fromEnv && fromEnv.trim()
        ? fromEnv.trim()
        : path.resolve(__dirname, '../../storage/app/stay-crawl-chrome-profile');
    const fallback = path.join(os.tmpdir(), 'stay-crawl-chrome-profile');
    return ensureWritableDir(preferred, fallback);
}

/** Xóa SingletonLock trong thư mục profile */
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

/**
 * Mỗi process / worker được cấp 1 profile riêng biệt (theo PID và Worker token)
 * để hỗ trợ chạy đồng thời 2, 4, 8 luồng Supervisor mà không bao giờ bị xung đột SingletonLock.
 */
function getUserDataDir({ ephemeral = false } = {}) {
    const base = getBaseUserDataDir();
    // Luôn phân nhánh thư mục profile theo process PID để đa luồng chạy độc lập 100%
    const unique = path.join(base, 'proc-' + process.pid);
    try {
        if (!fs.existsSync(unique)) {
            fs.mkdirSync(unique, { recursive: true });
        }
        clearProfileLocks(unique);
        return unique;
    } catch {
        const tmpUnique = path.join(os.tmpdir(), 'chrome-prof-' + process.pid + '-' + Date.now());
        fs.mkdirSync(tmpUnique, { recursive: true });
        return tmpUnique;
    }
}

function getChromeArgs(proxyServer = null, { headed = false } = {}) {
    const args = [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--crash-dumps-dir=' + getCrashDumpsDir(),
        '--disable-crash-reporter',
        '--disable-breakpad',
        '--disable-infobars',
        '--no-default-browser-check',
        '--no-first-run',
        '--disable-blink-features=AutomationControlled',
        '--disable-features=Translate,OptimizationHints,MediaRouter',
        '--disable-component-update',
        '--lang=vi-VN',
        '--window-size=1600,1000',
    ];
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
    
    // Đảm bảo các biến môi trường cấu hình Linux/Chrome không bị lỗi permission
    const tempDir = os.tmpdir();
    if (!env.XDG_CONFIG_HOME) env.XDG_CONFIG_HOME = path.join(tempDir, '.config');
    if (!env.XDG_DATA_HOME) env.XDG_DATA_HOME = path.join(tempDir, '.local', 'share');
    if (!env.XDG_CACHE_HOME) env.XDG_CACHE_HOME = path.join(tempDir, '.cache');

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
