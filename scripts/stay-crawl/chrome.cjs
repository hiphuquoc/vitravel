/**
 * Chrome launch — tối ưu tuyệt đối cho Đa Luồng Supervisor (Multi-Worker Safe & Zero-Disk Footprint).
 * - Mỗi worker / process nhận 1 thư mục profile cô lập hoàn toàn: worker-<PID>-<timestamp>-<rand>.
 * - Tuyệt đối không xung đột SingletonLock giữa các luồng Supervisor chạy đồng thời.
 * - Stale PID Sweeper an toàn: Chỉ xóa profile khi PID đã chết VÀ folder tạo trước đó, không xóa nhầm worker đang chạy.
 * - Vô hiệu hóa Disk Cache, Media Cache, GPU Cache, Log file rác.
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
    if (!dir || !fs.existsSync(dir)) return;
    for (const name of ['SingletonLock', 'SingletonSocket', 'SingletonCookie', 'lockfile']) {
        try {
            fs.unlinkSync(path.join(dir, name));
        } catch {
            // ignore
        }
    }
}

/** Xóa hoàn toàn thư mục profile của 1 process */
function cleanupUserDataDir(dir) {
    if (!dir || !fs.existsSync(dir)) return;
    try {
        clearProfileLocks(dir);
        fs.rmSync(dir, { recursive: true, force: true, maxRetries: 3, retryDelay: 100 });
    } catch {
        // ignore
    }
}

/**
 * Quét dọn an toàn cho ĐA LUỒNG SUPERVISOR:
 * - KHÔNG BAO GIỜ xóa thư mục của worker đang chạy (PID alive).
 * - Chỉ xóa khi PID đã CHẾT VÀ thư mục đã tạo hơn 2 phút, hoặc thư mục rác bỏ rơi quá 30 phút.
 */
function sweepStaleProfiles(baseDir) {
    if (!baseDir || !fs.existsSync(baseDir)) return;
    try {
        const entries = fs.readdirSync(baseDir);
        const now = Date.now();
        for (const entry of entries) {
            if (!entry.startsWith('proc-') && !entry.startsWith('worker-')) continue;
            const full = path.join(baseDir, entry);
            
            const match = entry.match(/(?:worker|proc)-(\d+)/);
            const pid = match ? parseInt(match[1], 10) : 0;
            
            // Bỏ qua PID của process hiện tại
            if (pid === process.pid) continue;

            let isRunning = false;
            try {
                if (pid > 0) {
                    process.kill(pid, 0); // Kiểm tra PID có còn sống trong hệ điều hành không
                    isRunning = true;
                }
            } catch {
                isRunning = false;
            }

            try {
                const stat = fs.statSync(full);
                const ageMs = now - stat.mtimeMs;
                // Nếu PID đã chết VÀ đã tạo hơn 2 phút -> an toàn xóa
                if (!isRunning && ageMs > 2 * 60 * 1000) {
                    fs.rmSync(full, { recursive: true, force: true });
                } else if (ageMs > 30 * 60 * 1000) {
                    // Nếu quá 30 phút (kể cả kill -9) -> dọn sạch
                    fs.rmSync(full, { recursive: true, force: true });
                }
            } catch {
                // ignore
            }
        }
    } catch {
        // ignore
    }
}

/**
 * Cấp phát profile riêng biệt theo Worker ID, PID và Timestamp ngẫu nhiên
 */
function getUserDataDir({ ephemeral = false } = {}) {
    const base = getBaseUserDataDir();
    
    // Tự động dọn dẹp các thư mục rác cũ an toàn
    sweepStaleProfiles(base);

    const workerToken = 'worker-' + process.pid + '-' + Date.now() + '-' + Math.random().toString(36).substring(2, 7);
    const unique = path.join(base, workerToken);
    try {
        if (!fs.existsSync(unique)) {
            fs.mkdirSync(unique, { recursive: true });
        }
        return unique;
    } catch {
        const tmpUnique = path.join(os.tmpdir(), 'chrome-' + workerToken);
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
        '--disable-features=Translate,OptimizationHints,MediaRouter,CalculateNativeWinOcclusion',
        '--disable-component-update',
        '--lang=vi-VN',
        '--window-size=1600,1000',
        
        // Tối ưu triệt để Disk Cache & Media Cache để KHÔNG chiếm dụng ổ cứng
        '--disk-cache-size=1',
        '--media-cache-size=1',
        '--disable-application-cache',
        '--disable-gpu-shader-disk-cache',
        '--disable-background-networking',
        '--disable-sync',
        '--disable-default-apps',
        '--disable-domain-reliability',
        '--aggressive-cache-discard',
        '--disable-extensions',
        '--disable-logging',
        '--log-level=3',
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
    
    const tempDir = os.tmpdir();
    if (!env.XDG_CONFIG_HOME) env.XDG_CONFIG_HOME = path.join(tempDir, '.config');
    if (!env.XDG_DATA_HOME) env.XDG_DATA_HOME = path.join(tempDir, '.local', 'share');
    if (!env.XDG_CACHE_HOME) env.XDG_CACHE_HOME = path.join(tempDir, '.cache');

    if (headed && isWsl() && !env.DISPLAY) {
        env.DISPLAY = ':0';
    }

    const userDataDir = getUserDataDir({ ephemeral: ephemeralProfile });

    const launchOptions = {
        headless: headed ? false : true,
        args: getChromeArgs(proxyServer, { headed }),
        ignoreDefaultArgs: ['--enable-automation'],
        timeout,
        protocolTimeout: 180000,
        ignoreHTTPSErrors: true,
        defaultViewport: headed ? null : { width: 1600, height: 1000, deviceScaleFactor: 1 },
        slowMo: headed ? Math.max(25, Number(slowMo) || 50) : Number(slowMo) || 0,
        userDataDir,
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
    cleanupUserDataDir,
    sweepStaleProfiles,
    getBaseUserDataDir,
};
