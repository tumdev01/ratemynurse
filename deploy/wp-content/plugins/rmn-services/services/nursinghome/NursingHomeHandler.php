<?php

class NursingHomeHandler {
    public function __construct() {
        add_shortcode( 'nursing-homes-carousel', [$this, 'nursingHomeCarousel'] );
        add_shortcode( 'nursing-homes-specific', [$this, 'nursingHomeSpecific'] );
        add_shortcode( 'nursing-homes-certified-carousel', [$this, 'nursingHomeCertifiedCarousel']);
        add_shortcode( 'nursing-homes-grid', [$this, 'nursingHomeGrid'] );
        add_shortcode( 'nursing-homes-info', [$this, 'nusingHomeInfo'] );
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
            'nursinghome-frontend',
            plugin_dir_url(__FILE__) . 'nursinghome-frontend.js',
            ['axios', 'swiper-script'],
            null,
            true
        );

        // Star rating CSS — ใช้ใน profile-rate ของ carousel (CSS pseudo-element + --rating-percent)
        wp_add_inline_style('swiper-style', '
            .star-rating { display: inline-block; font-size: 1.5rem; position: relative; color: #ccc; white-space: nowrap; }
            .star-rating::before { content: "★★★★★"; }
            .star-rating::after { content: "★★★★★"; position: absolute; top: 0; left: 0; width: var(--rating-percent); overflow: hidden; color: gold; white-space: nowrap; }
        ');
    }
    
    /**
     * Server-side fetch + cache nursing home list (TTL 2 นาที)
     * เลี่ยง 2-วินาที API call บน critical path
     */
    private function fetchAndCacheNursingHomes($args) {
        $cache_key = 'rmn_nursing_homes_' . md5(json_encode([
            'limit'     => $args['limit'],
            'certified' => $args['certified'],
        ]));
        $cached_data = get_transient($cache_key);
        if ($cached_data === false) {
            $response = wp_remote_post('https://services.ratemynurse.org/api/nursing-homes', [
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
            wp_localize_script('nursinghome-frontend', 'RMN_NURSING_HOMES', $cached_data);
        }
    }

    /**
     * เหมือน fetchAndCacheNursingHomes() แต่ยิงไป endpoint ใหม่ /api/nursing-homes/by-ids
     * โดยเฉพาะ (เบากว่า ไม่ดึง field ที่ไม่ได้ใช้) แยกจาก endpoint เดิมเพื่อกัน carousel เดิมพัง
     */
    private function fetchAndCacheSpecificNursingHomes($ids) {
        if (empty($ids)) {
            return;
        }

        $cache_key = 'rmn_nursing_homes_by_ids_' . md5(json_encode($ids));
        $cached_data = get_transient($cache_key);
        if ($cached_data === false) {
            $response = wp_remote_post('https://services.ratemynurse.org/api/nursing-homes/by-ids', [
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
            wp_localize_script('nursinghome-frontend', 'RMN_NURSING_HOMES', $cached_data);
        }
    }

    public function nursingHomeCarousel( $atts ) {
        $this->enqueueScripts();
        $args = shortcode_atts( array(
            'limit' => 5,
            'order_by' => 'id',
            'order' => 'ASC',
            'certified' => 0
        ), $atts );
        $this->fetchAndCacheNursingHomes($args);

        return '<div class="swiper-wrapper-container" style="position:relative;"><div class="swiper-container" id="nursinghome-list" data-limit="' . esc_attr($args['limit']) . '" data-certified="' . esc_attr($args['certified']) . '">กำลังโหลดข้อมูล...</div><div class="swiper-button-next nursinghome-swiper-button-next"></div><div class="swiper-button-prev nursinghome-swiper-button-prev"></div></div>';
    }

    public function nursingHomeCertifiedCarousel( $atts )
    {
        $this->enqueueScripts();
        $args = shortcode_atts( array(
            'certified' => 1,
            'limit' => 5,
            'order_by' => 'id',
            'order' => 'ASC'
        ), $atts );
        $this->fetchAndCacheNursingHomes($args);
        return '<div class="swiper-wrapper-container" style="position:relative;"><div class="swiper-container" id="nursinghome-list" data-limit="' . esc_attr($args['limit']) . '" data-certified="' . esc_attr($args['certified']) . '">กำลังโหลดข้อมูล...</div><div class="swiper-button-next nursinghome-swiper-button-next"></div><div class="swiper-button-prev nursinghome-swiper-button-prev"></div></div>';
    }

    public function nursingHomeGrid( $atts )
    {
        $province = $_GET['province'] ?? '';
        $zone     = $_GET['zone'] ?? '';
        wp_enqueue_script(
            'nursing-homes-grid-frontend',
            plugin_dir_url(__FILE__) . '/nursing-homes-grid-frontend.js',
            ['axios'],
            null,
            true
        );

        $args = shortcode_atts( array(
            'per_page' => 5,
            'order_by' => 'id',
            'order' => 'ASC',
            'certified' => 0,
            'province' => $province,
            'zone' => $zone
        ), $atts );

        $html_filters = <<<HTML
            <form>
                <input type="hidden" name="type" value="NURSING_HOME">
                <div id="home_filters" class="flex flex-wrap gap-3 items-center">

                    <!-- Type -->
                    <div class="filter_group has-selected" data-selected="NURSING_HOME">
                        <span class="filter_label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>บ้านพักผู้สูงอายุ</span>
                            <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                    </div>

                    <!-- Province -->
                    <div class="filter_group" id="province_filter">
                        <span class="filter_label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>จังหวัด</span>
                            <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                        <div class="options">
                            <div class="province-search-wrap">
                                <input type="text" id="province_search" placeholder="ค้นหาจังหวัด..." autocomplete="off">
                            </div>
                            <div class="options-scroll" id="province_list">
                                <div class="province-loading">กำลังโหลด...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Facilities -->
                    <div class="filter_group">
                        <span class="filter_label">
                            <span>สิ่งอำนวยความสะดวก</span>
                            <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                        <div class="options">
                            <div class="options-scroll">
                                <label class="filter-check"><input type="checkbox" value="NURSE_STATION" name="facilities[]" data-value="NURSE_STATION"><span>ห้องพยาบาล/สถานีพยาบาล</span></label>
                                <label class="filter-check"><input type="checkbox" value="EMERGENCY_ROOM" name="facilities[]" data-value="EMERGENCY_ROOM"><span>ห้องฉุกเฉิน</span></label>
                                <label class="filter-check"><input type="checkbox" value="EXAMINATION_ROOM" name="facilities[]" data-value="EXAMINATION_ROOM"><span>ห้องตรวจ</span></label>
                                <label class="filter-check"><input type="checkbox" value="MEDICINE_ROOM" name="facilities[]" data-value="MEDICINE_ROOM"><span>ห้องยา</span></label>
                                <label class="filter-check"><input type="checkbox" value="KITCHEN_CAFETERIA" name="facilities[]" data-value="KITCHEN_CAFETERIA"><span>ห้องครัว/โรงอาหาร</span></label>
                                <label class="filter-check"><input type="checkbox" value="DINING_ROOM" name="facilities[]" data-value="DINING_ROOM"><span>ห้องรับประทานอาหาร</span></label>
                                <label class="filter-check"><input type="checkbox" value="ACTIVITY_ROOM" name="facilities[]" data-value="ACTIVITY_ROOM"><span>ห้องกิจกรรม</span></label>
                                <label class="filter-check"><input type="checkbox" value="PHYSICAL_THERAPY_ROOM" name="facilities[]" data-value="PHYSICAL_THERAPY_ROOM"><span>ห้องกายภาพบำบัด</span></label>
                                <label class="filter-check"><input type="checkbox" value="MEETING_ROOM" name="facilities[]" data-value="MEETING_ROOM"><span>ห้องประชุม</span></label>
                                <label class="filter-check"><input type="checkbox" value="OFFICE_ROOM" name="facilities[]" data-value="OFFICE_ROOM"><span>ห้องออฟฟิศ</span></label>
                                <label class="filter-check"><input type="checkbox" value="LAUNDRY_ROOM" name="facilities[]" data-value="LAUNDRY_ROOM"><span>ห้องซักรีด</span></label>
                                <label class="filter-check"><input type="checkbox" value="ELEVATOR" name="facilities[]" data-value="ELEVATOR"><span>ลิฟต์</span></label>
                                <label class="filter-check"><input type="checkbox" value="WHEELCHAIR_RAMP" name="facilities[]" data-value="WHEELCHAIR_RAMP"><span>ทางลาดสำหรับรถเข็น</span></label>
                                <label class="filter-check"><input type="checkbox" value="BATHROOM_GRAB_BAR" name="facilities[]" data-value="BATHROOM_GRAB_BAR"><span>ราวจับในห้องน้ำ</span></label>
                                <label class="filter-check"><input type="checkbox" value="EMERGENCY_BELL" name="facilities[]" data-value="EMERGENCY_BELL"><span>กระดิ่งฉุกเฉิน</span></label>
                                <label class="filter-check"><input type="checkbox" value="CAMERA" name="facilities[]" data-value="CAMERA"><span>กล้องวงจรปิด</span></label>
                                <label class="filter-check"><input type="checkbox" value="FIRE_SYSTEM" name="facilities[]" data-value="FIRE_SYSTEM"><span>ระบบดับเพลิง</span></label>
                                <label class="filter-check"><input type="checkbox" value="BACKUP_GENERATOR" name="facilities[]" data-value="BACKUP_GENERATOR"><span>เครื่องปั่นไฟสำรอง</span></label>
                                <label class="filter-check"><input type="checkbox" value="AIR_CONDITIONER" name="facilities[]" data-value="AIR_CONDITIONER"><span>เครื่องปรับอากาศ</span></label>
                                <label class="filter-check"><input type="checkbox" value="GARDEN_AREA" name="facilities[]" data-value="GARDEN_AREA"><span>สวนหย่อม/พื้นที่นันทนาการ</span></label>
                                <label class="filter-check"><input type="checkbox" value="WIFI_INTERNET" name="facilities[]" data-value="WIFI_INTERNET"><span>WiFi / อินเทอร์เน็ต</span></label>
                                <label class="filter-check"><input type="checkbox" value="CENTRAL_TELEVISION" name="facilities[]" data-value="CENTRAL_TELEVISION"><span>โทรทัศน์ส่วนกลาง</span></label>
                            </div>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="filter_group" id="price_filter" data-selected="">
                        <span class="filter_label">
                            <span>ราคา</span>
                            <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                        <div class="options">
                            <div class="price-slider-wrap">
                                <div class="price-slider-values">
                                    <span id="price_min_display">฿0</span>
                                    <span id="price_max_display">฿30,000</span>
                                </div>
                                <div class="price-slider-track">
                                    <div class="price-slider-range" id="price_range_fill"></div>
                                    <input type="range" id="price_min" min="0" max="30000" step="1000" value="0">
                                    <input type="range" id="price_max" min="0" max="30000" step="1000" value="30000">
                                </div>
                            </div>
                            <div class="price-divider"></div>
                            <label class="filter-check"><input type="checkbox" value="0-7000" data-value="0-7000"><span>0 - 7,000 / เดือน</span></label>
                            <label class="filter-check"><input type="checkbox" value="7001-10000" data-value="7001-10000"><span>7,001 - 10,000 / เดือน</span></label>
                            <label class="filter-check"><input type="checkbox" value="10001-13000" data-value="10001-13000"><span>10,001 - 13,000 / เดือน</span></label>
                            <label class="filter-check"><input type="checkbox" value="13001-16000" data-value="13001-16000"><span>13,001 - 16,000 / เดือน</span></label>
                            <label class="filter-check"><input type="checkbox" value="16001" data-value="16001"><span>16,001 ขึ้นไป / เดือน</span></label>
                        </div>
                    </div>

                    <!-- Room Type -->
                    <div class="filter_group" data-selected="">
                        <span class="filter_label">
                            <span>ห้องพัก</span>
                            <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                        <div class="options">
                            <a href="#" class="filter-option" data-value="SINGLE_BED">เตียงเดี่ยว</a>
                            <a href="#" class="filter-option" data-value="DOUBLE_BED">เตียงคู่</a>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="filter_group" data-selected="">
                        <span class="filter_label">
                            <span>คะแนน</span>
                            <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </span>
                        <div class="options options-rating">
                            <a href="#" class="rating-option" data-value="1"><span class="rating-star">&#9733;</span> 1+</a>
                            <a href="#" class="rating-option" data-value="2"><span class="rating-star">&#9733;</span> 2+</a>
                            <a href="#" class="rating-option" data-value="3"><span class="rating-star">&#9733;</span> 3+</a>
                            <a href="#" class="rating-option" data-value="4"><span class="rating-star">&#9733;</span> 4+</a>
                            <a href="#" class="rating-option" data-value="5"><span class="rating-star">&#9733;</span> 5</a>
                        </div>
                    </div>

                    <!-- More -->
                    <div class="filter_group" data-selected="">
                        <span class="filter_label">
                            <span>เพิ่มเติม</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                        </span>
                    </div>

                </div>
            </form>
        HTML;

        $html  = '<div class="nhg-wrapper">';
        $html .= $html_filters;

        // Results count + sort bar
        $html .= '<div class="nhg-toolbar">
                    <div class="nhg-toolbar-count">ค้นพบ <span id="total_items_count">0</span> ประกาศ</div>
                    <div class="nhg-toolbar-sort">
                        <span>เรียงจาก:</span>
                        <select id="sortby">
                            <option value="created_at:DESC">ใหม่ล่าสุด</option>
                            <option value="created_at:ASC">เก่าไปหาใหม่</option>
                            <option value="cost_per_month:ASC">ราคาน้อย-มาก</option>
                            <option value="cost_per_month:DESC">ราคามาก-น้อย</option>
                            <option value="average_score:DESC">คะแนนสูงสุด</option>
                        </select>
                    </div>
                  </div>';

        $html .= '<div id="nursing_homes_grid">';
        $html .= '  <div id="nursing_homes_grid_results" 
                        data-order="DESC" 
                        data-certified="'.esc_attr($args['certified']).'" 
                        data-orderby="created_at" 
                        data-perpage="'.esc_attr($args['per_page']).'"
                        data-rete=""
                        data-cost=""
                        data-type="NURSING_HOME"
                        data-facility=""
                        data-room=""
                        data-province="'.esc_attr($args['province']).'"
                        data-zone="'.esc_attr($args['zone']).'"></div>';
        // ⏳ Spinner element
        $html .= '  <div id="loading-spinner" style="display:none;text-align:center;padding:20px;">
                        <div class="spinner"></div>
                        กำลังโหลด
                    </div>';
        $html .= '</div>';
        $html .= '</div>';

        // ✅ Filter & Spinner CSS
        $html .= '<style>
            /* === Layout wrapper === */
            .nhg-wrapper {
                display: flex;
                flex-direction: column;
                gap: 24px;
            }

            /* === Spinner === */
            .spinner {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #286F51;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                animation: spin 0.8s linear infinite;
                margin: auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .fade-in {
                opacity: 0;
                transform: translateY(10px);
                animation: fadeIn 0.4s ease forwards;
            }
            @keyframes fadeIn {
                to { opacity: 1; transform: translateY(0); }
            }

            /* === Toolbar (count + sort) === */
            .nhg-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 15px;
                color: #8C8A94;
            }
            .nhg-toolbar-count span {
                font-weight: 600;
                color: #286F51;
            }
            .nhg-toolbar-sort {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .nhg-toolbar-sort select {
                border: 1px solid #E2E2E5;
                border-radius: 8px;
                padding: 6px 12px;
                font-size: 14px;
                color: #3D3D3D;
                background: #fff;
                cursor: pointer;
                outline: none;
            }
            .nhg-toolbar-sort select:focus {
                border-color: #286F51;
            }

            /* === Filter pills row === */
            #home_filters {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }
            .filter_group {
                position: relative;
                cursor: pointer;
                white-space: nowrap;
                user-select: none;
            }
            .filter_label {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #fff;
                border: 1.5px solid #E2E2E5;
                border-radius: 9999px;
                padding: 10px 20px;
                font-size: 14px;
                font-weight: 500;
                color: #3D3D3D;
                transition: all 0.2s ease;
                line-height: 1;
            }
            .filter_label:hover {
                border-color: #286F51;
                color: #286F51;
            }
            .filter_group.active .filter_label,
            .filter_group.has-selected .filter_label {
                border-color: #286F51;
                color: #286F51;
                background: #EDF7F2;
            }
            .filter_label .chevron {
                transition: transform 0.25s ease;
                opacity: 0.45;
                margin-left: 2px;
            }
            .filter_group.active .filter_label .chevron {
                transform: rotate(180deg);
                opacity: 1;
            }

            /* === Dropdown panel === */
            .filter_group .options {
                display: none;
                flex-direction: column;
                position: absolute;
                top: calc(100% + 8px);
                left: 0;
                background: #fff;
                border: 1px solid #EBEBEB;
                border-radius: 16px;
                padding: 8px;
                z-index: 50;
                font-size: 14px;
                white-space: nowrap;
                min-width: 210px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.10);
            }
            .filter_group.active .options {
                display: flex;
            }

            /* === Scrollable list (facilities) === */
            .options-scroll {
                display: flex;
                flex-direction: column;
                max-height: 300px;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #D0D0D0 transparent;
            }
            .options-scroll::-webkit-scrollbar { width: 5px; }
            .options-scroll::-webkit-scrollbar-track { background: transparent; }
            .options-scroll::-webkit-scrollbar-thumb { background: #D0D0D0; border-radius: 10px; }

            /* === Checkbox rows === */
            .filter-check {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 9px 12px;
                border-radius: 10px;
                cursor: pointer;
                color: #4A4A4A;
                transition: background 0.15s ease;
                font-weight: 400;
                line-height: 1.3;
            }
            .filter-check:hover {
                background: #F7F7F7;
            }
            .filter-check input[type="checkbox"] {
                appearance: none;
                -webkit-appearance: none;
                width: 18px;
                height: 18px;
                min-width: 18px;
                border: 2px solid #CBCBCB;
                border-radius: 5px;
                cursor: pointer;
                position: relative;
                transition: all 0.15s ease;
                background: #fff;
            }
            .filter-check input[type="checkbox"]:checked {
                background: #286F51;
                border-color: #286F51;
            }
            .filter-check input[type="checkbox"]:checked::after {
                content: "";
                position: absolute;
                left: 5px;
                top: 1px;
                width: 5px;
                height: 10px;
                border: solid #fff;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
            }

            /* === Link options (Room) === */
            .filter-option {
                display: block;
                padding: 10px 14px;
                border-radius: 10px;
                color: #4A4A4A;
                text-decoration: none !important;
                transition: all 0.15s ease;
                font-weight: 400;
            }
            .filter-option:hover {
                background: #F7F7F7;
                color: #286F51;
            }
            .filter-option.active {
                background: #EDF7F2;
                color: #286F51;
                font-weight: 600;
            }

            /* === Rating options === */
            .options-rating {
                gap: 2px;
            }
            .rating-option {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 9px 14px;
                border-radius: 10px;
                color: #4A4A4A;
                text-decoration: none !important;
                transition: all 0.15s ease;
                font-weight: 400;
            }
            .rating-option:hover {
                background: #F7F7F7;
            }
            .rating-option.active {
                background: #FFF8E1;
                color: #B8860B;
                font-weight: 600;
            }
            .rating-star {
                color: #D5D5D5;
                font-size: 16px;
                line-height: 1;
            }
            .rating-option.active .rating-star,
            .rating-option:hover .rating-star {
                color: #F59E0B;
            }

            /* === Price range slider === */
            #price_filter .options {
                min-width: 280px;
            }
            .price-slider-wrap {
                padding: 12px 12px 8px;
            }
            .price-slider-values {
                display: flex;
                justify-content: space-between;
                font-size: 13px;
                font-weight: 600;
                color: #286F51;
                margin-bottom: 10px;
            }
            .price-slider-track {
                position: relative;
                height: 6px;
                background: #E8E8E8;
                border-radius: 3px;
                margin: 8px 0;
            }
            .price-slider-range {
                position: absolute;
                height: 100%;
                background: #286F51;
                border-radius: 3px;
                z-index: 1;
            }
            .price-slider-track input[type="range"] {
                position: absolute;
                top: -6px;
                left: 0;
                width: 100%;
                height: 18px;
                -webkit-appearance: none;
                appearance: none;
                background: transparent;
                pointer-events: none;
                z-index: 2;
                margin: 0;
                padding: 0;
            }
            .price-slider-track input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                width: 22px;
                height: 22px;
                background: #fff;
                border: 3px solid #286F51;
                border-radius: 50%;
                cursor: pointer;
                pointer-events: auto;
                box-shadow: 0 1px 4px rgba(0,0,0,0.15);
            }
            .price-slider-track input[type="range"]::-moz-range-thumb {
                width: 22px;
                height: 22px;
                background: #fff;
                border: 3px solid #286F51;
                border-radius: 50%;
                cursor: pointer;
                pointer-events: auto;
                box-shadow: 0 1px 4px rgba(0,0,0,0.15);
            }
            .price-divider {
                height: 1px;
                background: #F0F0F0;
                margin: 4px 8px;
            }

            /* === Province filter === */
            .province-search-wrap {
                padding: 4px 4px 8px;
                border-bottom: 1px solid #F0F0F0;
                margin-bottom: 4px;
            }
            .province-search-wrap input {
                width: 100%;
                padding: 10px 12px;
                border: 1.5px solid #E2E2E5;
                border-radius: 10px;
                font-size: 14px;
                outline: none;
                color: #3D3D3D;
                background: #FAFAFA;
                box-sizing: border-box;
            }
            .province-search-wrap input:focus {
                border-color: #286F51;
                background: #fff;
            }
            .province-search-wrap input::placeholder {
                color: #B0B0B0;
            }
            .province-item {
                display: block;
                padding: 10px 14px;
                border-radius: 10px;
                color: #4A4A4A;
                text-decoration: none !important;
                transition: all 0.15s ease;
                font-weight: 400;
                cursor: pointer;
            }
            .province-item:hover {
                background: #F7F7F7;
                color: #286F51;
            }
            .province-loading {
                padding: 16px;
                text-align: center;
                color: #B0B0B0;
                font-size: 13px;
            }
            #province_filter .options {
                min-width: 260px;
            }
            #province_filter .options-scroll {
                max-height: 280px;
            }

            /* ============================
               MOBILE (< 768px)
               ============================ */
            @media (max-width: 768px) {
                .nhg-wrapper { gap: 16px; }

                /* Horizontal scroll pills */
                #home_filters {
                    flex-wrap: nowrap !important;
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: none;
                    gap: 8px;
                    padding-bottom: 2px;
                }
                #home_filters::-webkit-scrollbar { display: none; }

                /* Green pills on mobile */
                .filter_label {
                    background: #286F51;
                    border-color: #286F51;
                    color: #fff;
                    padding: 9px 16px;
                    font-size: 13px;
                    font-weight: 500;
                }
                .filter_label:hover {
                    background: #1F5A40;
                    border-color: #1F5A40;
                    color: #fff;
                }
                .filter_label svg { stroke: #fff; }
                .filter_label .chevron { opacity: 0.7; }

                .filter_group.active .filter_label,
                .filter_group.has-selected .filter_label {
                    background: #1F5A40;
                    border-color: #1F5A40;
                    color: #fff;
                }
                .filter_group.active .filter_label .chevron {
                    opacity: 1;
                }

                /* Active filter group above backdrop */
                .filter_group.active {
                    z-index: 1001;
                }

                /* Bottom sheet dropdown */
                .filter_group .options {
                    position: fixed;
                    left: 0;
                    right: 0;
                    top: auto;
                    bottom: 0;
                    width: 100%;
                    min-width: unset;
                    border-radius: 20px 20px 0 0;
                    padding: 20px 16px;
                    box-shadow: 0 -6px 30px rgba(0,0,0,0.18);
                    max-height: 65vh;
                    overflow-y: auto;
                    z-index: 1000;
                    border: none;
                }
                .filter_group.active .options {
                    animation: slideUp 0.3s ease;
                }
                @keyframes slideUp {
                    from { transform: translateY(100%); }
                    to   { transform: translateY(0); }
                }

                /* Backdrop overlay (injected via JS) */
                .nhg-backdrop {
                    position: fixed;
                    inset: 0;
                    background: rgba(0,0,0,0.35);
                    z-index: 999;
                }

                /* Toolbar stacks on mobile */
                .nhg-toolbar {
                    flex-direction: column;
                    gap: 8px;
                    align-items: flex-start;
                    font-size: 14px;
                }
            }
        </style>';

        return $html;
    }

    public function nusingHomeInfo($atts) {
        $id = $_GET['id'];
        $response = wp_remote_get( 'https://api.example.com/data' );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            echo "เกิดข้อผิดพลาด: $error_message";
        } else {
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );
        }

    }

    public function nursingHomeSpecific($atts) {
        $this->enqueueScripts();
        $args = shortcode_atts( array(
            'ids' => '',
        ), $atts );

        // "9,620,124" -> [9, 620, 124] เรียงตามลำดับที่ระบุ (0/ค่าว่างถูกกรองทิ้ง)
        $ids = array_values(array_filter(array_map('intval', explode(',', $args['ids']))));

        $this->fetchAndCacheSpecificNursingHomes($ids);

        return '<div class="swiper-wrapper-container" style="position:relative;"><div class="swiper-container" id="nursinghome-list" data-limit="' . esc_attr(count($ids)) . '" data-certified="0" data-ids="' . esc_attr(implode(',', $ids)) . '">กำลังโหลดข้อมูล...</div><div class="swiper-button-next nursinghome-swiper-button-next"></div><div class="swiper-button-prev nursinghome-swiper-button-prev"></div></div>';
    }

}

new NursingHomeHandler();

