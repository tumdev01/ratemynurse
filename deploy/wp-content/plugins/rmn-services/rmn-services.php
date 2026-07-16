<?php
/*
Plugin Name: RateMyNurse ( RMN ) Services
Description: Service packs
Version: 1.0
Author: TumDev
*/
if (!defined('ABSPATH')) exit;

/**
 * หน้าที่ผู้ใช้ login อยู่จะฝังโปรไฟล์ของ user นั้นลงใน HTML ตรงๆ ผ่าน RMN_AUTH (ดู rmn_enqueue_scripts)
 * ถ้าโดน CDN/proxy/hosting cache แบบไม่แยกตาม cookie จะทำให้คนถัดไปที่เปิดหน้าเดียวกันเห็นข้อมูล/สถานะ login ของคนก่อนหน้า
 * จึงต้องบังคับห้าม cache หน้าใดๆ ที่มี access_token cookie ติดมา
 */
add_action('send_headers', function () {
    if (!empty($_COOKIE['access_token'])) {
        nocache_headers();
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Vary: Cookie');
    }
});

/**
 * Core (โหลดทุกหน้า)
 */
require_once __DIR__ . '/includes/AccessGuard.php';

add_action('plugins_loaded', function() {
    require_once __DIR__ . '/services/nursing/NursingHandler.php';
    require_once __DIR__ . '/services/nursinghome/NursingHomeHandler.php';
    require_once __DIR__ . '/services/nursinghome/NursingHome.php';
    require_once __DIR__ . '/services/authentication/Authentication.php';
    require_once __DIR__ . '/services/nursing/register.php';
    require_once __DIR__ . '/services/nursing/MyCalendar.php';
    require_once __DIR__ . '/services/nursinghome/register.php';
    require_once __DIR__ . '/services/search/Search.php';
    require_once __DIR__ . '/services/nursing/Nursing.php';
    require_once __DIR__ . '/services/member/register.php';
    require_once __DIR__ . '/services/board/board-index.php';
    require_once __DIR__ . '/services/board/job-post.php';
    require_once __DIR__ . '/services/board/job-interview.php';
    require_once __DIR__ . '/services/board/my-joblist.php';
    require_once __DIR__ . '/services/board/job-detail.php';
    require_once __DIR__ . '/services/my-profile/my-profile.php';
    require_once __DIR__ . '/services/my-account/my-account.php';
    require_once __DIR__ . '/services/subscription/subscription.php';
    require_once __DIR__ . '/services/my-favorite/my-favorite.php';
    require_once __DIR__ . '/services/my-contacts/my-contacts.php';
    require_once __DIR__ . '/services/my-contacts/contact-info.php';
    require_once __DIR__ . '/services/my-overview/my-overview.php';
    require_once __DIR__ . '/services/api/Global.php';
    require_once __DIR__ . '/services/comparison/comparison.php';
    require_once __DIR__ . '/services/my-profile/nursing-home-profile.php'; // Edit Nursing Home Profile
    require_once __DIR__ . '/services/reviews/Reviews.php'; // Reviews-all page (Nursing + NursingHome)
});

function rmn_enqueue_scripts() {
    wp_enqueue_script('jquery');

    // WP โหลด jQuery แบบ noConflict — alias $ เพื่อให้ inline script ของ shortcode ที่ใช้ $ ทำงานได้
    wp_add_inline_script('jquery', 'window.$ = window.$ || jQuery;', 'after');

    // Axios — single source of truth (pinned version, registered globally)
    wp_enqueue_script(
        'axios',
        'https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js',
        [],
        '1.7.7',
        true
    );

    // SweetAlert2
    wp_enqueue_script(
        'sweetalert2',
        'https://cdn.jsdelivr.net/npm/sweetalert2@11',
        [],
        null,
        true
    );

    // SweetAlert2 v11 inject stylesheet ของตัวเองผ่าน JS ตอน runtime (ไม่ใช่ wp_style ที่แก้ inline
    // ผ่าน wp_add_inline_style ได้) — z-index เดิม (~1060) ต่ำกว่า modal/dropdown ในระบบ (เช่น
    // .authen-loading-overlay 100000, .ts-dropdown 999999) ทำให้ toast/alert โผล่ไปอยู่หลัง modal
    // มองไม่เห็น ต้อง override ด้วย !important ให้สูงกว่าทุกอย่างในระบบเสมอ
    wp_register_style('rmn-global-overrides', false);
    wp_enqueue_style('rmn-global-overrides');
    wp_add_inline_style('rmn-global-overrides', '
        .swal2-container { z-index: 999999999 !important; }
    ');

    // Tom Select (แทน select2 — ไม่ต้องพึ่ง jQuery, dropdown ผูกกับ element เดิมโดย design
    // ไม่มีปัญหาตำแหน่งลอยผิดที่แบบ select2 ที่เคยต้อง hack dropdownParent/position:relative)
    wp_enqueue_script(
        'tom-select',
        'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js',
        [],
        '2.3.1',
        true
    );

    wp_enqueue_style(
        'tom-select',
        'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css'
    );

    // Hardcode สไตล์หลักของ Tom Select เข้าไปตรงๆ (ไม่พึ่ง CDN css อย่างเดียว)
    // กัน CDN โดนบล็อก/โหลดช้า/ad-blocker แทรกแซง ทำให้ dropdown โผล่มาแบบไม่มีสไตล์เลย (โปร่งใส)
    wp_add_inline_style('tom-select', '
        .ts-wrapper { position: relative; box-sizing: border-box; border: none !important; padding: 0 !important; }
        .ts-control { box-sizing: border-box; cursor: pointer; display: flex; align-items: center;
            min-height: 43px; padding: 8px 12px; background-color: #fff; border: 1px solid #D9D8DC;
            border-radius: 8px; font-size: 14px; color: #444; }
        .ts-control input { font-size: 14px; }
        .ts-wrapper.single .ts-control { padding-right: 24px; }
        .ts-dropdown { background-color: #fff !important; border: 1px solid #D9D8DC !important;
            border-radius: 8px; box-sizing: border-box; z-index: 999999 !important; }
        .ts-dropdown .ts-dropdown-content { max-height: 200px; overflow-y: auto; }
        .ts-dropdown .option { padding: 6px 12px; font-size: 14px; color: #444; cursor: pointer; }
        .ts-dropdown .option.active { background-color: #5897fb; color: #fff; }
        .ts-dropdown .option[data-selected] { background-color: #ddd; }
        .ts-dropdown .no-results { padding: 6px 12px; font-size: 14px; color: #999; }
    ');

    // Flatpickr (registered, enqueue per-shortcode that needs it)
    wp_register_style(
        'flatpickr',
        plugins_url('flatpickr/flatpickr.min.css', __FILE__),
        [],
        '4.6.13'
    );
    wp_register_script(
        'flatpickr',
        plugins_url('flatpickr/flatpickr.min.js', __FILE__),
        [],
        '4.6.13',
        true
    );
    wp_register_script(
        'flatpickr-month-select',
        plugins_url('flatpickr/monthSelect/index.js', __FILE__),
        ['flatpickr'],
        '4.6.13',
        true
    );
    wp_register_script(
        'flatpickr-th',
        plugins_url('flatpickr/th.js', __FILE__),
        ['flatpickr'],
        '4.6.13',
        true
    );

    // เวอร์ชันผูกกับ filemtime ของไฟล์จริง (ไม่ hardcode) — เดิม hardcode '1.0.x' ทำให้
    // query string ?ver=... เหมือนเดิมทุกครั้งที่แก้โค้ด browser/cache เลยไม่โหลดไฟล์ใหม่ให้
    $rmn_config_path            = plugin_dir_path(__FILE__) . 'js/rmn-config.js';
    $rmn_utils_path             = plugin_dir_path(__FILE__) . 'js/rmn-utils.js';
    $rmn_location_selector_path = plugin_dir_path(__FILE__) . 'js/rmn-location-selector.js';
    $rmn_scripts_path           = plugin_dir_path(__FILE__) . 'rmn-scripts.js';

    wp_enqueue_script(
        'rmn-config',
        plugins_url('js/rmn-config.js', __FILE__),
        [],
        file_exists($rmn_config_path) ? filemtime($rmn_config_path) : '1.0.0',
        true
    );

    wp_enqueue_script(
        'rmn-utils',
        plugins_url('js/rmn-utils.js', __FILE__),
        ['rmn-config', 'jquery', 'axios'],
        file_exists($rmn_utils_path) ? filemtime($rmn_utils_path) : '1.0.0',
        true
    );

    wp_enqueue_script(
        'rmn-location-selector',
        plugins_url('js/rmn-location-selector.js', __FILE__),
        ['rmn-config', 'rmn-utils', 'jquery', 'tom-select', 'axios'],
        file_exists($rmn_location_selector_path) ? filemtime($rmn_location_selector_path) : '1.0.1',
        true
    );

    wp_enqueue_script(
        'rmn-scripts',
        plugins_url('rmn-scripts.js', __FILE__),
       ['rmn-config', 'rmn-utils', 'rmn-location-selector', 'jquery', 'axios'],
        file_exists($rmn_scripts_path) ? filemtime($rmn_scripts_path) : '1.0.1',
        true
    );

    // หน้าเว็บ (รวมทั้ง markup ตรงนี้) ถูก cache แบบข้าม visitor ได้ (เช่น WordPress.com Batcache
    // ที่เช็คแค่ cookie login มาตรฐานของ WP ไม่รู้จัก access_token/is_auth ที่ระบบนี้ตั้งเอง)
    // จึงห้ามฝังข้อมูลผู้ใช้จริงลงใน HTML ที่นี่ — ต้องให้ browser ไปดึงเองผ่าน get_current_user
    // (admin-ajax.php ไม่ถูก cache) เสมอ ดู rmn_get_current_user() ด้านล่าง
    wp_localize_script('rmn-scripts', 'RMN_AUTH', [
        'hasToken' => !empty($_COOKIE['access_token']),
        'ajaxUrl'  => admin_url('admin-ajax.php'),
    ]);

    wp_localize_script('rmn-location-selector', 'RMN_PROVINCES', rmn_get_provinces());
}
add_action('wp_enqueue_scripts', 'rmn_enqueue_scripts');

/**
 * Server-side fetch + cache จังหวัด (TTL 7 วัน) — เลี่ยง API call บน critical path
 * ใช้ผ่าน window.RMN_PROVINCES ใน JS, fallback API ถ้าไม่มี
 */
function rmn_get_provinces() {
    $cache_key = 'rmn_provinces_v1';
    $cached    = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $response = wp_remote_get('https://services.ratemynurse.org/api/provinces_list', [
        'timeout' => 10,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return [];
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data)) {
        return [];
    }

    set_transient($cache_key, $data, 7 * DAY_IN_SECONDS);
    return $data;
}

/**
 * Defer non-critical CSS (flatpickr) ด้วย media="print" onload trick
 * เพื่อไม่ block render — CSS ยังโหลด แค่ไม่ block initial paint
 * รองรับ noscript fallback (ถ้า JS disabled จะโหลดปกติ)
 */
add_filter('style_loader_tag', function ($html, $handle) {
    // tom-select (เดิม select2) ถูกถอดออกจากลิสต์นี้เจตนา: การ defer ด้วย media="print" onload="..."
    // ต้องพึ่ง inline onload handler ทำงานสำเร็จ — ถ้าโดน CSP บล็อก หรือ browser
    // ไม่ยิง onload ตามจังหวะที่คาด media จะค้างที่ "print" ถาวร ทำให้ dropdown เลือกจังหวัด/อำเภอ/ตำบล
    // (ซึ่งเป็น UI ที่ผู้ใช้ต้องโต้ตอบด้วยจริง ไม่ใช่ non-critical CSS) โผล่มาแบบไม่มีสไตล์เลย
    $deferred = ['flatpickr'];
    if (!in_array($handle, $deferred, true)) {
        return $html;
    }
    if (!preg_match("/href=['\"]([^'\"]+)['\"]/", $html, $matches)) {
        return $html;
    }
    $href = $matches[1];
    // เปลี่ยน media='all' (หรือค่าอื่น) เป็น media='print' + onload swap
    $deferred_html = preg_replace(
        "/media=['\"][^'\"]*['\"]/",
        'media="print" onload="this.media=\'all\'; this.onload=null;"',
        $html
    );
    // ถ้าไม่มี media= attr อยู่เลย ใส่ก่อน />
    if ($deferred_html === $html) {
        $deferred_html = preg_replace(
            "/(\/?>)$/",
            ' media="print" onload="this.media=\'all\'; this.onload=null;"$1',
            $html
        );
    }
    $noscript = '<noscript><link rel="stylesheet" href="' . esc_url($href) . '"></noscript>';
    return $deferred_html . $noscript;
}, 10, 2);

// Tailwind built CSS (static file, purged) — แทน CDN runtime compile
// Build ที่ local: npx tailwindcss -i tailwind.input.css -o tailwind.css --minify
function rmn_enqueue_tailwind() {
    $tailwind_path = plugin_dir_path(__FILE__) . 'tailwind.css';
    $version       = file_exists($tailwind_path) ? filemtime($tailwind_path) : '1.0.0';
    wp_enqueue_style(
        'tailwind-built',
        plugins_url('tailwind.css', __FILE__),
        [],
        $version
    );
}
add_action('wp_enqueue_scripts', 'rmn_enqueue_tailwind');

add_filter('query_vars', function($vars) {
    $vars[] = 'nursing_home_id';
    return $vars;
});

add_filter('query_vars', function($vars) {
    $vars[] = 'nursing_id';
    return $vars;
});

add_filter('query_vars', function($vars) {
    $vars[] = 'job_id';
    return $vars;
});

add_filter('query_vars', function($vars) {
    $vars[] = 'contact_id';
    return $vars;
});

add_action('init', function() {
    add_rewrite_rule('^nursing-home-info/([0-9]+)/?', 'index.php?pagename=nursing-home-info&nursing_home_id=$matches[1]', 'top');
    add_rewrite_rule('^nursing-info/([0-9]+)/?', 'index.php?pagename=nursing-info&nursing_id=$matches[1]', 'top');
    add_rewrite_rule('^job-info/([0-9]+)/?', 'index.php?pagename=job-info&job_id=$matches[1]', 'top');
    add_rewrite_rule('^job-interview/([0-9]+)/?', 'index.php?pagename=job-interview&job_id=$matches[1]', 'top');
    add_rewrite_rule('^contact-info/([0-9]+)/?', 'index.php?pagename=contact-info&contact_id=$matches[1]', 'top');
    add_rewrite_rule('^nursing-home-profile/([0-9]+)/?', 'index.php?pagename=nursing-home-profile&nursing_home_id=$matches[1]', 'top');
    // Reviews-all pages — Oxygen template ผูก slug "nursing-reviews" / "nursing-home-reviews"
    add_rewrite_rule('^nursing-reviews/([0-9]+)/?', 'index.php?pagename=nursing-reviews&nursing_id=$matches[1]', 'top');
    add_rewrite_rule('^nursing-home-reviews/([0-9]+)/?', 'index.php?pagename=nursing-home-reviews&nursing_home_id=$matches[1]', 'top');
});

add_action('wp_ajax_logout', 'rmn_logout');
add_action('wp_ajax_nopriv_logout', 'rmn_logout');

function rmn_logout()
{
    // ลบ access_token
    if (isset($_COOKIE['access_token'])) {
        // ลบ user transient cache
        $cache_key = 'rmn_user_' . substr(hash('sha256', $_COOKIE['access_token']), 0, 16);
        delete_transient($cache_key);

        setcookie(
            'access_token',
            '',
            [
                'expires'  => time() - 3600, // ย้อนเวลา
                'path'     => '/',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    // ลบ is_auth
    if (isset($_COOKIE['is_auth'])) {
        setcookie(
            'is_auth',
            '',
            [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => true,
                'httponly' => false,
                'samesite' => 'Strict'
            ]
        );
    }

    wp_send_json_success([
        'success' => true  // เปลี่ยนจาก = เป็น =>
    ]);
}

add_action('wp_ajax_get_current_user', 'rmn_get_current_user');
add_action('wp_ajax_nopriv_get_current_user', 'rmn_get_current_user');
function rmn_get_current_user()
{
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_success(null);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    // Cache key เดียวกับ AccessGuard::me() (includes/AccessGuard.php) — กัน /api/me ถูกยิงซ้ำ
    // ทั้งจาก server-side (ทุกหน้าผ่าน template_redirect) และ client-side (หลายจุดในหน้าเดียวกัน)
    $cache_key = 'rmn_me_' . substr(hash('sha256', $token), 0, 16);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
    }

    $response = wp_remote_get('https://services.ratemynurse.org/api/me', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ],
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    // ✅ ตรวจเจอ unauthenticated → ลบ cookie access_token
    if (
        in_array($status_code, [401, 403]) ||
        (isset($body['message']) && $body['message'] === 'Unauthenticated.')
    ) {
        // ลบ cookie
        setcookie('access_token', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['access_token']);

        wp_send_json_success(null); // ส่งกลับเหมือน user ยังไม่ได้ login
    }

    if ($status_code !== 200) {
        wp_send_json_error(['message' => 'Unexpected error', 'status' => $status_code], $status_code);
    }

    set_transient($cache_key, $body, 30);

    wp_send_json_success($body ?? null);
}

add_action('wp_ajax_set_user_token', 'rmn_set_user_token');
add_action('wp_ajax_nopriv_set_user_token', 'rmn_set_user_token');
function rmn_set_user_token() {
    if (!isset($_POST['access_token'])) {
        wp_send_json_error(['message' => 'No token provided']);
    }

    $token = sanitize_text_field($_POST['access_token']);

    setcookie(
        'access_token',
        $token,
        [
            'expires'  => time() + (7 * 24 * 60 * 60), // 7 วัน
            'path'     => '/',
            'secure'   => true,      // ใช้ HTTPS
            'httponly' => true,      // JS อ่านไม่ได้
            'samesite' => 'Strict'   // ป้องกัน CSRF
        ]
    );

    setcookie('is_auth', '1', [
        'expires'  => time() + (7 * 24 * 60 * 60),
        'path'     => '/',
        'secure'   => true,
        'httponly' => false, // JS อ่านได้
        'samesite' => 'Strict'
    ]);

    wp_send_json_success(['message' => 'Token stored securely']);
}

add_action('wp_ajax_verify_otp', 'rmn_verify_otp');
add_action('wp_ajax_nopriv_verify_otp', 'rmn_verify_otp');
function rmn_verify_otp() {
    // อ่าน raw body แล้ว decode JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['phone']) || empty($input['otp'])) {
        wp_send_json_error(['message' => 'Missing phone or OTP']);
    }

    $phone = sanitize_text_field($input['phone']);
    $otp   = sanitize_text_field($input['otp']);

    // เรียก API verify OTP
    $response = wp_remote_post('https://services.ratemynurse.org/api/otp/verify', [
        'body'    => json_encode([
            'phone' => $phone,
            'otp'   => $otp
        ]),
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['access_token'])) {
        wp_send_json_error(['message' => 'Invalid OTP']);
    }

    // Set HttpOnly cookie
    setcookie('access_token', $body['access_token'], [
        'expires'  => time() + (7 * 24 * 60 * 60), // 7 วัน
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    setcookie('is_auth', '1', [
        'expires'  => time() + (7 * 24 * 60 * 60),
        'path'     => '/',
        'secure'   => true,
        'httponly' => false, // JS อ่านได้
        'samesite' => 'Strict'
    ]);

    wp_send_json_success(['message' => 'Login success']);
}

add_action('wp_ajax_job_post', 'rmn_job_post');
add_action('wp_ajax_nopriv_job_post', 'rmn_job_post');
function rmn_job_post() {
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => '401 Unauthenticated'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['data'])) {
        wp_send_json_error(['message' => 'Invalid input'], 400);
    }

    try {
        $data = $input['data'];

        $payload = [
            'profile_id'    => sanitize_text_field($data['profile_id'] ?? ''),
            'name'          => sanitize_text_field($data['name'] ?? ''),
            'service_type'  => sanitize_text_field($data['service_type'] ?? ''),
            'hire_type'     => sanitize_text_field($data['hire_type'] ?? ''),
            'hire_rule'     => sanitize_text_field($data['hire_rule'] ?? ''),
            'care_type'     => sanitize_text_field($data['care_type'] ?? ''),
            'cost'          => sanitize_text_field($data['cost'] ?? ''),
            'start_date'    => sanitize_text_field($data['start_date'] ?? ''),
            'description'   => wp_kses_post($data['description'] ?? ''),
            'address'       => sanitize_text_field($data['address'] ?? ''),
            'province_id'   => sanitize_text_field($data['province_id'] ?? ''),
            'district_id'   => sanitize_text_field($data['district_id'] ?? ''),
            'sub_district_id' => sanitize_text_field($data['sub_district_id'] ?? ''),
            'phone'         => sanitize_text_field($data['phone'] ?? ''),
            'email'         => sanitize_email($data['email'] ?? ''),
            'facebook'      => sanitize_text_field($data['facebook'] ?? ''),
            'lineid'        => sanitize_text_field($data['lineid'] ?? '')
        ];

        $response = wp_remote_post('https://services.ratemynurse.org/api/job/create', [
            'body'    => json_encode($payload),
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$token}", // ✅ แก้ตรงนี้
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()], 500);
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        wp_send_json_success($decoded);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()], 500);
    }
}

add_action('wp_ajax_job_get_posts', 'rmn_job_get_posts');
add_action('wp_ajax_nopriv_job_get_posts', 'rmn_job_get_posts');
function rmn_job_get_posts() {
    // if (!isset($_COOKIE['access_token'])) {
    //     wp_send_json_error(['message' => '401 Unauthenticated'], 401);
    // }

    // $token = sanitize_text_field($_COOKIE['access_token']);
    $input = json_decode(file_get_contents('php://input'), true);
    $payload = [
        'user'          => sanitize_text_field($input['user'] ?? ''),
        'limits'  => sanitize_text_field($input['limits'] ?? ''),
        'service_type'     => sanitize_text_field($input['service_type'] ?? ''),
        'care_type'     => sanitize_text_field($input['care_type'] ?? ''),
        'hire_type'     => sanitize_text_field($input['hire_type'] ?? ''),
        'created_at'          => sanitize_text_field($input['created_at'] ?? ''),
        'page'    => sanitize_text_field($input['page'] ?? ''),
        'min_cost' => sanitize_text_field($input['min_cost'] ?? 0),
        'max_cost' => sanitize_text_field($input['max_cost'] ?? ''),
        'province_id' => sanitize_text_field($input['province_id'] ?? '')
    ];
    $response = wp_remote_post('https://services.ratemynurse.org/api/job/job-list', [
        'body'    => json_encode($payload),
        'headers' => [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            //'Authorization' => "Bearer {$token}",
            'X-internal-Token' => '9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
        ],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $body = wp_remote_retrieve_body($response);
    $decoded = json_decode($body, true);

    wp_send_json_success($decoded);
    
}

add_action('wp_ajax_job_get_interviews', 'rmn_job_get_interviews');
add_action('wp_ajax_nopriv_job_get_interviews', 'rmn_job_get_interviews');
function rmn_job_get_interviews() {
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => '401 Unauthenticated'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $input = json_decode(file_get_contents('php://input'), true);
    $payload = [
        'limits'       => sanitize_text_field($input['limits'] ?? ''),
        'service_type' => sanitize_text_field($input['service_type'] ?? ''),
        'care_type'    => sanitize_text_field($input['care_type'] ?? ''),
        'hire_rule'    => sanitize_text_field($input['hire_rule'] ?? ''),
        'created_at'   => sanitize_text_field($input['created_at'] ?? ''),
        'page'         => sanitize_text_field($input['page'] ?? ''),
        'min_cost'     => sanitize_text_field($input['min_cost'] ?? 0),
        'max_cost'     => sanitize_text_field($input['max_cost'] ?? ''),
        'province_id'  => sanitize_text_field($input['province_id'] ?? ''),
    ];

    $response = wp_remote_post('https://services.ratemynurse.org/api/job-nursing/interviews', [
        'body'    => json_encode($payload),
        'headers' => [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$token}",
        ],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $body = wp_remote_retrieve_body($response);
    $decoded = json_decode($body, true);

    wp_send_json($decoded);
}

add_action('wp_ajax_member_register', 'rmn_member_register');
add_action('wp_ajax_nopriv_member_register', 'rmn_member_register');
function rmn_member_register() {
    $payload = [
        'firstname' => sanitize_text_field($_POST['fname'] ?? ''),
        'lastname'  => sanitize_text_field($_POST['lname'] ?? ''),
        'phone'     => sanitize_text_field($_POST['phone'] ?? ''),
        'email'     => sanitize_text_field($_POST['email'] ?? ''),
        'cardid'    => sanitize_text_field($_POST['cardid'] ?? ''),
        'user_type' => 'MEMBER',
    ];

    $response = wp_remote_post('https://services.ratemynurse.org/api/member/create', [
        'body'    => json_encode($payload),
        'headers' => [
            'Content-Type'     => 'application/json',
            'Accept'           => 'application/json',
            'X-internal-Token' => '9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
        ],
    ]);

    if (is_wp_error($response)) {
        wp_send_json([
            'success' => false,
            'data'    => ['message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้']
        ]);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    // ถ้า Laravel ส่ง error เช่น Unauthorized
    if (!empty($body['error']) || wp_remote_retrieve_response_code($response) === 401) {
        wp_send_json([
            'success' => false,
            'data'    => ['message' => $body['error'] ?? 'Unauthorized'],
        ]);
    }

    // ถ้า validation errors
    if (!empty($body['errors']) || !empty($body['success'] == false)) {
        wp_send_json([
            'success' => false,
            'data' => [
                'message' => $body['message'] ?? 'Validation failed',
                'errors'  => $body['errors'],
            ]
        ]);
    }

    // สมัครสำเร็จแล้ว แต่ยังไม่ได้ access_token — backend ส่ง OTP ไปที่เบอร์แล้ว ต้องยืนยันผ่าน
    // /api/otp/verify (action verify_otp) ก่อนถึงจะได้ token จริงและ login เข้าระบบสมบูรณ์
    if (empty($body['data']['otp_required'])) {
        wp_send_json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด ไม่สามารถบันทึกข้อมูลได้!',
        ]);
    }

    wp_send_json([
        'success' => true,
        'message' => $body['message'] ?? 'สมัครสมาชิกสำเร็จ',
        'data'    => ['otp_required' => true, 'phone' => $payload['phone']],
    ]);
}

add_action('wp_ajax_provider_register', 'rmn_provider_register');
add_action('wp_ajax_nopriv_provider_register', 'rmn_provider_register');
function rmn_provider_register() {
    $payload = [
        'firstname' => sanitize_text_field($_POST['name'] ?? ''),
        'lastname' => sanitize_text_field($_POST['name'] ?? ''),
        'phone'  => sanitize_text_field($_POST['main_phone'] ?? ''),
        'email'     => sanitize_text_field($_POST['email'] ?? ''),
        'res_phone' => sanitize_text_field($_POST['res_phone'] ?? ''),
        'facebook'  => sanitize_text_field($_POST['facebook'] ?? ''),
        'website'   => sanitize_text_field($_POST['website'] ?? ''),
        'address' => sanitize_text_field($_POST['address'] ?? ''),
        'province_id' => sanitize_text_field($_POST['province_id'] ?? ''),
        'district_id'  => sanitize_text_field($_POST['district_id'] ?? ''),
        'sub_district_id' => sanitize_text_field($_POST['sub_district_id'] ?? ''),
        'zipcode' => sanitize_text_field($_POST['zipcode'] ?? ''),
        'user_type' => 'NURSING_HOME',
    ];

    $response = wp_remote_post('https://services.ratemynurse.org/api/nursinghome/create', [
        'body'    => json_encode($payload),
        'headers' => [
            'Content-Type'     => 'application/json',
            'Accept'           => 'application/json',
            'X-internal-Token' => '9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
        ],
        'timeout' => 20,
    ]);

    // ❌ Network error
    if (is_wp_error($response)) {
        wp_send_json([
            'success' => false,
            'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้',
            'errors'  => $response->get_error_messages(),
        ]);
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    // ❌ Invalid response
    if (empty($body)) {
        wp_send_json([
            'success' => false,
            'message' => 'ไม่สามารถอ่านข้อมูลจากเซิร์ฟเวอร์ได้',
            'errors'  => ['invalid_json_response'],
        ]);
    }

    // ❌ Unauthorized
    if ($status_code === 401 || $status_code === 403) {
        wp_send_json([
            'success' => false,
            'message' => $body['message'] ?? 'Unauthorized',
            'data'    => ['errors' => $body['errors'] ?? []],
        ]);
    }

    // ❌ Validation error
    if (!empty($body['errors']) && empty($body['success'])) {
        wp_send_json([
            'success' => false,
            'message' => $body['message'] ?? 'Validation failed',
            'data'    => ['errors' => $body['errors']],
        ]);
    }

    // ❌ ไม่ใช่กรณีต้องยืนยัน OTP (ถือว่าสมัครสมาชิกไม่สำเร็จ)
    if (empty($body['data']['otp_required'])) {
        wp_send_json([
            'success' => false,
            'message' => $body['message'] ?? 'เกิดข้อผิดพลาด ไม่สามารถสมัครสมาชิกได้',
            'data'    => ['errors' => $body['errors'] ?? []],
        ]);
    }

    // ✅ สำเร็จ — สร้าง user + profile ในทรานแซคชันเดียวเรียบร้อยแล้วฝั่ง backend
    // ยังไม่ set cookie/login เพราะ backend ส่ง OTP ไปที่เบอร์แล้ว ต้องยืนยันผ่าน verify_otp ก่อน
    wp_send_json([
        'success' => true,
        'message' => $body['message'] ?? 'สมัครสมาชิกสำเร็จ',
        'data'    => array_merge($body['data'], ['phone' => $payload['phone']]),
    ]);
}

add_action('wp_ajax_provider_profile_update', 'rmn_provider_profile_update');
add_action('wp_ajax_nopriv_provider_profile_update', 'rmn_provider_profile_update');
function rmn_provider_profile_update() {
    $token = null;
    if (isset($_COOKIE['access_token'])) {
        $token = sanitize_text_field($_COOKIE['access_token']);
    } else {
        wp_send_json([
            'success' => false,
            'data'    => ['message' => $body['error'] ?? 'Unauthorized'],
        ]);
    }

    $payload = [
        'id'            => sanitize_text_field($_POST['profile_id'] ?? ''),
        'name'          => sanitize_text_field($_POST['name'] ?? ''),
        'main_phone'    => sanitize_text_field($_POST['main_phone'] ?? ''),
        'res_phone'     => sanitize_text_field($_POST['res_phone'] ?? ''),
        'email'         => sanitize_text_field($_POST['email'] ?? ''),
        'facebook'      => sanitize_text_field($_POST['facebook'] ?? ''),
        'address'       => sanitize_text_field($_POST['address'] ?? ''),
        'province_id'   => sanitize_text_field($_POST['province_id'] ?? ''),
        'district_id'   => sanitize_text_field($_POST['district_id'] ?? ''),
        'sub_district_id' => sanitize_text_field($_POST['sub_district_id'] ?? ''),
        'zipcode'       => sanitize_text_field($_POST['zipcode'] ?? ''),
        'map_show'      => sanitize_text_field($_POST['map_show'] ?? ''),
        'license_no'    => sanitize_text_field($_POST['license_no'] ?? ''),
        'license_by'    => sanitize_text_field($_POST['license_by'] ?? ''),
        'license_start_date'    => sanitize_text_field($_POST['license_start_date'] ?? ''),
        'license_exp_date'      => sanitize_text_field($_POST['license_exp_date'] ?? ''),
        'certificates'  => sanitize_text_field($_POST['certificates'] ?? ''),
        'hospital_no'   => sanitize_text_field($_POST['hospital_no'] ?? ''),
        'cost_per_day'  => sanitize_text_field($_POST['cost_per_day'] ?? 0),
        'cost_per_month'=> sanitize_text_field($_POST['cost_per_month'] ?? 0),
        'deposit'       => sanitize_text_field($_POST['deposit'] ?? 0),
        'registration_fee' => sanitize_text_field($_POST['registration_fee'] ?? 0),
        'special_food_expenses' => sanitize_text_field($_POST['special_food_expenses'] ?? ''),
        'physical_therapy_fee' => sanitize_text_field($_POST['physical_therapy_fee'] ?? ''),
        'delivery_fee' => sanitize_text_field($_POST['delivery_fee'] ?? ''),
        'laundry_service' => sanitize_text_field($_POST['laundry_service'] ?? ''),
        'social_security' => sanitize_text_field($_POST['social_security'] ?? 0),
        'private_health_insurance' => sanitize_text_field($_POST['private_health_insurance'] ?? 0),
        'installment' => sanitize_text_field($_POST['installment'] ?? 0),
        'payment_methods' => sanitize_text_field($_POST['payment_methods'] ?? ''),
        'description' => sanitize_text_field($_POST['description'] ?? ''),
        'youtube_url' => sanitize_text_field($_POST['youtube_url'] ?? ''),
        'home_service_type' => $_POST['home_service_type'] ?? [],
        'additional_service_type' => $_POST['additional_service_type'] ?? [],
        'etc_service' => sanitize_text_field($_POST['etc_service'] ?? ''),
        'building_no' => sanitize_text_field($_POST['building_no'] ?? 0),
        'total_room' => sanitize_text_field($_POST['total_room'] ?? 0),
        'private_room_no' => sanitize_text_field($_POST['private_room_no'] ?? 0),
        'duo_room_no' => sanitize_text_field($_POST['duo_room_no'] ?? 0),
        'shared_room_three_beds' => sanitize_text_field($_POST['shared_room_three_beds'] ?? 0),
        'max_serve_no' => sanitize_text_field($_POST['max_serve_no'] ?? 0),
        'area' => sanitize_text_field($_POST['area'] ?? 0),
        'special_facilities' => $_POST['special_facilities'] ?? [],
        'facilities' => $_POST['facilities'] ?? [],
        'ambulance' => sanitize_text_field($_POST['ambulance'] ?? 0),
        'ambulance_amount' => sanitize_text_field($_POST['ambulance_amount'] ?? 0),
        'van_shuttle' => sanitize_text_field($_POST['ambulance_amount'] ?? 0),
        'special_medical_equipment' => sanitize_text_field($_POST['special_medical_equipment'] ?? ''),
        'center_highlights' => $_POST['center_highlights'] ?? [],
        'status' => 0,
    ];

    $endpoint = "https://services.ratemynurse.org/api/nursing-home/profile/update";
    $response = wp_remote_post($endpoint, [
        'body'    => json_encode($payload),
        'headers' => [
            'Content-Type'     => 'application/json',
            'Accept'           => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ],
    ]);

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code === 500) {
        wp_send_json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดติดต่อ System Admin',
            'errors' => $body['errors'] ?? [],
        ]);
    }
}

add_action('wp_ajax_nursing_register', 'rmn_nursing_register');
add_action('wp_ajax_nopriv_nursing_register', 'rmn_nursing_register');
function rmn_nursing_register() {
    $postData = [
        'firstname' => sanitize_text_field($_POST['nursingFirstName'] ?? ''),
        'lastname' => sanitize_text_field($_POST['nursingLastName'] ?? ''),
        'nickname'  => sanitize_text_field($_POST['nursingNickname'] ?? ''),
        'email' => sanitize_text_field($_POST['nursingEmail'] ?? ''),
        'phone' => sanitize_text_field($_POST['nursingPhone'] ?? ''),
        'date_of_birth' => sanitize_text_field($_POST['nursingBirthDate'] ?? ''),
        'gender' => sanitize_text_field($_POST['nursingGender'] ?? ''),
        'care_type' => sanitize_text_field($_POST['nursingCareType'] ?? ''),
        'blood' => sanitize_text_field($_POST['nursingBlood'] ?? ''),
        'medical_condition' => sanitize_text_field($_POST['medical_condition'] ?? ''),
        'history_of_drug_allergy' => sanitize_text_field($_POST['history_of_drug_allergy'] ?? ''),
        'medical_condition_detail' => sanitize_textarea_field($_POST['medical_condition_detail'] ?? ''),
        'history_of_drug_allergy_detail' => sanitize_textarea_field($_POST['history_of_drug_allergy_detail'] ?? ''),
        'user_type' => 'NURSING',
    ];

    // แนบรูปถ่าย (บังคับ) ด้วย CURLFile — เดิม endpoint นี้ส่งเป็น JSON ผ่าน wp_remote_post() ทำให้ไฟล์
    // ที่ผู้ใช้อัปโหลดหายไปเงียบๆ ไม่เคยถึง backend เลย เปลี่ยนมาใช้ curl+CURLFile แบบเดียวกับ
    // nursing_profile_draft_save() ด้านบนแทน
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (in_array($file['type'], $allowedTypes)) {
            $postData['profile_photo'] = new CURLFile(
                $file['tmp_name'],
                $file['type'],
                $file['name']
            );
        }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://services.ratemynurse.org/api/nursing/create');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'X-internal-Token: 9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
    ]);

    $response = curl_exec($ch);

    // ❌ Network error
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        wp_send_json([
            'success' => false,
            'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้',
            'errors'  => [$error],
        ]);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $body = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json([
            'success' => false,
            'message' => 'ไม่สามารถอ่านข้อมูลจากเซิร์ฟเวอร์ได้',
        ]);
    }

    if (!empty($body['error']) || $httpCode === 401) {
        wp_send_json([
            'success' => false,
            'data'    => ['message' => $body['error'] ?? 'Unauthorized'],
        ]);
    }

    if (!empty($body['errors'])) {
        wp_send_json([
            'success' => false,
            'data' => [
                'message' => $body['message'] ?? 'กรุณาตรวจสอบข้อมูล!',
                'errors'  => $body['errors'],
            ]
        ]);
    }

    // สมัครสำเร็จแล้ว แต่ยังไม่ได้ access_token — backend ส่ง OTP ไปที่เบอร์แล้ว ต้องยืนยันผ่าน
    // /api/otp/verify (action verify_otp) ก่อนถึงจะได้ token จริงและ login เข้าระบบสมบูรณ์
    if (empty($body['data']['otp_required'])) {
        wp_send_json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด ไม่สามารถบันทึกข้อมูลได้!',
        ]);
    }

    wp_send_json_success([
        'otp_required' => true,
        'phone'        => $postData['phone'],
    ]);
}

add_action('wp_ajax_nursing_profile_draft_save', 'nursing_profile_draft_save');
add_action('wp_ajax_nopriv_nursing_profile_draft_save', 'nursing_profile_draft_save');
function nursing_profile_draft_save()
{
    // ตรวจสอบ token
    $token = null;
    if (isset($_COOKIE['access_token'])) {
        $token = sanitize_text_field($_COOKIE['access_token']);
    } else {
        wp_send_json([
            'success' => false,
            'data'    => ['message' => 'Unauthorized'],
        ]);
        wp_die();
    }

    // รับข้อมูล draft
    $draft = isset($_POST['draft']) ? wp_unslash($_POST['draft']) : '';
    
    if (empty($draft)) {
        wp_send_json([
            'success' => false,
            'message' => 'No draft data received'
        ]);
        wp_die();
    }

    $raw = json_decode($draft, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        wp_die();
    }

    // เตรียมข้อมูล costs
    $costs = [];
    $costs['daily'] = array(
        'FULL_STAY'  => $raw['daily_full_stay_cost'] ?? null,
        'FULL_ROUND' => $raw['daily_full_round_cost'] ?? null,
        'PART_STAY'  => $raw['daily_part_stay_cost'] ?? null,
        'PART_ROUND' => $raw['daily_part_round_cost'] ?? null
    );
    $costs['monthly'] = array(
        'FULL_STAY'  => $raw['monthly_full_stay_cost'] ?? null,
        'FULL_ROUND' => $raw['monthly_full_round_cost'] ?? null,
        'PART_STAY'  => $raw['monthly_part_stay_cost'] ?? null,
        'PART_ROUND' => $raw['monthly_part_round_cost'] ?? null
    );

    // เตรียม payload ข้อมูลปกติ
    $postData = [
        'id'                => sanitize_text_field($raw['profile'] ?? ''),
        'is_draft'          => 1,
        'status'            => 0,
        'firstname'         => sanitize_text_field($raw['firstname'] ?? ''),
        'lastname'          => sanitize_text_field($raw['lastname'] ?? ''),
        'nickname'          => sanitize_text_field($raw['nickname'] ?? ''),
        'gender'            => sanitize_text_field($raw['gender'] ?? ''),
        'phone'             => sanitize_text_field($raw['phone'] ?? ''),
        'email'             => sanitize_email($raw['email'] ?? ''),
        'date_of_birth'     => sanitize_text_field($raw['date_of_birth'] ?? ''),
        'blood'             => sanitize_text_field($raw['blood'] ?? ''),
        'care_type'         => sanitize_text_field($raw['care_type'] ?? ''),
        'address'           => sanitize_textarea_field($raw['address'] ?? ''),
        'province_id'       => sanitize_text_field($raw['province_id']['id'] ?? ''),
        'district_id'       => sanitize_text_field($raw['district_id']['id'] ?? ''),
        'sub_district_id'   => sanitize_text_field($raw['sub_district_id']['id'] ?? ''),
        'zipcode'           => sanitize_text_field($raw['zipcode'] ?? ''),
        'graducated'        => sanitize_text_field($raw['graducated'] ?? ''),
        'edu_ins'           => sanitize_text_field($raw['edu_ins'] ?? ''),
        'graduated_year'    => sanitize_text_field($raw['graduated_year'] ?? ''),
        'gpa'               => sanitize_text_field($raw['gpa'] ?? ''),
        'cert_no'           => sanitize_text_field($raw['cert_no'] ?? ''),
        'cert_date'         => sanitize_text_field($raw['cert_date'] ?? ''),
        'cert_expire'       => sanitize_text_field($raw['cert_expire'] ?? ''),
        'cert_etc'          => sanitize_text_field($raw['cert_etc'] ?? ''),
        'extra_courses'     => sanitize_textarea_field($raw['extra_courses'] ?? ''),
        'current_workplace' => sanitize_text_field($raw['current_workplace'] ?? ''),
        'department'        => sanitize_text_field($raw['department'] ?? ''),
        'position'          => sanitize_text_field($raw['position'] ?? ''),
        'exp'               => sanitize_text_field($raw['exp'] ?? NULL),
        'work_type'         => sanitize_text_field($raw['work_type'] ?? ''),
        'extra_shirft'      => sanitize_text_field($raw['extra_shirft'] ?? ''),
        'languages'         => sanitize_text_field($raw['languages'] ?? ''),
        'about'             => sanitize_textarea_field($raw['about'] ?? ''),
        'skills'            => json_encode($raw['skills'] ?? []),
        'other_skills'      => sanitize_text_field($raw['other_skills'] ?? ''),
        'costs'             => json_encode($costs),
    ];

    // เพิ่ม profile_image (single file)
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        
        // ตรวจสอบประเภทไฟล์
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (in_array($file['type'], $allowedTypes)) {
            $postData['profile_image'] = new CURLFile(
                $file['tmp_name'],
                $file['type'],
                $file['name']
            );
        }
    }

    // เพิ่ม cvs_images (multiple files)
    if (isset($_FILES['cvs_images']) && is_array($_FILES['cvs_images']['name'])) {
        $files = $_FILES['cvs_images'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            // ตรวจสอบว่ามีไฟล์และไม่มี error
            if (isset($files['tmp_name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
                // ตรวจสอบว่าไฟล์มีอยู่จริง
                if (file_exists($files['tmp_name'][$i])) {
                    $postData["cvs_images[{$i}]"] = new CURLFile(
                        $files['tmp_name'][$i],
                        $files['type'][$i],
                        $files['name'][$i]
                    );
                }
            }
        }
    }

    // เพิ่ม detail_images (multiple files)
    if (isset($_FILES['detail_images']) && is_array($_FILES['detail_images']['name'])) {
        $files = $_FILES['detail_images'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            // ตรวจสอบว่ามีไฟล์และไม่มี error
            if (isset($files['tmp_name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
                // ตรวจสอบว่าไฟล์มีอยู่จริง
                if (file_exists($files['tmp_name'][$i])) {
                    $postData["detail_images[{$i}]"] = new CURLFile(
                        $files['tmp_name'][$i],
                        $files['type'][$i],
                        $files['name'][$i]
                    );
                }
            }
        }
    }

    // ส่งข้อมูลด้วย cURL
    $endpoint = "https://services.ratemynurse.org/api/nursing/profile/update";
    
    $ch = curl_init();
    
    // ตั้งค่า cURL options
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // timeout 60 วินาที
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // connection timeout 30 วินาที
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ]);

    // Execute
    $response = curl_exec($ch);
    // ตรวจสอบ error
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        
        wp_send_json([
            'success' => false,
            'message' => 'cURL Error: ' . $error,
        ]);
        wp_die();
    }
    
    // ดึงข้อมูล response
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    curl_close($ch);

    // Parse response
    $result = json_decode($response, true);
    
    // ถ้า decode ไม่ได้ ให้ส่ง response ดิบกลับไป
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json([
            'success' => false,
            'message' => 'Invalid JSON response from API',
            'http_code' => $httpCode,
            'raw_response' => $response,
        ]);
        wp_die();
    }

    // ส่ง response กลับ
    wp_send_json([
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => $result,
        'http_code' => $httpCode,
        'message' => $result['message'] ?? 'Request completed',
    ]);
    
    wp_die();
}

// nursing_profile_update
add_action('wp_ajax_nursing_profile_update', 'rmn_nursing_profile_update');
add_action('wp_ajax_nopriv_nursing_profile_update', 'rmn_nursing_profile_update');
function rmn_nursing_profile_update() {
    $token = null;
    if (isset($_COOKIE['access_token'])) {
        $token = sanitize_text_field($_COOKIE['access_token']);
    } else {
        wp_send_json([
            'success' => false,
            'data'    => ['message' => $body['error'] ?? 'Unauthorized'],
        ]);
    }

    $profile_id = sanitize_text_field($_POST['profile']);
    if ((int) $profile_id == NULL || !$profile_id) {
        wp_send_json([
            'success' => false,
            'data'    => ['message' => 'ข้อมูล Profile ไม่ถูกต้องติดต่อผู้ดูแลระบบ'],
        ]);
    }

    $payload = [
        'id'            => sanitize_text_field($_POST['profile_id'] ?? ''),
    ];
}

add_action('wp_ajax_cv_file_delete', 'rmn_cv_file_delete');
add_action('wp_ajax_nopriv_cv_file_delete', 'rmn_cv_file_delete');
function rmn_cv_file_delete() {

    // Access token
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json([
            'success' => false,
            'message' => 'Unauthorized: no access token',
        ]);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    // Get ID from AJAX
    $id = isset($_POST['cv']) ? intval($_POST['cv']) : 0;

    if ($id <= 0) {
        wp_send_json([
            'success' => false,
            'message' => 'Invalid CV ID',
        ]);
    }

    // API Endpoint
    $endpoint = "https://services.ratemynurse.org/api/cv/{$id}/delete";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);               // Required for POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Accept: application/json"
    ]);

    // ไม่มี POSTFIELDS เพราะไม่จำเป็นต้องส่ง body

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);

        wp_send_json([
            'success' => false,
            'message' => "cURL Error: {$error}",
        ]);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $result = json_decode($response, true);

    // ตรวจสอบผลลบ
    if ($httpCode >= 200 && $httpCode < 300) {
        wp_send_json([
            'success' => true,
            'message' => 'Deleted successfully',
            'data' => $result
        ]);
    }

    wp_send_json([
        'success' => false,
        'message' => $result['message'] ?? "Delete failed ({$httpCode})",
        'response' => $result
    ]);
}

add_action('wp_ajax_detail_file_delete', 'rmn_detail_file_delete');
add_action('wp_ajax_nopriv_detail_file_delete', 'rmn_detail_file_delete');
function rmn_detail_file_delete() {
    // Access token
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json([
            'success' => false,
            'message' => 'Unauthorized: no access token',
        ]);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    // Get ID from AJAX
    $id = isset($_POST['detail_id']) ? intval($_POST['detail_id']) : 0;

    if ($id <= 0) {
        wp_send_json([
            'success' => false,
            'message' => 'Invalid Image',
        ]);
    }

    // API Endpoint
    $endpoint = "https://services.ratemynurse.org/api/detail_image/{$id}/delete";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);               // Required for POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Accept: application/json"
    ]);

    // ไม่มี POSTFIELDS เพราะไม่จำเป็นต้องส่ง body

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);

        wp_send_json([
            'success' => false,
            'message' => "cURL Error: {$error}",
        ]);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $result = json_decode($response, true);

    // ตรวจสอบผลลบ
    if ($httpCode >= 200 && $httpCode < 300) {
        wp_send_json([
            'success' => true,
            'message' => 'Deleted successfully',
            'data' => $result
        ]);
    }

    wp_send_json([
        'success' => false,
        'message' => $result['message'] ?? "Delete failed ({$httpCode})",
        'response' => $result
    ]);
}

add_action('wp_ajax_nursing_home_image_delete', 'rmn_nursing_home_image_delete');
function rmn_nursing_home_image_delete() {
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized: no access token']);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

    if ($id <= 0) {
        wp_send_json(['success' => false, 'message' => 'Invalid image ID']);
    }

    $endpoint = "https://services.ratemynurse.org/api/nursing-home/image/{$id}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        wp_send_json(['success' => false, 'message' => "cURL Error: {$error}"]);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        wp_send_json(['success' => true, 'message' => 'Deleted successfully']);
    }

    wp_send_json([
        'success' => false,
        'message' => $result['message'] ?? "Delete failed ({$httpCode})"
    ]);
}

add_action('wp_ajax_nursing_home_license_delete', 'rmn_nursing_home_license_delete');
function rmn_nursing_home_license_delete() {
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized: no access token']);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $id = isset($_POST['license_id']) ? intval($_POST['license_id']) : 0;

    if ($id <= 0) {
        wp_send_json(['success' => false, 'message' => 'Invalid license ID']);
    }

    $endpoint = "https://services.ratemynurse.org/api/nursing-home/license/{$id}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        wp_send_json(['success' => false, 'message' => "cURL Error: {$error}"]);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        wp_send_json(['success' => true, 'message' => 'Deleted successfully']);
    }

    wp_send_json([
        'success' => false,
        'message' => $result['message'] ?? "Delete failed ({$httpCode})"
    ]);
}

add_action('wp_ajax_nursing_home_staff_delete', 'rmn_nursing_home_staff_delete');
function rmn_nursing_home_staff_delete() {
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json(['success' => false, 'message' => 'Unauthorized: no access token']);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;

    if ($id <= 0) {
        wp_send_json(['success' => false, 'message' => 'Invalid staff ID']);
    }

    $endpoint = "https://services.ratemynurse.org/api/nursing-home/staff/{$id}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        wp_send_json(['success' => false, 'message' => "cURL Error: {$error}"]);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        wp_send_json(['success' => true, 'message' => 'Deleted successfully']);
    }

    wp_send_json([
        'success' => false,
        'message' => $result['message'] ?? "Delete failed ({$httpCode})"
    ]);
}

add_action('wp_ajax_compare_nurse', 'rmn_compare_nurse');
add_action('wp_ajax_nopriv_compare_nurse', 'rmn_compare_nurse');
function rmn_compare_nurse() {

    // 0. Auth
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error([
            'message' => 'Unauthorized: no access token',
        ], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $token_hash = md5($token);

    // 1. Read + sanitize items
    $items = isset($_POST['items'])
        ? json_decode(stripslashes($_POST['items']), true)
        : [];

    if (!is_array($items)) {
        $items = [];
    }

    $items = array_unique(array_map('intval', $items));

    // ===== LIMIT =====
    if (empty($items)) {
        wp_send_json_error([
            'message' => 'No nurse selected',
        ], 422);
    }

    if (count($items) > 3) {
        wp_send_json_error([
            'message' => 'Maximum 3 nurses allowed',
        ], 422);
    }

    // 2. Cache key (ผูกกับ token)
    $cache_key = 'rmn_compare_nurse_' . $token_hash . '_' . md5(wp_json_encode($items));

    // ===== CACHE CHECK =====
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success([
            'cached' => true,
            'data'   => $cached,
            'ids'    => $items,
        ]);
    }

    // ===== SAVE compare items (แทน update_option) =====
    set_transient(
        'compare_nurse_items_' . $token_hash,
        $items,
        10 * MINUTE_IN_SECONDS
    );

    // 3. Call External API
    $endpoint = 'https://services.ratemynurse.org/api/nursing/compare';

    $payload = wp_json_encode([
        'nurse_ids' => $items,
    ]);

    $ch = curl_init($endpoint);

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_HTTPHEADER     => [
            'X-internal-Token: 9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        wp_send_json_error([
            'message' => curl_error($ch),
        ], 500);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error([
            'message' => 'Invalid JSON from API',
            'raw'     => $response,
        ], 500);
    }

    // ===== TOKEN EXPIRED =====
    if ($httpCode === 401) {
        delete_transient($cache_key);
        delete_transient('compare_nurse_items_' . $token_hash);

        wp_send_json_error([
            'message' => 'Token expired',
        ], 401);
    }

    // ===== SAVE CACHE =====
    set_transient(
        $cache_key,
        $result,
        10 * MINUTE_IN_SECONDS
    );

    // 4. Response
    wp_send_json_success([
        'cached' => false,
        'data'   => $result,
        'ids'    => $items,
        'code'   => $httpCode,
    ]);
}

add_action('wp_ajax_compare_nursinghome', 'rmn_compare_nursinghome');
add_action('wp_ajax_nopriv_compare_nursinghome', 'rmn_compare_nursinghome');
function rmn_compare_nursinghome() {

    // 0. Auth
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error([
            'message' => 'Unauthorized: no access token',
        ], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $token_hash = md5($token);

    // 1. Read + sanitize items
    $items = isset($_POST['items'])
        ? json_decode(stripslashes($_POST['items']), true)
        : [];

    if (!is_array($items)) {
        $items = [];
    }

    $items = array_unique(array_map('intval', $items));

    // ===== LIMIT =====
    if (empty($items)) {
        wp_send_json_error([
            'message' => 'No nurse selected',
        ], 422);
    }

    if (count($items) > 3) {
        wp_send_json_error([
            'message' => 'Maximum 3 nurses allowed',
        ], 422);
    }

    // 2. Cache key (ผูกกับ token)
    $cache_key = 'rmn_compare_nursinghome_' . $token_hash . '_' . md5(wp_json_encode($items));

    // ===== CACHE CHECK =====
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success([
            'cached' => true,
            'data'   => $cached,
            'ids'    => $items,
        ]);
    }

    // ===== SAVE compare items (แทน update_option) =====
    set_transient(
        'compare_home_item_' . $token_hash,
        $items,
        10 * MINUTE_IN_SECONDS
    );

    // 3. Call External API
    $endpoint = 'https://services.ratemynurse.org/api/nursing-home/compare';

    $payload = wp_json_encode([
        'nursinghome_profile_ids' => $items,
    ]);

    $ch = curl_init($endpoint);

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_HTTPHEADER     => [
            'X-internal-Token: 9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        wp_send_json_error([
            'message' => curl_error($ch),
        ], 500);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error([
            'message' => 'Invalid JSON from API',
            'raw'     => $response,
        ], 500);
    }

    // ===== TOKEN EXPIRED =====
    if ($httpCode === 401) {
        delete_transient($cache_key);
        delete_transient('compare_nursinghome_items_' . $token_hash);

        wp_send_json_error([
            'message' => 'Token expired',
        ], 401);
    }

    // ===== SAVE CACHE =====
    set_transient(
        $cache_key,
        $result,
        10 * MINUTE_IN_SECONDS
    );

    // 4. Response
    wp_send_json_success([
        'cached' => false,
        'data'   => $result,
        'ids'    => $items,
        'code'   => $httpCode,
    ]);
}

add_action('wp_ajax_toggle_favorite', 'rmn_toggle_favorite');
add_action('wp_ajax_nopriv_toggle_favorite', 'rmn_toggle_favorite');
function rmn_toggle_favorite() {
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error([
            'message' => 'Unauthorized: no access token',
        ], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    $profile_id   = intval($_POST['profile_id'] ?? 0);
    $profile_type = strtoupper(sanitize_text_field($_POST['profile_type'] ?? ''));

    if (!$profile_id || !$profile_type) {
        wp_send_json_error(['message' => 'Invalid data'], 422);
    }

    // Invalidate favorite-state transient cache (ของ NursingHome.php / Nursing.php / rmn_get_my_favorite_ids)
    $token_hash = substr(hash('sha256', $token), 0, 16);
    delete_transient('rmn_favs_nh_' . $token_hash);
    delete_transient('rmn_favs_nursing_' . $token_hash);
    delete_transient('rmn_fav_ids_nursing_' . $token_hash);
    delete_transient('rmn_fav_ids_nursing_home_' . $token_hash);

    $endpoint = 'https://services.ratemynurse.org/api/favorite/toggle';

    $payload = wp_json_encode([
        'profile_id'   => $profile_id,
        'profile_type' => $profile_type,
    ]);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        wp_send_json_error(['message' => curl_error($ch)], 500);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        wp_send_json_error([
            'message' => $result['message'] ?? 'API error',
        ], $httpCode);
    }

    wp_send_json_success($result);
}

add_action('wp_ajax_get_my_favorite_ids', 'rmn_get_my_favorite_ids');
add_action('wp_ajax_nopriv_get_my_favorite_ids', 'rmn_get_my_favorite_ids');
function rmn_get_my_favorite_ids() {
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token        = sanitize_text_field($_COOKIE['access_token']);
    $profile_type = strtoupper(sanitize_text_field($_POST['profile_type'] ?? ''));

    if (!in_array($profile_type, ['NURSING', 'NURSING_HOME'], true)) {
        wp_send_json_error(['message' => 'Invalid data'], 422);
    }

    $cache_key = 'rmn_fav_ids_' . strtolower($profile_type) . '_' . substr(hash('sha256', $token), 0, 16);
    $cached    = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
    }

    $endpoint = add_query_arg(['profile_type' => $profile_type], 'https://services.ratemynurse.org/api/favorite/ids');

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ],
        'timeout' => 10,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        wp_send_json_error(['message' => 'API error'], 500);
    }

    $result = json_decode(wp_remote_retrieve_body($response), true);
    $ids    = $result['data'] ?? [];

    set_transient($cache_key, $ids, 30);

    wp_send_json_success($ids);
}

add_action('wp_ajax_remove_provider_favorite', 'rmn_remove_provider_favorite');
add_action('wp_ajax_nopriv_remove_provider_favorite', 'rmn_remove_provider_favorite');
function rmn_remove_provider_favorite() {
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error([
            'message' => 'Unauthorized: no access token',
        ], 401);
    }

    $token        = sanitize_text_field($_COOKIE['access_token']);
    $favorite_id  = intval($_POST['favorite_id'] ?? 0);
    $profile_type = strtoupper(sanitize_text_field($_POST['profile_type'] ?? ''));

    if (!$favorite_id || !in_array($profile_type, ['NURSING', 'NURSING_HOME'], true)) {
        wp_send_json_error(['message' => 'Invalid data'], 422);
    }

    $path = $profile_type === 'NURSING' ? 'nursing' : 'nursing-home';
    $endpoint = "https://services.ratemynurse.org/api/{$path}/provider/favorites/{$favorite_id}";

    $response = wp_remote_request($endpoint, [
        'method'  => 'DELETE',
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $httpCode = wp_remote_retrieve_response_code($response);
    $result   = json_decode(wp_remote_retrieve_body($response), true);

    if ($httpCode < 200 || $httpCode >= 300) {
        wp_send_json_error([
            'message' => $result['message'] ?? 'API error',
        ], $httpCode);
    }

    wp_send_json_success($result);
}

add_action('wp_ajax_get_member_favorites', 'rmn_get_member_favorites');
add_action('wp_ajax_nopriv_get_member_favorites', 'rmn_get_member_favorites');
function rmn_get_member_favorites() {
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token        = sanitize_text_field($_COOKIE['access_token']);
    $profile_type = sanitize_text_field($_POST['profile_type'] ?? 'NURSING');
    $key          = sanitize_text_field($_POST['key'] ?? '');
    $page         = intval($_POST['page'] ?? 1);

    $endpoint = add_query_arg([
        'profile_type' => $profile_type,
        'key'          => $key,
        'page'         => $page,
    ], 'https://services.ratemynurse.org/api/favorite');

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $httpCode = wp_remote_retrieve_response_code($response);
    $body     = wp_remote_retrieve_body($response);
    $result   = json_decode($body, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        wp_send_json_error([
            'message' => $result['message'] ?? 'API error',
        ], $httpCode);
    }

    wp_send_json_success($result['data'] ?? []);
}

add_action('wp_ajax_submit_contact', 'rmn_submit_contact');
add_action('wp_ajax_nopriv_submit_contact', 'rmn_submit_contact');
function rmn_submit_contact() {

    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    if (empty($_POST['phone'])) {
        wp_send_json_error([
            'errors' => ['phone' => 'กรุณากรอกเบอร์โทรศัพท์']
        ], 422);
    }

    if (empty($_POST['startdate'])) {
        wp_send_json_error([
            'errors' => ['startdate' => 'กรุณาเลือกวันที่']
        ], 422);
    }

    $range = sanitize_text_field($_POST['startdate']);
    if( sanitize_text_field($_POST['provider_role']) == 'NURSING_HOME' ) { 
        $range = $range . ' - ' . $range;
    }
    
    $parts = explode(' - ', $range);
    
    if (count($parts) !== 2) {
        wp_send_json_error([
            'errors' => ['startdate' => 'รูปแบบวันที่ไม่ถูกต้อง']
        ], 422);
    }
    

    [$start, $end] = array_map('trim', $parts);

    $startDate = DateTime::createFromFormat('d/m/Y', $start);
    $endDate   = DateTime::createFromFormat('d/m/Y', $end);

    if (!$startDate || !$endDate) {
        wp_send_json_error([
            'errors' => ['startdate' => 'รูปแบบวันที่ไม่ถูกต้อง']
        ], 422);
    }

    $today = new DateTime('today');

    if ($startDate < $today || $endDate < $startDate) {
        wp_send_json_error([
            'errors' => ['startdate' => 'ช่วงวันที่ไม่ถูกต้อง']
        ], 422);
    }

    $description = sanitize_text_field($_POST['description']);

    $endpoint = 'https://services.ratemynurse.org/api/member/contact/create';

    $payload = wp_json_encode([
        'provider_id'  => sanitize_text_field($_POST['provider_id']),
        'provider_role' => sanitize_text_field($_POST['provider_role']),
        'description' => $description ?? 'รายละเอียดงาน (ไม่ระบุ)',
        'phone' => sanitize_text_field($_POST['phone']),
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
        'facebook' => sanitize_text_field($_POST['facebook']) ?? null,
        'lineid' => sanitize_text_field($_POST['lineid']) ?? null,
        'email' => sanitize_text_field($_POST['email']) ?? null,
        'type'  => 'USER',
    ]);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        wp_send_json_error(['message' => curl_error($ch)], 500);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        if ( $httpCode == 403 ) {
            $result['message'] = 'สำหรับผู้ใช้บริการเท่านั้น';
        }
        wp_send_json_error([
            'message' => $result['message'] ?? 'API error'
        ], $httpCode);
    }

    wp_send_json_success(['message' => 'success']);
}

add_action('wp_ajax_read_all_notifications', 'rmn_read_all_notifications');
add_action('wp_ajax_nopriv_read_all_notifications', 'rmn_read_all_notifications');
function rmn_read_all_notifications() {
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    $endpoint = 'https://services.ratemynurse.org/api/notification/read-all';
}

add_action('wp_ajax_rmn_set_notification_as_read', 'rmn_set_notification_as_read');
add_action('wp_ajax_nopriv_rmn_set_notification_as_read', 'rmn_set_notification_as_read');

function rmn_set_notification_as_read()
{
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    if (empty($_POST['noti_id'])) {
        wp_send_json_error(['message' => 'Notification ID missing'], 400);
    }

    $token   = sanitize_text_field($_COOKIE['access_token']);
    $noti_id = intval($_POST['noti_id']);

    $endpoint = "https://services.ratemynurse.org/api/notification/{$noti_id}/read/";

    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => $response->get_error_message()
        ], 500);
    }

    $status = wp_remote_retrieve_response_code($response);

    if ($status !== 200 && $status !== 204) {
        wp_send_json_error([
            'message' => 'API failed',
            'status'  => $status
        ], $status);
    }

    wp_send_json_success([
        'message' => 'Notification marked as read'
    ]);
}

add_action('wp_ajax_log_analytics', 'rmn_log_analytics');
add_action('wp_ajax_nopriv_log_analytics', 'rmn_log_analytics');
function rmn_log_analytics() {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $action = sanitize_text_field($input['action'] ?? '');
    $subject_id = intval($input['subject_id'] ?? 0);
    $subject_type = sanitize_text_field($input['subject_type'] ?? '');

    if (!$action || !$subject_id || !$subject_type) {
        wp_send_json_error(['message' => 'Missing required fields'], 422);
    }

    // Validate action type
    $allowed_actions = ['profile_view', 'click_call', 'click_contact', 'click_favorite', 'click_compare', 'click_share'];
    if (!in_array($action, $allowed_actions)) {
        wp_send_json_error(['message' => 'Invalid action type'], 422);
    }

    // Actions ที่ต้อง login (ยกเว้น profile_view)
    $auth_required_actions = ['click_call', 'click_contact'];
    $token = !empty($_COOKIE['access_token']) ? sanitize_text_field($_COOKIE['access_token']) : null;

    if (in_array($action, $auth_required_actions) && !$token) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    // Validate subject type
    $allowed_subject_types = [
        'App\\Models\\NursingProfile',
        'App\\Models\\NursingHomeProfile'
    ];
    if (!in_array($subject_type, $allowed_subject_types)) {
        wp_send_json_error(['message' => 'Invalid subject type'], 422);
    }

    $payload = [
        'action' => $action,
        'subject_id' => $subject_id,
        'subject_type' => $subject_type,
    ];

    // Add metadata if provided
    if (!empty($input['metadata']) && is_array($input['metadata'])) {
        $payload['metadata'] = $input['metadata'];
    }

    // สำหรับ profile_view ที่ไม่มี token ใช้ endpoint แยก (no auth)
    if ($action === 'profile_view' && !$token) {
        $endpoint = 'https://services.ratemynurse.org/api/analytics/track-view';
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    } else {
        $endpoint = 'https://services.ratemynurse.org/api/analytics/log';
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    $response = wp_remote_post($endpoint, [
        'body' => json_encode($payload),
        'headers' => $headers,
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $httpCode = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($httpCode >= 200 && $httpCode < 300) {
        wp_send_json_success($body);
    }

    wp_send_json_error($body, $httpCode);
}

add_action('wp_ajax_get_line_oauth_url', 'rmn_get_line_oauth_url');
add_action('wp_ajax_nopriv_get_line_oauth_url', 'rmn_get_line_oauth_url');

function rmn_get_line_oauth_url() {
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    // เรียก Laravel backend
    $response = wp_remote_post('https://services.ratemynurse.org/api/line/oauth-url', [
        'body' => json_encode(['token' => $token]),
        'headers' => [
            'Content-Type' => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'ไม่สามารถเชื่อมต่อ backend']);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($body['url'])) {
        wp_send_json_success(['url' => $body['url']]);
    } else {
        wp_send_json_error(['message' => 'เกิดข้อผิดพลาด']);
    }
}

add_action('wp_ajax_rmn_get_timeseries', 'rmn_get_timeseries');
function rmn_get_timeseries()
{
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    $type = $_POST['range_type'] ?? 'day';
    $tz   = new DateTimeZone('Asia/Bangkok');
    $now  = new DateTime('now', $tz);

    switch ($type)
    {
        case 'day':
            /**
             * DAY = วันนี้ (รายชั่วโมง)
             * 00:00 → 23:59
             */
            $start = (clone $now)->setTime(0, 0, 0);
            $end   = (clone $now)->setTime(23, 59, 59);
            break;

        case 'week':
            /**
             * WEEK = จันทร์ → อาทิตย์ (รายวัน)
             */
            $start = (clone $now)->modify('monday this week')->setTime(0, 0, 0);
            $end   = (clone $start)->modify('sunday this week')->setTime(23, 59, 59);
            break;

        case 'month':
            /**
             * MONTH = ปีปัจจุบันเต็มปี (ม.ค. → ธ.ค.)
             * แสดง 12 เดือน เดือนไหนไม่มีข้อมูลก็แสดง 0
             */
            $start = new DateTime($now->format('Y') . '-01-01', $tz);
            $start->setTime(0, 0, 0);
            $end = new DateTime($now->format('Y') . '-12-31', $tz);
            $end->setTime(23, 59, 59);
            break;

        case 'custom':
            /**
             * CUSTOM = user เลือกเอง
             * backend (Laravel) จะตัดสิน group_by
             */
            if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
                wp_send_json_error('Custom range requires start_date and end_date');
            }

            try {
                $start = new DateTime($_POST['start_date'], $tz);
                $end   = new DateTime($_POST['end_date'], $tz);

                if ($start > $end) {
                    wp_send_json_error('start_date must be before end_date');
                }

                $start->setTime(0, 0, 0);
                $end->setTime(23, 59, 59);

            } catch (Exception $e) {
                wp_send_json_error('Invalid date format');
            }
            break;

        default:
            wp_send_json_error('Invalid range type');
    }

    /**
     * ส่งไปให้ Laravel API
     * Laravel จะเป็นคนตัดสิน group_by = hour / day / week / month
     */
    $queryArgs = [
        'start_date' => $start->format('Y-m-d'),
        'end_date'   => $end->format('Y-m-d'),
    ];
    if (!empty($_POST['profile_id'])) {
        $queryArgs['profile_id'] = sanitize_text_field($_POST['profile_id']);
    }
    $endpoint = add_query_arg($queryArgs, 'https://services.ratemynurse.org/api/analytics/my-stats/timeseries');

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ],
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    wp_send_json_success($body);
}

/**
 * AJAX: Track action (click_contact, click_call)
 * รองรับทั้ง logged in และ guest users
 */
add_action('wp_ajax_rmn_track_action', 'rmn_track_action');
add_action('wp_ajax_nopriv_rmn_track_action', 'rmn_track_action');
function rmn_track_action()
{
    $action = sanitize_text_field($_POST['track_action'] ?? '');
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $subject_type_input = sanitize_text_field($_POST['subject_type'] ?? '');
    $source = sanitize_text_field($_POST['source'] ?? '');

    // Validate required fields
    if (!$action || !$subject_id || !$subject_type_input) {
        wp_send_json_error(['message' => 'Missing required fields'], 422);
    }

    // Validate action type
    $allowed_actions = ['profile_view', 'click_call', 'click_contact', 'click_favorite', 'click_compare', 'click_share'];
    if (!in_array($action, $allowed_actions)) {
        wp_send_json_error(['message' => 'Invalid action type'], 422);
    }

    // Map simple type to full model name (รองรับทั้ง 'nursinghome' และ 'nursing_home')
    $type_map = [
        'nursing'      => 'App\\Models\\NursingProfile',
        'nursinghome'  => 'App\\Models\\NursingHomeProfile',
        'nursing_home' => 'App\\Models\\NursingHomeProfile',
    ];

    $subject_type = $type_map[$subject_type_input] ?? null;
    if (!$subject_type) {
        wp_send_json_error(['message' => 'Invalid subject type'], 422);
    }

    // ตรวจสอบ click limit สำหรับ click_call / click_contact
    // Bypass mode → ปลด limit (cascade ผ่าน canClickAction → isPaidMember → isBypassActive แล้ว แต่ explicit check ที่นี่กันเหนียว)
    if (in_array($action, AccessGuard::LIMITED_CLICK_ACTIONS, true)) {
        $guard = AccessGuard::getInstance();
        if (!$guard->isBypassActive() && !$guard->canClickAction($action)) {
            $actionLabel = $action === 'click_call' ? 'โทร' : 'ติดต่อ';
            wp_send_json_error([
                'message'       => "คุณใช้สิทธิ์{$actionLabel}ฟรีครบ 10 ครั้งต่อเดือนแล้ว กรุณาอัปเกรดแพ็กเกจ",
                'reason'        => 'limit_reached',
                'action'        => $action,
                'remaining'     => 0,
                'upgrade_url'   => $guard->isLogged() ? '/subscription' : '#',
                'require_login' => !$guard->isLogged(),
            ], 403);
        }
    }

    // Actions ที่ Laravel backend ยังไม่รองรับ → skip remote call, return success
    // (frontend handler ทำต่อ — favorite/compare/share เป็น UX action ไม่ต้อง persist log)
    $backend_supported_actions = ['profile_view', 'click_call', 'click_contact'];
    if (!in_array($action, $backend_supported_actions, true)) {
        wp_send_json_success(['skipped' => true, 'action' => $action]);
    }

    $token = !empty($_COOKIE['access_token']) ? sanitize_text_field($_COOKIE['access_token']) : null;

    $payload = [
        'action' => $action,
        'subject_id' => $subject_id,
        'subject_type' => $subject_type,
        'metadata' => ['source' => $source],
    ];

    // ใช้ track-view endpoint สำหรับ guest users
    if (!$token) {
        $endpoint = 'https://services.ratemynurse.org/api/analytics/track-view';
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    } else {
        $endpoint = 'https://services.ratemynurse.org/api/analytics/log';
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    $response = wp_remote_post($endpoint, [
        'body' => json_encode($payload),
        'headers' => $headers,
        'timeout' => 10,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $httpCode = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($httpCode >= 200 && $httpCode < 300) {
        // นับครั้ง click_call / click_contact หลัง API สำเร็จ
        if (in_array($action, AccessGuard::LIMITED_CLICK_ACTIONS, true)) {
            $guard = AccessGuard::getInstance();
            $guard->incrementClickAction($action);
            $body['remaining'] = $guard->getRemainingClickActions($action);
        }
        wp_send_json_success($body);
    }

    wp_send_json_error($body, $httpCode);
}

/**
 * AJAX: Get overview stats by period (for Tab Control)
 * Updates both stats cards and prepares comparison data
 */
add_action('wp_ajax_rmn_get_overview_stats', 'rmn_get_overview_stats');
function rmn_get_overview_stats()
{
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $type = $_POST['range_type'] ?? 'day';
    $tz = new DateTimeZone('Asia/Bangkok');
    $now = new DateTime('now', $tz);

    // Calculate date ranges based on type
    switch ($type) {
        case 'day':
            // วันนี้ vs เมื่อวาน
            $currentStart = (clone $now)->setTime(0, 0, 0);
            $currentEnd = (clone $now)->setTime(23, 59, 59);
            $prevStart = (clone $now)->modify('-1 day')->setTime(0, 0, 0);
            $prevEnd = (clone $now)->modify('-1 day')->setTime(23, 59, 59);
            $compareText = 'เทียบกับเมื่อวาน';
            break;

        case 'week':
            // สัปดาห์นี้ vs สัปดาห์ที่แล้ว
            $currentStart = (clone $now)->modify('monday this week')->setTime(0, 0, 0);
            $currentEnd = (clone $now)->modify('sunday this week')->setTime(23, 59, 59);
            $prevStart = (clone $currentStart)->modify('-1 week');
            $prevEnd = (clone $currentEnd)->modify('-1 week');
            $compareText = 'เทียบกับสัปดาห์ที่แล้ว';
            break;

        case 'month':
            // เดือนนี้ vs เดือนที่แล้ว
            $currentStart = new DateTime($now->format('Y-m-01'), $tz);
            $currentEnd = (clone $currentStart)->modify('last day of this month')->setTime(23, 59, 59);
            $prevStart = (clone $currentStart)->modify('-1 month');
            $prevEnd = (clone $prevStart)->modify('last day of this month')->setTime(23, 59, 59);
            $compareText = 'เทียบกับเดือนที่แล้ว';
            break;

        case 'custom':
            // Custom date range
            if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
                wp_send_json_error('Custom range requires start_date and end_date');
            }
            $currentStart = new DateTime($_POST['start_date'], $tz);
            $currentEnd = new DateTime($_POST['end_date'], $tz);
            $days = $currentStart->diff($currentEnd)->days + 1;
            $prevEnd = (clone $currentStart)->modify('-1 day');
            $prevStart = (clone $prevEnd)->modify("-" . ($days - 1) . " days");
            $compareText = 'เทียบกับช่วงก่อนหน้า';
            break;

        default:
            wp_send_json_error('Invalid range type');
    }
    
    // Call Laravel API for stats
    $queryArgs = [
        'start_date' => $currentStart->format('Y-m-d'),
        'end_date' => $currentEnd->format('Y-m-d'),
    ];
    if (!empty($_POST['profile_id'])) {
        $queryArgs['profile_id'] = sanitize_text_field($_POST['profile_id']);
    }
    $endpoint = add_query_arg($queryArgs, 'https://services.ratemynurse.org/api/analytics/my-stats');

    $response = wp_remote_get($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ],
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Add compare text to response
    if (isset($body['data'])) {
        $body['compare_text'] = $compareText;
        $body['period'] = [
            'current' => [
                'start' => $currentStart->format('Y-m-d'),
                'end' => $currentEnd->format('Y-m-d'),
            ],
            'previous' => [
                'start' => $prevStart->format('Y-m-d'),
                'end' => $prevEnd->format('Y-m-d'),
            ],
        ];
    }

    wp_send_json_success($body);
}

add_action('wp_ajax_rmn_provider_accept', 'rmn_provider_accept');
function rmn_provider_accept()
{
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => '401 Unauthenticated'], 401);
    }

    if (!isset($_POST['contactID'])) {
        wp_send_json_error(['message' => 'Invalid input - contactID required'], 400);
    }

    $token     = sanitize_text_field($_COOKIE['access_token']);
    $contactId = sanitize_text_field($_POST['contactID']);

    /* -------------------------
     * ✅ CHECK QUOTA ผ่าน AccessGuard
     * (handle ทั้ง BASIC limit และ non-BASIC ให้อัตโนมัติ)
     * ------------------------- */
    $guard = AccessGuard::getInstance();

    // Debug log
    error_log('=== rmn_provider_accept called ===');
    error_log('canAcceptJob: ' . var_export($guard->canAcceptJob(), true));
    error_log('getCurrentPlan: ' . $guard->getCurrentPlan());
    error_log('isNursingUser: ' . var_export($guard->isNursingUser(), true));

    if (!$guard->canAcceptJob()) {
        wp_send_json_error([
            'message' => 'คุณใช้สิทธิ์รับงานครบ 3 ครั้งต่อเดือนแล้ว'
        ], 403);
    }

    try {
        $url = "https://services.ratemynurse.org/api/provider/contact/{$contactId}/accept";

        $response = wp_remote_post($url, [
            'body'    => json_encode(['contact_id' => $contactId]),
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()], 500);
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);
        $decoded     = json_decode($body, true);

        if ($status_code >= 200 && $status_code < 300) {
            /* -------------------------
             * ✅ INCREASE QUOTA (เฉพาะ BASIC เท่านั้น ตาม logic ใน AccessGuard)
             * ------------------------- */
            $guard->incrementAcceptJob();

            wp_send_json_success(['data' => $decoded]);
        }

        wp_send_json_error(
            $decoded ?? ['message' => 'Unknown error'],
            $status_code
        );

    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()], 500);
    }
}

add_action('wp_ajax_rmn_nursing_home_account_update', 'rmn_nursing_home_account_update');
function rmn_nursing_home_account_update()
{
    if (empty($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);

    $data = [
        'firstname' => sanitize_text_field($_POST['firstname'] ?? ''),
        'lastname'  => sanitize_text_field($_POST['lastname'] ?? ''),
        'email'     => sanitize_email($_POST['email'] ?? ''),
        'phone'     => sanitize_text_field($_POST['phone'] ?? ''),
    ];

    $response = wp_remote_post('https://services.ratemynurse.org/api/user/update', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ],
        'body'    => json_encode($data),
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200) {
        wp_send_json_error($body);
    }

    wp_send_json_success($body);
}

add_action('wp_ajax_rmn_member_account_update', 'rmn_member_account_update');
add_action('wp_ajax_nopriv_rmn_member_account_update', 'rmn_member_account_update');
function rmn_member_account_update()
{
    // ===== 1. ตรวจสอบ Token =====
    $token = null;
    if (isset($_COOKIE['access_token'])) {
        $token = sanitize_text_field($_COOKIE['access_token']);
    } else {
        wp_send_json([
            'success' => false,
            'message' => 'Unauthorized - กรุณาเข้าสู่ระบบ',
        ], 401);
    }

    // ===== 2. เตรียมข้อมูลสำหรับส่งไปยัง Laravel =====
    $payload = [
        'firstname' => sanitize_text_field($_POST['firstname'] ?? ''),
        'lastname'  => sanitize_text_field($_POST['lastname'] ?? ''),
        'phone'     => sanitize_text_field($_POST['phone'] ?? ''),
        'email'     => sanitize_email($_POST['email'] ?? ''),
        'cardid'    => sanitize_text_field($_POST['cardid'] ?? ''),
        'gender'    => sanitize_text_field($_POST['gender'] ?? ''),
        'address'   => sanitize_textarea_field($_POST['address'] ?? ''),
        'province_id' => sanitize_text_field($_POST['province_id'] ?? ''),
        'district_id' => sanitize_text_field($_POST['district_id'] ?? ''),
        'sub_district_id' => sanitize_text_field($_POST['sub_district_id'] ?? ''),
        'zipcode'   => sanitize_text_field($_POST['zipcode'] ?? ''),
        'facebook'  => esc_url_raw($_POST['facebook'] ?? ''),
        'lineid'    => sanitize_text_field($_POST['lineid'] ?? ''),
        'date_of_birth' => sanitize_text_field($_POST['date_of_birth'] ?? ''),
    ];

    // ===== 3. ตรวจสอบและเตรียมไฟล์รูปภาพ =====
    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $file = $_FILES['profile_image'];
        
        // ตรวจสอบประเภทไฟล์
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            wp_send_json([
                'success' => false,
                'message' => 'ประเภทไฟล์ไม่รองรับ (รองรับเฉพาะ jpg, png, webp)',
            ], 422);
        }

        // ตรวจสอบขนาดไฟล์ (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json([
                'success' => false,
                'message' => 'ขนาดไฟล์ต้องไม่เกิน 5MB',
            ], 422);
        }

        // สร้าง CURLFile object
        $payload['profile_image'] = new CURLFile(
            $file['tmp_name'],
            $file_type,
            $file['name']
        );
    }

    // ===== 4. ส่งข้อมูลไปยัง Laravel API ด้วย cURL =====
    $endpoint = "https://services.ratemynurse.org/api/member/profile/update";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $endpoint,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_POSTFIELDS => $payload,
    ]);

    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // ===== 5. ตรวจสอบ cURL Error =====
    if ($curl_error) {
        wp_send_json([
            'success' => false,
            'message' => 'การเชื่อมต่อมีปัญหา: ' . $curl_error,
        ], 500);
    }

    // ===== 6. Parse Response =====
    $body = json_decode($response, true);

    // ===== 7. ตรวจสอบ HTTP Status Code =====
    if ($status_code !== 200) {
        wp_send_json([
            'success' => false,
            'message' => $body['message'] ?? 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล',
            'errors'  => $body['errors'] ?? [],
        ], $status_code);
    }

    // ===== 8. สำเร็จ =====
    wp_send_json([
        'success' => true,
        'message' => 'อัปเดตข้อมูลเรียบร้อยแล้ว',
        'data'    => $body['data'] ?? [],
    ], 200);
}

add_action('wp_ajax_rmn_member_review_contact', 'rmn_member_review_contact');
add_action('wp_ajax_nopriv_rmn_member_review_contact', 'rmn_member_review_contact');
function rmn_member_review_contact()
{
    // ===== 1. ตรวจสอบ Token =====
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized - กรุณาเข้าสู่ระบบ'], 401);
    }
    $token = sanitize_text_field($_COOKIE['access_token']);

    // ===== 2. เตรียมข้อมูลสำหรับส่งไปยัง Laravel =====
    $contact_id  = intval($_POST['contact_id'] ?? 0);
    $user_id     = intval($_POST['user_id'] ?? 0);
    $role        = sanitize_text_field($_POST['role'] ?? '');
    $rateable_id = intval($_POST['rateable_id'] ?? 0);
    $description = sanitize_textarea_field($_POST['description'] ?? '');
    $ratings     = json_decode(stripslashes($_POST['ratings'] ?? '[]'), true);

    // Transform ratings [{type, rating}] → scores {TYPE: rating}
    $scores = [];
    if (is_array($ratings)) {
        foreach ($ratings as $rating) {
            $scores[$rating['type']] = intval($rating['rating']);
        }
    }

    // Determine rateable_type from role
    $rateable_type = $role === 'NURSING_HOME'
        ? 'App\\Models\\NursingHomeProfile'
        : 'App\\Models\\NursingProfile';

    // ===== 3. เตรียม multipart payload (รองรับไฟล์รูป) =====
    $postFields = [
        'contact_id'    => $contact_id,
        'user_id'       => $user_id,
        'user_type'     => $role,
        'rateable_id'   => $rateable_id,
        'rateable_type' => $rateable_type,
        'description'   => $description,
    ];

    // scores เป็น associative array → flatten เป็น scores[TYPE]=value
    foreach ($scores as $type => $value) {
        $postFields["scores[{$type}]"] = $value;
    }

    // แนบรูปจาก $_FILES['images'] (multiple files)
    if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
        foreach ($_FILES['images']['name'] as $i => $name) {
            if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $postFields["images[{$i}]"] = new CURLFile(
                    $_FILES['images']['tmp_name'][$i],
                    $_FILES['images']['type'][$i] ?? 'application/octet-stream',
                    $name
                );
            }
        }
    }

    // ===== 4. ส่งข้อมูลไปยัง Laravel API (multipart/form-data) =====
    $ch = curl_init('https://services.ratemynurse.org/api/member/contact/rate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        wp_send_json_error(['message' => $err ?: 'cURL error'], 500);
    }

    $decoded = json_decode($body, true);

    if ($code >= 400) {
        wp_send_json_error(['message' => $decoded['message'] ?? 'เกิดข้อผิดพลาด'], $code);
    }

    wp_send_json_success($decoded);
}

add_action('wp_ajax_job_interview', 'rmn_job_interview');
add_action('wp_ajax_nopriv_job_interview', 'rmn_job_interview');
function rmn_job_interview()
{
    // 1. ตรวจสอบ Token
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized - กรุณาเข้าสู่ระบบ'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        wp_send_json_error(['message' => 'Invalid input'], 400);
    }

    // 2. payload
    $payload = [
        'job_id'         => intval($input['job_id'] ?? 0),
        'message'        => sanitize_textarea_field($input['message'] ?? ''),
        'price'          => floatval($input['price'] ?? 0),
        'start_date'     => sanitize_text_field($input['start_date'] ?? ''),
        'attach_profile' => intval($input['attach_profile'] ?? 0),
    ];

    // 3. sending request
    $response = wp_remote_post('https://services.ratemynurse.org/api/job-nursing/apply', [
        'body'    => json_encode($payload),
        'headers' => [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$token}",
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $body = wp_remote_retrieve_body($response);
    $decoded = json_decode($body, true);
    $code = wp_remote_retrieve_response_code($response);

    if ($code >= 400) {
        wp_send_json_error(['message' => $decoded['message'] ?? 'เกิดข้อผิดพลาด'], $code);
    }

    // ลบ cache ของ job detail และ job interview เมื่อมีการนำเสนองาน
    $job_id = intval($input['job_id'] ?? 0);
    if ($job_id) {
        delete_transient('job_data_' . $job_id);
        delete_transient('job_interview_' . $job_id);
    }

    wp_send_json_success($decoded);
}

add_action('wp_ajax_rmn_job_cancel', 'rmn_job_cancel');
function rmn_job_cancel()
{
    // 1. ตรวจสอบ Token
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized - กรุณาเข้าสู่ระบบ'], 401);
    }

    $token        = sanitize_text_field($_COOKIE['access_token']);
    $interview_id = intval($_POST['id'] ?? 0);
    $job_id       = intval($_POST['job_id'] ?? 0);

    if (!$interview_id) {
        wp_send_json_error(['message' => 'ไม่พบรหัสรายการ'], 400);
    }

    // 2. ส่ง DELETE ไปยัง Laravel API
    $response = wp_remote_request("https://services.ratemynurse.org/api/member/job-interview/{$interview_id}", [
        'method'  => 'DELETE',
        'headers' => [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$token}",
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }

    $body    = wp_remote_retrieve_body($response);
    $decoded = json_decode($body, true);
    $code    = wp_remote_retrieve_response_code($response);

    if ($code >= 400) {
        wp_send_json_error(['message' => $decoded['message'] ?? 'เกิดข้อผิดพลาด'], $code);
    }

    // ลบ cache ของ job detail และ job interview
    if ($job_id) {
        delete_transient('job_data_' . $job_id);
        delete_transient('job_interview_' . $job_id);
    }

    wp_send_json_success($decoded);
}

add_action('wp_ajax_updateNursingHomeProfile', 'updateNursingHomeProfile');
add_action('wp_ajax_nopriv_updateNursingHomeProfile', 'updateNursingHomeProfile');
function updateNursingHomeProfile()
{
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized - กรุณาเข้าสู่ระบบ'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $guard = AccessGuard::getInstance();
    $user  = $guard->me();

    if (!$user) {
        wp_send_json_error(['message' => 'เกิดข้อผิดพลาด'], 500);
    }

    $step       = sanitize_text_field($_POST['step']);
    $profile_id = sanitize_text_field($_POST['profile']);
    $endpoint   = '';
    $payload    = [];
    $has_files  = false;

    $payload['user_id']    = sanitize_text_field($user['data']['id']);
    $payload['profile_id'] = sanitize_text_field($profile_id);

    // -------------------------------------------------------
    if ($step == 'general') {
        $payload['name']             = sanitize_text_field($_POST['name']);
        $payload['main_phone']       = sanitize_text_field($_POST['main_phone'] ?? '');
        $payload['res_phone']        = sanitize_text_field($_POST['res_phone']);
        $payload['email']            = sanitize_text_field($_POST['email']);
        $payload['facebook']         = sanitize_text_field($_POST['facebook']);
        $payload['website']          = sanitize_text_field($_POST['website']);
        $payload['address']          = sanitize_text_field($_POST['address']);
        $payload['province_id']      = sanitize_text_field($_POST['province_id']);
        $payload['district_id']      = sanitize_text_field($_POST['district_id']);
        $payload['sub_district_id']  = sanitize_text_field($_POST['sub_district_id']);
        $payload['zipcode']          = sanitize_text_field($_POST['zipcode']);
        $payload['map_show']         = sanitize_text_field($_POST['show_map'] == 'on' ? 1 : 0);
        $endpoint = 'https://services.ratemynurse.org/api/nursing-home/profile/general';

    // -------------------------------------------------------
    } elseif ($step == 'about') {
        $payload['license_no']               = sanitize_text_field($_POST['license_no']);
        $payload['license_start_date']       = sanitize_text_field($_POST['license_start_date']);
        $payload['license_exp_date']         = sanitize_text_field($_POST['license_exp_date']);
        $payload['license_by']               = sanitize_text_field($_POST['license_by']);
        $payload['certificates']             = sanitize_text_field($_POST['certificates'] ?? '');
        $payload['hospital_no']              = sanitize_text_field($_POST['hospital_no'] ?? '');
        $payload['cost_per_day']             = sanitize_text_field($_POST['cost_per_day'] ?? 0);
        $payload['cost_per_month']           = sanitize_text_field($_POST['cost_per_month'] ?? 0);
        $payload['deposit']                  = sanitize_text_field($_POST['deposit'] ?? 0);
        $payload['registration_fee']         = sanitize_text_field($_POST['registration_fee'] ?? 0);
        $payload['special_food_expenses']    = sanitize_text_field($_POST['special_food_expenses'] ?? 0);
        $payload['physical_therapy_fee']     = sanitize_text_field($_POST['physical_therapy_fee'] ?? 0);
        $payload['delivery_fee']             = sanitize_text_field($_POST['delivery_fee'] ?? 0);
        $payload['laundry_service']          = sanitize_text_field($_POST['laundry_service'] ?? 0);
        $payload['social_security']          = sanitize_text_field($_POST['social_security'] ?? 0);
        $payload['private_health_insurance'] = sanitize_text_field($_POST['private_health_insurance'] ?? 0);
        $payload['installment']              = sanitize_text_field($_POST['installment'] ?? 0);
        $payload['payment_methods']          = sanitize_text_field($_POST['payment_methods'] ?? null);

        // Staff data (name, position, existing_image) — flatten nested array for multipart
        if (!empty($_POST['staffs']) && is_array($_POST['staffs'])) {
            foreach ($_POST['staffs'] as $i => $staffItem) {
                $payload["staffs[{$i}][name]"] = sanitize_text_field($staffItem['name'] ?? '');
                $payload["staffs[{$i}][position]"] = sanitize_text_field($staffItem['position'] ?? '');
                if (!empty($staffItem['existing_image'])) {
                    $payload["staffs[{$i}][existing_image]"] = sanitize_text_field($staffItem['existing_image']);
                }
            }
        }

        $endpoint  = 'https://services.ratemynurse.org/api/nursing-home/profile/about';
        $has_files = true; // step นี้มีไฟล์เสมอ (license_images, staffs)

    // -------------------------------------------------------
    } elseif ($step == 'moreinfo') {
        $payload['about']                   = sanitize_text_field($_POST['about'] ?? '');
        $payload['youtube_url']             = sanitize_text_field($_POST['youtube_url'] ?? '');
        $payload['home_service_type']       = $_POST['home_service_type'] ?? [];
        $payload['etc_services']            = sanitize_text_field($_POST['etc_services'] ?? '');
        $payload['additional_service_type'] = $_POST['additional_service_type'] ?? [];
        $payload['center_highlights']       = $_POST['center_highlights'] ?? [];
        $payload['building_no']             = sanitize_text_field($_POST['building_no'] ?? 0);
        $payload['total_room']              = sanitize_text_field($_POST['total_room'] ?? 0);
        $payload['private_room_no']         = sanitize_text_field($_POST['private_room_no'] ?? 0);
        $payload['duo_room_no']             = sanitize_text_field($_POST['duo_room_no'] ?? 0);
        $payload['shared_room_three_beds']  = sanitize_text_field($_POST['shared_room_three_beds'] ?? 0);
        $payload['max_serve_no']            = sanitize_text_field($_POST['max_serve_no'] ?? 0);
        $payload['area']                    = sanitize_text_field($_POST['area'] ?? 0);
        $payload['facilities']              = $_POST['facilities'] ?? [];
        $payload['special_facilities']      = $_POST['special_facilities'] ?? [];
        $payload['ambulance']               = sanitize_text_field($_POST['ambulance'] ?? 0);
        $payload['ambulance_amount']        = sanitize_text_field($_POST['ambulance_amount'] ?? 0);
        $payload['van_shuttle']             = sanitize_text_field($_POST['van_shuttle'] ?? 0);
        $payload['special_medical_equipment'] = sanitize_text_field($_POST['special_medical_equipment'] ?? '');
        if (!empty($_POST['image_order'])) {
            $payload['image_order'] = $_POST['image_order'];
        }
        $endpoint  = 'https://services.ratemynurse.org/api/nursing-home/profile/moreinfo';
        // detail_images อาจมีหรือไม่มีก็ได้ ตรวจด้านล่าง — also send multipart if image_order present
        $has_files = !empty($_FILES['detail_images']['name'][0]) || !empty($_POST['image_order']);
    }

    if (!$endpoint) {
        wp_send_json_error(['message' => 'ไม่พบ endpoint'], 400);
    }

    // -------------------------------------------------------
    // ส่ง Request
    // -------------------------------------------------------
    if ($has_files) {
        $result = sendMultipartRequest($endpoint, $token, $payload, $step);
    } else {
        $result = sendJsonRequest($endpoint, $token, $payload);
    }

    if (isset($result['error'])) {
        wp_send_json_error(['message' => $result['error']], $result['code'] ?? 500);
    }

    wp_send_json_success($result['data']);
}

// -------------------------------------------------------
// Helper: ส่งแบบ multipart/form-data (มีไฟล์) ผ่าน cURL
// -------------------------------------------------------
function sendMultipartRequest(string $endpoint, string $token, array $payload, string $step): array
{
    
    $allowed_img_types = ['image/jpeg', 'image/png', 'image/webp'];
    $multipart = [];

    // แปลง payload ปกติ (รวม nested array)
    foreach ($payload as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $i => $v) {
                $multipart["{$key}[{$i}]"] = $v;
            }
        } else {
            $multipart[$key] = $value;
        }
    }

    // ---- แนบไฟล์ตาม step ----
    if ($step === 'about') {

        // license_images_upload[]
        if (!empty($_FILES['license_images_upload']['name'][0])) {
            foreach ($_FILES['license_images_upload']['name'] as $i => $name) {
                if ($_FILES['license_images_upload']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $multipart["license_images_upload[{$i}]"] = new CURLFile(
                    $_FILES['license_images_upload']['tmp_name'][$i],
                    $_FILES['license_images_upload']['type'][$i],
                    sanitize_file_name($name)
                );
            }
        }

        // staffs[N][avatar]
        if (!empty($_FILES['staffs']['name'])) {
            foreach ($_FILES['staffs']['name'] as $index => $staffData) {
                if (!empty($staffData['avatar']) && $_FILES['staffs']['error'][$index]['avatar'] === UPLOAD_ERR_OK) {
                    $multipart["staffs[{$index}][avatar]"] = new CURLFile(
                        $_FILES['staffs']['tmp_name'][$index]['avatar'],
                        $_FILES['staffs']['type'][$index]['avatar'],
                        sanitize_file_name($staffData['avatar'])
                    );
                }
            }
        }

    } elseif ($step === 'moreinfo') {

        // detail_images[] — รูปแรกคือ cover
        if (!empty($_FILES['detail_images']['name'][0])) {
            foreach ($_FILES['detail_images']['name'] as $i => $name) {
                if ($_FILES['detail_images']['error'][$i] !== UPLOAD_ERR_OK) continue;

                $tmp  = $_FILES['detail_images']['tmp_name'][$i];
                $mime = mime_content_type($tmp);

                if (!in_array($mime, $allowed_img_types)) continue;
                if ($_FILES['detail_images']['size'][$i] > 5 * 1024 * 1024) continue;

                $multipart["detail_images[{$i}]"] = new CURLFile(
                    $tmp,
                    $mime,
                    sanitize_file_name($name)
                );
            }
        }
    }

    error_log('$_FILES: ' . print_r($_FILES, true));
    error_log('multipart keys: ' . print_r(array_keys($multipart), true));

    // ---- cURL ----
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $multipart,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            "Authorization: Bearer {$token}",
            // ไม่ใส่ Content-Type — cURL จัดการ boundary เองอัตโนมัติ
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $raw      = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        return ['error' => $curl_err, 'code' => 500];
    }

    $decoded = json_decode($raw, true);
    // print_r($raw); // DEBUG: ลบออก — ทำให้ response JSON ซ้อนกัน 2 ชั้น

    if ($http_code >= 400) {
        return ['error' => $decoded['message'] ?? 'เกิดข้อผิดพลาด', 'code' => $http_code];
    }

    return ['data' => $decoded];
}

// -------------------------------------------------------
// Helper: ส่งแบบปกติผ่าน wp_remote_post (ไม่มีไฟล์)
// -------------------------------------------------------
function sendJsonRequest(string $endpoint, string $token, array $payload): array
{
    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$token}",
        ],
        'body'    => $payload,
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message(), 'code' => 500];
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $decoded   = json_decode(wp_remote_retrieve_body($response), true);

    if ($http_code >= 400) {
        return ['error' => $decoded['message'] ?? 'เกิดข้อผิดพลาด', 'code' => $http_code];
    }

    return ['data' => $decoded];
}

add_action('wp_ajax_closejob', 'rmn_closeJob');
add_action('wp_ajax_nopriv_closejob', 'rmn_closeJob');
function rmn_closeJob()
{
    if (!isset($_COOKIE['access_token'])) {
        wp_send_json_error(['message' => 'Unauthorized - กรุณาเข้าสู่ระบบ'], 401);
    }

    $token = sanitize_text_field($_COOKIE['access_token']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if (!$id) {
        wp_send_json_error(['message' => 'ไม่พบรหัสประกาศ']);
    }

    $endpoint = "https://services.ratemynurse.org/api/job/{$id}/close";

    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$token}",
        ],
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้']);
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $decoded   = json_decode(wp_remote_retrieve_body($response), true);

    if ($http_code >= 400) {
        wp_send_json_error(['message' => $decoded['message'] ?? 'เกิดข้อผิดพลาด']);
    }

    // ล้าง cache ของ job นี้แล้วดึงข้อมูลใหม่ทันที
    delete_transient('job_data_' . $id);

    // สร้าง cache ใหม่ทันที
    $fresh = wp_remote_get("https://services.ratemynurse.org/api/job/{$id}", [
        'headers' => [
            'Accept'        => 'application/json',
            'Authorization' => "Bearer {$token}",
        ],
        'timeout' => 15,
    ]);

    if (!is_wp_error($fresh) && wp_remote_retrieve_response_code($fresh) === 200) {
        $fresh_data = json_decode(wp_remote_retrieve_body($fresh), true);
        if ($fresh_data) {
            set_transient('job_data_' . $id, $fresh_data, 10 * MINUTE_IN_SECONDS);
        }
    }

    wp_send_json_success(['message' => 'ปิดรับประกาศเรียบร้อยแล้ว']);
}

add_action('wp_ajax_updateNursingHomeAboutProfile', 'updateNursingHomeAboutProfile');
add_action('wp_ajax_nopriv_updateNursingHomeAboutProfile', 'updateNursingHomeAboutProfile');
function updateNursingHomeAboutProfile()
{

}

// add_action('wp_ajax_rmn_debug_quota', function() {
//     $guard = AccessGuard::getInstance();
//     $me    = $guard->me();
//     $month = date('Y-m');
//     $key   = "rmn_accept_job_{$me['data']['id']}_{$month}";

//     wp_send_json([
//         'key'          => $key,
//         'count'        => get_transient($key),   // ตอนนี้กดไปแล้วกี่ครั้ง
//         'canAcceptJob' => $guard->canAcceptJob(), // รับงานได้อีกไหม
//         'plan'         => $guard->getCurrentPlan(),
//     ]);
// });

// add_action('wp_ajax_rmn_reset_quota', function() {
//     $key = 'rmn_accept_job_578_2026-02';
//     delete_transient($key);
//     wp_send_json_success(['deleted' => $key, 'verify' => get_transient($key)]);
// });