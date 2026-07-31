<?php
/**
 * Unify GCS config across sibling Laravel projects (ViTravel standard).
 * Does not echo credential material.
 */

$root = dirname(__DIR__, 2);
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

$gcsDiskBlock = <<<'PHP'
        /*
        | Google Cloud Storage — chuẩn đa dự án (ViTravel)
        | .env: GCS_PROJECT_ID, GCS_BUCKET, GCS_KEY_FILE, GCS_PUBLIC_URL
        | Key JSON: thường storage/app/gcs-credentials.json (relative base_path)
        | Fallback GOOGLE_CLOUD_* chỉ để migrate dự án cũ — đừng thêm mới.
        */
        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GCS_PROJECT_ID', env('GOOGLE_CLOUD_PROJECT_ID')),
            'key_file_path' => ($path = env('GCS_KEY_FILE', env('GOOGLE_CLOUD_KEY_FILE')))
                ? (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path) ? $path : base_path($path))
                : null,
            'bucket' => env('GCS_BUCKET', env('GOOGLE_CLOUD_STORAGE_BUCKET')),
            'path_prefix' => env('GCS_PATH_PREFIX', env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', '')),
            'storage_api_uri' => env('GCS_PUBLIC_URL', env('GCS_STORAGE_API_URI', env('GOOGLE_CLOUD_STORAGE_API_URI'))),
            'visibility' => 'public',
            'visibility_handler' => \League\Flysystem\GoogleCloudStorage\UniformBucketLevelAccessVisibility::class,
            'metadata' => ['cacheControl' => 'public,max-age=86400'],
            'throw' => true,
        ],

PHP;

$gcsServicesBlock = <<<'PHP'

    /*
    |--------------------------------------------------------------------------
    | Google Cloud Storage — chuẩn đa dự án
    |--------------------------------------------------------------------------
    | Hằng số .env (bắt buộc khi MEDIA_DISK=gcs):
    |   GCS_PROJECT_ID, GCS_BUCKET, GCS_KEY_FILE, GCS_PUBLIC_URL
    | Key file: đường dẫn relative base_path hoặc absolute.
    | Fallback GOOGLE_CLOUD_* chỉ migrate dự án cũ.
    */
    'gcs' => [
        'project_id' => env('GCS_PROJECT_ID', env('GOOGLE_CLOUD_PROJECT_ID')),
        'bucket' => env('GCS_BUCKET', env('GOOGLE_CLOUD_STORAGE_BUCKET')),
        'key_file' => ($path = env('GCS_KEY_FILE', env('GOOGLE_CLOUD_KEY_FILE')))
            ? (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path) ? $path : base_path($path))
            : null,
        'public_url' => env('GCS_PUBLIC_URL', env('GCS_PUBLIC_BASE_URL', env('GOOGLE_CLOUD_URL'))),
        'path_prefix' => env('GCS_PATH_PREFIX', env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', '')),
    ],

PHP;

$credSource = "$root/baseos.dev/storage/app/gcs-credentials.json";

foreach ($projects as $project) {
    $base = "$root/$project";
    if (! is_dir($base)) {
        echo "$project: SKIP (directory missing)\n";
        continue;
    }

    $credTarget = "$base/storage/app/gcs-credentials.json";
    if (! is_file($credTarget) && is_file($credSource)) {
        if (! is_dir(dirname($credTarget))) {
            mkdir(dirname($credTarget), 0755, true);
        }
        copy($credSource, $credTarget);
        @chmod($credTarget, 0600);
        echo "$project: credentials file created\n";
    } elseif (is_file($credTarget)) {
        echo "$project: credentials already present\n";
    } else {
        echo "$project: WARN no credentials file\n";
    }

    $fsPath = "$base/config/filesystems.php";
    if (is_file($fsPath)) {
        $fs = file_get_contents($fsPath);
        if (preg_match("/        'gcs' => \\[/s", $fs)) {
            $fs = preg_replace(
                "/        'gcs' => \\[.*?        \\],\\n/s",
                rtrim($gcsDiskBlock) . "\n",
                $fs,
                1,
                $count
            );
            if ($count !== 1) {
                echo "$project: WARN filesystems gcs replace count=$count\n";
            }
        } else {
            $fs = preg_replace(
                "/(        's3' => \\[.*?        \\],\\n)(\\n    \\],)/s",
                '$1' . "\n" . $gcsDiskBlock . '$2',
                $fs,
                1,
                $count
            );
            if ($count !== 1) {
                echo "$project: WARN filesystems gcs insert count=$count\n";
            }
        }
        file_put_contents($fsPath, $fs);
        echo "$project: filesystems.php updated\n";
    }

    $svcPath = "$base/config/services.php";
    if (is_file($svcPath)) {
        $svc = file_get_contents($svcPath);
        if (preg_match("/    'gcs' => \\[/s", $svc)) {
            $svc = preg_replace(
                "/    'gcs' => \\[.*?    \\],\\n/s",
                trim($gcsServicesBlock) . "\n",
                $svc,
                1,
                $count
            );
        } else {
            $svc = preg_replace(
                "/\\n\\];\\s*\\z/",
                trim($gcsServicesBlock) . "\n\n];",
                $svc,
                1,
                $count
            );
        }
        file_put_contents($svcPath, $svc);
        echo "$project: services.php updated\n";
    }

    $gitignore = "$base/.gitignore";
    if (is_file($gitignore)) {
        $gi = file_get_contents($gitignore);
        $line = '/storage/app/gcs-credentials.json';
        if (! str_contains($gi, 'gcs-credentials.json')) {
            if (str_contains($gi, '/storage') && ! str_contains($gi, '/storage/app/gcs-credentials.json')) {
                // whole /storage ignored — ok
                echo "$project: gitignore (storage root ignored)\n";
            } else {
                $gi = rtrim($gi) . "\n{$line}\n";
                file_put_contents($gitignore, $gi);
                echo "$project: gitignore updated\n";
            }
        }
    }
}

echo "Done.\n";
