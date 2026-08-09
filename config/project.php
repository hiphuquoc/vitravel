<?php

/**
 * Cấu hình multi-project: thư mục seed + runtime tenancy.
 *
 * Seed profile không còn qua .env PROJECT_SEED.
 * Chọn profile bằng:
 *   php artisan project:seed {profile}
 *   ProjectSeed::useProfile() / ProjectContext (seed_profile|code)
 *
 * .env (tuỳ chọn):
 *   PROJECT_DEFAULT_CODE=vitravel     → fallback khi Host không khớp
 *   PROJECT_PUBLIC_QUERY_OVERRIDE=true → cho phép ?project= / cookie vt_project (mặc định = APP_DEBUG)
 *   PROJECT_REQUIRE_ADMIN_HEADER=false
 *
 * Host public: ResolveProjectFromHost (query → cookie → domain → default → first).
 * Admin API: X-Project-Code / X-Project-Id (ResolveAdminProject).
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Thư mục chứa file seed (relative to base_path)
    |--------------------------------------------------------------------------
    */
    'seed_dir' => 'project',

    /*
    |--------------------------------------------------------------------------
    | Runtime project code (optional public / admin soft fallback)
    |--------------------------------------------------------------------------
    |
    | Dùng khi host không khớp project_domains và không có query/cookie override.
    | null = bỏ qua, lấy project active đầu tiên.
    |
    */
    'default_code' => env('PROJECT_DEFAULT_CODE', null),

    /*
    |--------------------------------------------------------------------------
    | Public: cho phép ?project= / cookie chuyển dự án trên cùng Host
    |--------------------------------------------------------------------------
    |
    | Mặc định = APP_DEBUG. Production: false (trừ khi đặt
    | PROJECT_PUBLIC_QUERY_OVERRIDE=true cố ý trên staging).
    | Dùng FILTER_VALIDATE_BOOLEAN — tránh (bool)"false" === true.
    |
    */
    'allow_public_query_override' => filter_var(
        env('PROJECT_PUBLIC_QUERY_OVERRIDE', env('APP_DEBUG', false)),
        FILTER_VALIDATE_BOOLEAN
    ),

    'public_project_cookie' => 'vt_project',

    /*
    |--------------------------------------------------------------------------
    | Admin API: bắt buộc header dự án?
    |--------------------------------------------------------------------------
    |
    | false = soft: 1 project / default_code / membership duy nhất → auto-pick.
    | true  = luôn yêu cầu X-Project-Code / X-Project-Id khi không soft-resolve được.
    |
    */
    'require_admin_project_header' => env('PROJECT_REQUIRE_ADMIN_HEADER', false),

    'admin_header_code' => 'X-Project-Code',

    'admin_header_id' => 'X-Project-Id',

];
