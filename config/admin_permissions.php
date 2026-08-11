<?php

declare(strict_types=1);

/**
 * Admin RBAC — permission catalog + role matrices (multi-project).
 *
 * - users.role = admin|super_admin → toàn hệ thống (mọi project, mọi quyền).
 * - project_user.role = owner|admin|editor|viewer → quyền trong 1 project.
 * - project_user.permissions (JSON) → grant thêm / deny bớt (optional).
 */

return [

    /*
    |--------------------------------------------------------------------------
    | System roles (users.role)
    |--------------------------------------------------------------------------
    */
    'system_roles' => [
        'super_admin' => 'Siêu quản trị',
        'admin' => 'Quản trị hệ thống',
        'staff' => 'Nhân sự (theo dự án)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Project roles (project_user.role)
    |--------------------------------------------------------------------------
    */
    'project_roles' => [
        'owner' => 'Chủ dự án',
        'admin' => 'Quản trị dự án',
        'editor' => 'Biên tập',
        'viewer' => 'Chỉ xem',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission keys (module.action)
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'dashboard.view' => 'Xem bảng điều khiển',

        'packages.view' => 'Xem gói tour/du thuyền',
        'packages.create' => 'Tạo gói',
        'packages.update' => 'Sửa gói',
        'packages.delete' => 'Xóa gói',

        'tour_categories.view' => 'Xem danh mục / chủ đề tour',
        'tour_categories.create' => 'Tạo danh mục tour',
        'tour_categories.update' => 'Sửa danh mục tour',
        'tour_categories.delete' => 'Xóa danh mục tour',

        'cruise_types.view' => 'Xem loại du thuyền',
        'cruise_types.create' => 'Tạo loại du thuyền',
        'cruise_types.update' => 'Sửa loại du thuyền',
        'cruise_types.delete' => 'Xóa loại du thuyền',

        'travel_styles.view' => 'Xem phong cách du lịch',
        'travel_styles.create' => 'Tạo phong cách',
        'travel_styles.update' => 'Sửa phong cách',
        'travel_styles.delete' => 'Xóa phong cách',

        'countries.view' => 'Xem điểm đến',
        'countries.create' => 'Tạo điểm đến',
        'countries.update' => 'Sửa điểm đến',
        'countries.delete' => 'Xóa điểm đến',

        'services.view' => 'Xem dịch vụ',
        'services.create' => 'Tạo dịch vụ',
        'services.update' => 'Sửa dịch vụ',
        'services.delete' => 'Xóa dịch vụ',

        'service_categories.view' => 'Xem danh mục dịch vụ',
        'service_categories.create' => 'Tạo danh mục DV',
        'service_categories.update' => 'Sửa danh mục DV',
        'service_categories.delete' => 'Xóa danh mục DV',

        'content.view' => 'Xem nội dung (slider/home/blog)',
        'content.create' => 'Tạo nội dung',
        'content.update' => 'Sửa nội dung',
        'content.delete' => 'Xóa nội dung',

        'brand.view' => 'Xem thương hiệu',
        'brand.create' => 'Tạo mục thương hiệu',
        'brand.update' => 'Sửa thương hiệu',
        'brand.delete' => 'Xóa thương hiệu',

        'leads.view' => 'Xem leads / bình luận',
        'leads.update' => 'Xử lý leads',
        'leads.delete' => 'Xóa leads',

        'media.view' => 'Xem thư viện media',
        'media.manage' => 'Upload / sửa / xóa media',

        'settings.view' => 'Xem cài đặt dự án',
        'settings.update' => 'Sửa cài đặt / hub / ngôn ngữ / cache',

        'users.view' => 'Xem danh sách người dùng',
        'users.manage' => 'Tạo / sửa / phân quyền người dùng (chỉ quản trị hệ thống)',

        'ai.use' => 'Dùng AI dịch / xây dựng nội dung',
        'ai.manage' => 'Quản lý prompt AI hệ thống + xem usage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role → permissions (* = mọi quyền trong catalog)
    |--------------------------------------------------------------------------
    */
    'role_permissions' => [
        'owner' => ['*'],
        'admin' => [
            'dashboard.view',
            'packages.*',
            'tour_categories.*',
            'cruise_types.*',
            'travel_styles.*',
            'countries.*',
            'services.*',
            'service_categories.*',
            'content.*',
            'brand.*',
            'leads.*',
            'media.*',
            'settings.*',
            // users.* chỉ siêu quản trị hệ thống — không cấp qua vai trò dự án
            'ai.use',
            'ai.manage',
        ],
        'editor' => [
            'dashboard.view',
            'packages.view', 'packages.create', 'packages.update',
            'tour_categories.view', 'tour_categories.create', 'tour_categories.update',
            'cruise_types.view', 'cruise_types.create', 'cruise_types.update',
            'travel_styles.view', 'travel_styles.create', 'travel_styles.update',
            'countries.view', 'countries.create', 'countries.update',
            'services.view', 'services.create', 'services.update',
            'service_categories.view', 'service_categories.create', 'service_categories.update',
            'content.view', 'content.create', 'content.update',
            'brand.view', 'brand.create', 'brand.update',
            'leads.view', 'leads.update',
            'media.view', 'media.manage',
            'settings.view',
            'ai.use',
        ],
        'viewer' => [
            'dashboard.view',
            'packages.view',
            'tour_categories.view',
            'cruise_types.view',
            'travel_styles.view',
            'countries.view',
            'services.view',
            'service_categories.view',
            'content.view',
            'brand.view',
            'leads.view',
            'media.view',
            'settings.view',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API path prefix → permission module
    |--------------------------------------------------------------------------
    | Method map: GET→view, POST→create, PUT/PATCH→update, DELETE→delete
    | Special overrides below take precedence.
    */
    'route_modules' => [
        'packages' => 'packages',
        'tour-categories' => 'tour_categories',
        'cruise-types' => 'cruise_types',
        'travel-styles' => 'travel_styles',
        'countries' => 'countries',
        'services' => 'services',
        'service-categories' => 'service_categories',
        'home-slides' => 'content',
        'home-sections' => 'content',
        'blog-categories' => 'content',
        'articles' => 'content',
        'team-members' => 'brand',
        'offices' => 'brand',
        'company-profile' => 'brand',
        'company-values' => 'brand',
        'reasons' => 'brand',
        'reference-people' => 'brand',
        'reviews' => 'brand',
        'review-platforms' => 'brand',
        'experience-albums' => 'brand',
        'experience-videos' => 'brand',
        'albums' => 'brand',
        'videos' => 'brand',
        'leads' => 'leads',
        'comments' => 'leads',
        'media' => 'media',
        'listing-hubs' => 'settings',
        'languages' => 'settings',
        'cache' => 'settings',
        'ai' => 'ai',
        'meta' => 'dashboard',
        'users' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Explicit route permission overrides (path contains → permission)
    |--------------------------------------------------------------------------
    */
    'route_overrides' => [
        // media library read vs write
        'GET media/library' => 'media.view',
        'GET media/meta' => 'media.view',
        'GET media/video-meta' => 'media.view',
        'POST media/upload' => 'media.manage',
        'POST media/upload-video' => 'media.manage',
        'PUT media/library' => 'media.manage',
        'DELETE media/library' => 'media.manage',

        'GET ai/prompts' => 'ai.manage',
        'PUT ai/prompts' => 'ai.manage',
        'POST ai/prompts/sync' => 'ai.manage',
        'GET ai/usage' => 'ai.manage',
        'GET ai/status' => 'ai.use',
        'POST ai/translate-page' => 'ai.use',
        'POST ai/enrich-detail-program' => 'ai.use',
        'GET ai/' => 'ai.use',
        'POST ai/' => 'ai.use',

        'GET cache' => 'settings.view',
        'POST cache' => 'settings.update',
        'DELETE cache' => 'settings.update',

        'GET users' => 'users.view',
        'POST users' => 'users.manage',
        'PUT users' => 'users.manage',
        'DELETE users' => 'users.manage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin nav item → required permission (frontend filter)
    |--------------------------------------------------------------------------
    */
    'nav_permissions' => [
        '/' => 'dashboard.view',
        '/tours/packages' => 'packages.view',
        '/tours/destinations' => 'countries.view',
        '/tours/categories' => 'tour_categories.view',
        '/tours/themes' => 'travel_styles.view',
        '/cruises/packages' => 'packages.view',
        '/cruises/types' => 'cruise_types.view',
        '/services/products' => 'services.view',
        '/services/categories' => 'service_categories.view',
        '/settings/hubs' => 'settings.view',
        '/content/slides' => 'content.view',
        '/content/home' => 'content.view',
        '/content/articles' => 'content.view',
        '/content/blog-categories' => 'content.view',
        '/brand/' => 'brand.view',
        '/leads/' => 'leads.view',
        '/settings/site' => 'settings.view',
        '/settings/languages' => 'settings.view',
        '/settings/ai-prompts' => 'ai.manage',
        '/settings/media' => 'media.view',
        '/settings/users' => 'users.view',
        '/account' => null, // always
    ],
];
