<?php
/**
 * One-off: write storage/app/gcs-credentials.json from hardcoded key_file in filesystems.php.
 * Does not print key material.
 */
$projects = [
    'baseos.dev',
    'superdong.dev',
    'hitour.dev',
    'zenpot.dev',
    'hirachgia.dev',
    'liendoan.dev',
    'wallsora.dev',
    'hoptackinhdoanh.dev',
];

$root = '/var/www/html';

foreach ($projects as $project) {
    $base = "$root/$project";
    $target = "$base/storage/app/gcs-credentials.json";
    if (is_file($target)) {
        echo "$project: credentials already present\n";
        continue;
    }
    $fs = "$base/config/filesystems.php";
    if (! is_file($fs)) {
        echo "$project: skip (no filesystems.php)\n";
        continue;
    }
    $config = require $fs;
    $key = $config['disks']['gcs']['key_file'] ?? null;
    if (! is_array($key) || empty($key['private_key'])) {
        $fallback = "$root/vitravel.dev/storage/app/gcs-credentials.json";
        if (is_file($fallback)) {
            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }
            copy($fallback, $target);
            echo "$project: copied from vitravel.dev\n";
        } else {
            echo "$project: skip (no key_file array and no vitravel fallback)\n";
        }
        continue;
    }
    if (! is_dir(dirname($target))) {
        mkdir(dirname($target), 0755, true);
    }
    file_put_contents(
        $target,
        json_encode($key, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
        LOCK_EX
    );
    chmod($target, 0600);
    echo "$project: wrote credentials from config\n";
}
