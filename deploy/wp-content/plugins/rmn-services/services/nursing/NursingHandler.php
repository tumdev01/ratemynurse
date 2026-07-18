<?php

class NursingHandler {
    public function __construct() {
        add_shortcode( 'nursings-carousel', [$this, 'nursingCarousel'] );
        add_shortcode( 'nursings-specific', [$this, 'nursingSpecific'] );
        add_shortcode( 'nursings-grid', [$this, 'nursingGrid'] );
    }

    public function enqueueScripts() {
        // Swiper CSS
        if (!wp_style_is('swiper-style', 'enqueued')) {
            wp_enqueue_style(
                'swiper-style',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                [],
                null
            );
        }


        // Swiper JS
        if (!wp_script_is('swiper-script', 'enqueued')) {
            wp_enqueue_script(
                'swiper-script',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                [],
                null,
                true
            );
        }

        // Your custom frontend logic
        wp_enqueue_script(
            'nursing-frontend',
            plugin_dir_url(__FILE__) . '/nursing-frontend.js',
            ['axios', 'swiper-script'],
            null,
            true
        );

        // Pass API URL to JS
        wp_localize_script('nursing-frontend', 'nursingApiData', [
            'apiUrl' => 'https://your-laravel-app.com/api/nursings'
        ]);
    }
    
    public function nursingCarousel( $atts ) {
        $this->enqueueScripts();
        $args = shortcode_atts( array(
            'certified' => false,
            'limit' => 5,
            'order_by' => 'id',
            'order' => 'ASC'
        ), $atts );

        // Server-side fetch + cache (TTL 2 นาที) — เลี่ยง 2-วินาที API call บน critical path
        $cache_key = 'rmn_nursings_' . md5(json_encode([
            'limit'     => $args['limit'],
            'certified' => $args['certified'],
        ]));
        $cached_data = get_transient($cache_key);
        if ($cached_data === false) {
            $response = wp_remote_post('https://services.ratemynurse.org/api/nursings', [
                'body'    => json_encode([
                    'limit'     => (int) $args['limit'],
                    'certified' => $args['certified'],
                ]),
                'headers' => [
                    'Accept'           => 'application/json',
                    'Content-Type'     => 'application/json',
                    'X-internal-Token' => '9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
                ],
                'timeout' => 10,
            ]);
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $cached_data = json_decode(wp_remote_retrieve_body($response), true);
                set_transient($cache_key, $cached_data, 2 * MINUTE_IN_SECONDS);
            }
        }
        if (is_array($cached_data)) {
            wp_localize_script('nursing-frontend', 'RMN_NURSINGS', $cached_data);
        }

        return '<div class="swiper-wrapper-container" style="position:relative;"><div class="swiper-container" id="nursing-list" data-certified="' . esc_attr($args['certified']) . '" data-limit="' . esc_attr($args['limit']) . '">กำลังโหลดข้อมูล...</div><div class="nursing-swiper-button-next swiper-button-next"></div><div class="swiper-button-prev nursing-swiper-button-prev"></div></div>';
    }

    // เหมือน nursingCarousel() แต่ยิงไป endpoint ใหม่ /api/nursings/by-ids โดยเฉพาะ มิเรอร์จาก
    // NursingHomeHandler::fetchAndCacheSpecificNursingHomes()
    private function fetchAndCacheSpecificNursings($ids) {
        if (empty($ids)) {
            return;
        }

        $cache_key = 'rmn_nursings_by_ids_' . md5(json_encode($ids));
        $cached_data = get_transient($cache_key);
        if ($cached_data === false) {
            $response = wp_remote_post('https://services.ratemynurse.org/api/nursings/by-ids', [
                'body'    => json_encode(['ids' => $ids]),
                'headers' => [
                    'Accept'           => 'application/json',
                    'Content-Type'     => 'application/json',
                    'X-internal-Token' => '9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789',
                ],
                'timeout' => 10,
            ]);
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $cached_data = json_decode(wp_remote_retrieve_body($response), true);
                set_transient($cache_key, $cached_data, 2 * MINUTE_IN_SECONDS);
            }
        }
        if (is_array($cached_data)) {
            wp_localize_script('nursing-frontend', 'RMN_NURSINGS', $cached_data);
        }
    }

    // ใช้งาน [nursings-specific ids="9,620,124"] ดึงเฉพาะพยาบาลตาม ids ที่ระบุ เรียงตามลำดับใน
    // ids array เหมือน nursingCarousel ทุกประการ มิเรอร์จาก NursingHomeHandler::nursingHomeSpecific()
    public function nursingSpecific($atts) {
        $this->enqueueScripts();
        $args = shortcode_atts( array(
            'ids' => '',
        ), $atts );

        // "9,620,124" -> [9, 620, 124] เรียงตามลำดับที่ระบุ (0/ค่าว่างถูกกรองทิ้ง)
        $ids = array_values(array_filter(array_map('intval', explode(',', $args['ids']))));

        $this->fetchAndCacheSpecificNursings($ids);

        return '<div class="swiper-wrapper-container" style="position:relative;"><div class="swiper-container" id="nursing-list" data-limit="' . esc_attr(count($ids)) . '" data-certified="0" data-ids="' . esc_attr(implode(',', $ids)) . '">กำลังโหลดข้อมูล...</div><div class="nursing-swiper-button-next swiper-button-next"></div><div class="swiper-button-prev nursing-swiper-button-prev"></div></div>';
    }

    public function nursingGrid( $atts )
    {
        wp_enqueue_script(
            'nursing-grid-frontend',
            plugin_dir_url(__FILE__) . '/nursing-grid-frontend.js',
            ['axios', 'swiper-script'],
            null,
            true
        );
        $args = shortcode_atts( array(
            'per_page' => 8,
            'order_by' => 'id',
            'order' => 'ASC'
        ), $atts );

        $province = $_GET['province'] ?? null;
        $zone     = $_GET['zone'] ?? null;

        $sortingHTML = '
            <div></div>
            <div class="flex flex-between align-center">
                <div id="total_items">ค้นพบ <span id="total_amount">0</span> ประกาศ</div>
                <div class="flex flex-row align-center text-[#8C8A94] text-[16px]">
                    <span>เรียงจาก:</span>
                    <select id="sortby">
                        <option value="date:DESC">ใหม่ล่าสุด</option>
                        <option value="date:ASC">เก่าไปหาใหม่</option>
                    </select>
                    <div class="layout_swt hidden md:flex">
                        <a href="#" class="view_sw view_grid active" data-view="grid">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z"/> </svg>
                        </a>
                        <a href="#" class="view_sw view_list" data-view="list">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5"/> </svg>
                        </a>
                    </div>
                </div>
            </div>
        ';
        $html = '<div class="flex gap-[40px] flex-column">' . $sortingHTML . '<div id="nursing_grid"><div id="nursing_grid_results" class="grid" data-province="'.$province.'" data-zone="'.$zone.'" data-view="grid" data-order="DESC" data-orderby="created_at" data-perpage="'.esc_attr($args['per_page']).'">กำลังโหลดข้อมูล</div></div></div>';

        return $html;
    }
}

new NursingHandler();
