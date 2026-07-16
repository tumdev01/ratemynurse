<?php

/**
 * ===========================================
 * AccessGuard - ตัวกลางสำหรับจัดการสิทธิ์
 * ===========================================
 *
 * Global Rule (BASIC plan / Guest):
 *   - nursing-info, nursing-home-info, job-info → รวมกันสูงสุด 10 โปรไฟล์/เดือน
 *   - นับข้ามประเภท (ดูพยาบาล 5 + สถานพยาบาล 3 + งาน 2 = 10 เต็ม)
 *   - Paid plan → ไม่จำกัด
 */
class AccessGuard
{
    protected static $instance;
    protected $me = null;
    protected $monthlyLimit = 10;
    private static $overlayRendered = false;
    private static $subscriptionExpiredRendered = false;
    private $accessCheckResult = null;

    // Profile types
    const PROFILE_MEMBER = 'MEMBER';
    const PROFILE_NURSING = 'NURSING';
    const PROFILE_NURSING_HOME = 'NURSING_HOME';
    const PROFILE_JOB = 'JOB';

    // Pages ที่ต้องจำกัดการดู (BASIC rule)
    const RESTRICTED_PAGES = ['nursing-info', 'nursing-home-info', 'job-info'];

    const ACCESS_GUEST        = 'GUEST';
    const ACCESS_MEMBER       = 'MEMBER';
    const ACCESS_NURSING      = 'NURSING';
    const ACCESS_NURSING_HOME = 'NURSING_HOME';

    const PLAN_GUEST      = 'GUEST';
    const PLAN_BASIC      = 'BASIC';
    const PLAN_PREMIUM    = 'PREMIUM';
    const PLAN_ENTERPRISE = 'ENTERPRISE';
    const PLAN_PROFESSIONAL = 'PROFESSIONAL';
    const PLAN_VIP        = 'VIP';

    const BASIC_NURSING_ACCEPT_LIMIT = 3;
    const UPLOAD_LIMITS = 5;
    const CLICK_CALL_MONTHLY_LIMIT = 10;
    const CLICK_CONTACT_MONTHLY_LIMIT = 10;
    const LIMITED_CLICK_ACTIONS = ['click_call', 'click_contact'];

    /**
     * Mapping: user_type => allowed plans
     */
    protected static array $allowedPlansByUserType = [
        self::ACCESS_MEMBER => [
            self::PLAN_BASIC,
            self::PLAN_PREMIUM,
        ],
        self::ACCESS_NURSING => [
            self::PLAN_BASIC,
            self::PLAN_PROFESSIONAL,
            self::PLAN_VIP,
        ],
        self::ACCESS_NURSING_HOME => [
            self::PLAN_BASIC,
            self::PLAN_PREMIUM,
            self::PLAN_ENTERPRISE,
        ],
    ];

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Hook ที่ทำงานทั้ง early และ late เพื่อให้ overlay แสดงได้เสมอ
        add_action('template_redirect', [$this, 'checkAndRenderOverlay'], 1);
        // Fallback: ถ้า getInstance() ถูกเรียกหลัง template_redirect แล้ว → hook ตรงไป wp_head/wp_footer
        if (did_action('template_redirect')) {
            $this->checkAndRenderOverlay();
        }

        // เช็ค subscription หมดอายุทุกหน้า
        add_action('template_redirect', [$this, 'checkSubscriptionExpiry'], 2);
        if (did_action('template_redirect')) {
            $this->checkSubscriptionExpiry();
        }

        // Debug panel: เปิดด้วย ?rmn_debug=1
        if (isset($_GET['rmn_debug']) && $_GET['rmn_debug'] === '1') {
            add_action('wp_footer', [$this, 'renderDebugPanel'], 9999);
        }
    }

    /**
     * ตรวจสอบหน้าปัจจุบันและแสดง overlay ถ้าจำเป็น
     */
    public function checkAndRenderOverlay()
    {
        if (self::$overlayRendered) {
            return;
        }

        $currentProfile = $this->getCurrentProfileContext();
        if (!$currentProfile) {
            return;
        }

        $access = $this->viewProfileResult(
            $currentProfile['id'],
            'page',
            $currentProfile['type']
        );

        $this->accessCheckResult = $access;

        if (!$access['allowed']) {
            // ถ้า wp_head ยังไม่ fire → hook ปกติ
            if (!did_action('wp_head')) {
                add_action('wp_head', [$this, 'renderBlurStyles']);
            }
            add_action('wp_footer', [$this, 'renderBlurOverlay']);
            self::$overlayRendered = true;
        }
    }

    /**
     * เช็ค subscription หมดอายุ → แสดง popup เตือนทุกหน้า
     * ทำงานเฉพาะ user ที่ login แล้ว + เคยมี subscription (ไม่ใช่ BASIC/GUEST ที่ไม่เคยซื้อ)
     */
    public function checkSubscriptionExpiry()
    {
        if (self::$subscriptionExpiredRendered) {
            return;
        }

        // Bypass mode → ข้ามการเช็คหมดอายุทั้งหมด (เพื่อช่วงเปิดบริการใหม่)
        if ($this->isBypassActive()) {
            return;
        }

        // ต้อง login แล้วเท่านั้น
        if (!$this->isLogged()) {
            return;
        }

        $me = $this->me();
        if (!$me) {
            return;
        }

        // เช็คว่ามี subscription อยู่ (เคยซื้อ) แต่หมดอายุแล้ว
        $subscription = $me['data']['profile']['current_active_subscription'] ?? null;
        if (!$subscription) {
            return;
        }

        // ถ้ายังไม่หมดอายุ → ไม่ต้องแสดง
        if ($this->isSubscriptionActive()) {
            return;
        }

        // Subscription หมดอายุแล้ว → แสดง popup
        if (!did_action('wp_head')) {
            add_action('wp_head', [$this, 'renderExpiredStyles']);
        }
        add_action('wp_footer', [$this, 'renderExpiredOverlay']);
        self::$subscriptionExpiredRendered = true;
    }

    /**
     * CSS สำหรับ subscription expired popup
     */
    public function renderExpiredStyles()
    {
        ?>
        <style>
            .rmn-expired-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                z-index: 999999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                animation: rmn-fade-in 0.3s ease-out;
            }

            .rmn-expired-modal {
                background: white;
                border-radius: 20px;
                padding: 40px 30px;
                max-width: 500px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                text-align: center;
                animation: rmn-scale-in 0.3s ease-out;
                position: relative;
            }

            .rmn-expired-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                background: linear-gradient(135deg, #E67E22 0%, #F39C12 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 20px rgba(230, 126, 34, 0.3);
            }

            .rmn-expired-icon svg {
                width: 40px;
                height: 40px;
                fill: white;
            }

            .rmn-expired-modal h2 {
                font-size: 24px;
                font-weight: 600;
                color: #1F1F1F;
                margin: 0 0 12px;
                line-height: 1.4;
            }

            .rmn-expired-modal .rmn-expired-date {
                font-size: 14px;
                color: #E67E22;
                font-weight: 500;
                margin: 0 0 16px;
            }

            .rmn-expired-modal p {
                font-size: 14px;
                color: #5A5A5A;
                line-height: 1.6;
                margin: 0 0 24px;
            }

            .rmn-expired-modal .rmn-btn-renew {
                display: block;
                width: 100%;
                padding: 14px 24px;
                background: #E67E22;
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-bottom: 12px;
            }

            .rmn-expired-modal .rmn-btn-renew:hover {
                background: #D35400;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
            }

            .rmn-expired-modal .rmn-btn-upgrade {
                display: block;
                width: 100%;
                padding: 14px 24px;
                background: #286F51;
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-bottom: 12px;
            }

            .rmn-expired-modal .rmn-btn-upgrade:hover {
                background: #1d5239;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(40, 111, 81, 0.3);
            }

            .rmn-expired-modal .rmn-btn-close-expired {
                display: block;
                width: 100%;
                padding: 14px 24px;
                background: white;
                color: #5A5A5A;
                border: 1px solid #E2E2E4;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .rmn-expired-modal .rmn-btn-close-expired:hover {
                background: #F5F5F5;
                border-color: #E67E22;
                color: #E67E22;
            }

            @media (max-width: 768px) {
                .rmn-expired-modal {
                    padding: 30px 20px;
                    margin: 20px;
                }
                .rmn-expired-modal h2 {
                    font-size: 20px;
                }
            }
        </style>
        <?php
    }

    /**
     * Popup แสดงเมื่อ subscription หมดอายุ
     */
    public function renderExpiredOverlay()
    {
        $me = $this->me();
        $subscription = $me['data']['profile']['current_active_subscription'] ?? [];
        $planName = $subscription['plan'] ?? 'BASIC';
        $expiredAt = $subscription['expired_at'] ?? '-';
        ?>
        <div class="rmn-expired-overlay" id="rmn-expired-overlay">
            <div class="rmn-expired-modal">
                <div class="rmn-expired-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="none"/>
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>

                <h2>แพ็กเกจของคุณหมดอายุแล้ว</h2>

                <div class="rmn-expired-date">
                    แพ็กเกจ <?php echo esc_html($planName); ?> หมดอายุเมื่อ <?php echo esc_html($expiredAt); ?>
                </div>

                <p>
                    กรุณาต่ออายุแพ็กเกจเพื่อใช้งานฟีเจอร์พิเศษต่อไป<br>
                    หรืออัปเกรดเป็นแพ็กเกจที่สูงขึ้นเพื่อรับสิทธิประโยชน์เพิ่มเติม
                </p>

                <a href="/subscription" class="rmn-btn-renew">
                    ต่ออายุแพ็กเกจ
                </a>
                <a href="/subscription" class="rmn-btn-upgrade">
                    อัปเกรดแพ็กเกจ
                </a>
                <button type="button" class="rmn-btn-close-expired" onclick="document.getElementById('rmn-expired-overlay').remove();">
                    ปิด
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * ตรวจสอบว่าอยู่ในหน้าโปรไฟล์ใด
     * รองรับทั้ง get_query_var และ $_GET fallback
     */
    private function getCurrentProfileContext(): ?array
    {
        // ตรวจสอบ Nursing Profile
        $nursingId = $this->getQueryVar('nursing_id');
        if ($nursingId && is_numeric($nursingId)) {
            return [
                'type' => self::PROFILE_NURSING,
                'id' => (int)$nursingId,
                'page' => 'nursing-info'
            ];
        }

        // ตรวจสอบ Nursing Home Profile
        $nursingHomeId = $this->getQueryVar('nursing_home_id');
        if ($nursingHomeId && is_numeric($nursingHomeId)) {
            return [
                'type' => self::PROFILE_NURSING_HOME,
                'id' => (int)$nursingHomeId,
                'page' => 'nursing-home-info'
            ];
        }

        // ตรวจสอบ Job Profile
        $jobId = $this->getQueryVar('job_id');
        if ($jobId && is_numeric($jobId)) {
            return [
                'type' => self::PROFILE_JOB,
                'id' => (int)$jobId,
                'page' => 'job-info'
            ];
        }

        return null;
    }

    /**
     * ดึง query var โดยลอง get_query_var ก่อน แล้ว fallback เป็น $_GET
     */
    private function getQueryVar(string $var): string
    {
        // ลอง WordPress query var ก่อน
        $value = get_query_var($var, '');
        if ($value !== '' && $value !== false) {
            return (string)$value;
        }

        // Fallback: ดึงจาก $_GET (กรณี query var ไม่ได้ register หรือ rewrite rule ไม่ตรง)
        if (isset($_GET[$var]) && is_numeric($_GET[$var])) {
            return sanitize_text_field($_GET[$var]);
        }

        return '';
    }

    /**
     * เพิ่ม CSS สำหรับ blur effect
     */
    public function renderBlurStyles()
    {
        ?>
        <style>
            body.rmn-page-restricted {
                overflow: hidden;
                position: relative;
            }

            body.rmn-page-restricted > *:not(.rmn-blur-overlay, #section-678-21) {
                filter: blur(10px);
                pointer-events: none;
                user-select: none;
            }

            .rmn-blur-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                z-index: 999999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                animation: rmn-fade-in 0.3s ease-out;
            }

            .rmn-restriction-modal {
                background: white;
                border-radius: 20px;
                padding: 40px 30px;
                max-width: 500px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                text-align: center;
                animation: rmn-scale-in 0.3s ease-out;
                position: relative;
            }

            .rmn-restriction-modal img.rmn-hidden-img {
                max-width: 356px;
                width: 100%;
                height: auto;
                margin: 0 auto 20px;
                display: block;
            }

            .rmn-restriction-modal h2 {
                font-size: 24px;
                font-weight: 600;
                color: #1F1F1F;
                margin: 0 0 16px;
                line-height: 1.4;
            }

            .rmn-restriction-modal p {
                font-size: 14px;
                color: #5A5A5A;
                line-height: 1.6;
                margin: 0 0 24px;
            }

            .rmn-restriction-modal .rmn-btn-upgrade {
                display: block;
                width: 100%;
                padding: 14px 24px;
                background: #286F51;
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-bottom: 12px;
            }

            .rmn-restriction-modal .rmn-btn-upgrade:hover {
                background: #1d5239;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(40, 111, 81, 0.3);
            }

            .rmn-restriction-modal .rmn-btn-secondary {
                display: block;
                width: 100%;
                padding: 14px 24px;
                background: white;
                color: #5A5A5A;
                border: 1px solid #E2E2E4;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 500;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .rmn-restriction-modal .rmn-btn-secondary:hover {
                background: #F5F5F5;
                border-color: #286F51;
                color: #286F51;
            }

            .rmn-restriction-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                background: linear-gradient(135deg, #286F51 0%, #1D8654 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 20px rgba(40, 111, 81, 0.2);
            }

            .rmn-restriction-icon svg {
                width: 40px;
                height: 40px;
                fill: white;
            }

            @keyframes rmn-fade-in {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes rmn-scale-in {
                from {
                    opacity: 0;
                    transform: scale(0.9) translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            @media (max-width: 768px) {
                .rmn-restriction-modal {
                    padding: 30px 20px;
                    margin: 20px;
                }
                .rmn-restriction-modal h2 {
                    font-size: 20px;
                }
                .rmn-restriction-modal img.rmn-hidden-img {
                    max-width: 280px;
                }
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('rmn-page-restricted');
            });
        </script>
        <?php
    }

    /**
     * เพิ่ม overlay modal
     */
    public function renderBlurOverlay()
    {
        $remaining = $this->getRemainingViews();
        $targetHref = '/subscription';
        $class      = '';
        $buttonTxt  = 'อัปเกรดแพ็กเกจ';
        if ( !$this->isLogged()) {
            $targetHref = '#';
            $class      = 'authen';
            $buttonTxt  = 'สมัครสมาชิกฟรี';
        }
        ?>
        <div class="rmn-blur-overlay">
            <div class="rmn-restriction-modal">
                <div class="rmn-restriction-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C9.243 2 7 4.243 7 7v3H6c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8c0-1.103-.897-2-2-2h-1V7c0-2.757-2.243-5-5-5zM9 7c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9V7zm4 10.723V20h-2v-2.277c-.595-.347-1-.985-1-1.723 0-1.103.897-2 2-2s2 .897 2 2c0 .738-.405 1.376-1 1.723z"/>
                    </svg>
                </div>

                <h2>คุณดูโปรไฟล์ครบจำนวนเดือนนี้แล้ว</h2>

                <p>
                    คุณได้ใช้สิทธิ์ดูโปรไฟล์ฟรี <?php echo $this->monthlyLimit; ?> โปรไฟล์ต่อเดือนครบแล้ว<br>
                    อัปเกรดแพ็กเกจตอนนี้เลย เพื่อดูโปรไฟล์ได้ไม่จำกัด<br>
                    และเข้าถึงฟีเจอร์พิเศษอื่นๆ อีกมากมาย
                </p>

                <a href="<?php echo $targetHref; ?>" class="rmn-btn-upgrade <?php echo $class; ?>">
                    <?php echo $buttonTxt; ?>
                </a>
                <a href="/" class="rmn-btn-secondary">
                    กลับหน้าหลัก
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * แปลง profile type เป็นข้อความไทย
     */
    private function getProfileTypeText(string $type): string
    {
        $map = [
            self::PROFILE_NURSING => 'โปรไฟล์พยาบาล',
            self::PROFILE_NURSING_HOME => 'โปรไฟล์สถานพยาบาล',
            self::PROFILE_JOB => 'โปรไฟล์งาน',
        ];
        return $map[$type] ?? 'โปรไฟล์';
    }

    public function isLogged(): bool
    {
        return !empty($_COOKIE['access_token']);
    }

    /**
     * Service launch bypass — เปิดทุก feature ให้สมาชิก login โดยไม่จำกัด
     * เปิดใช้โดย: define('RMN_OPEN_ACCESS', true) ใน wp-config.php
     * รองรับค่า truthy: true, 1, '1', 'true', 'yes', 'on'
     * Guest ยังถูกจำกัดเหมือนเดิม (เงื่อนไข isLogged กั้นไว้)
     */
    public function isBypassActive(): bool
    {
        if (!defined('RMN_OPEN_ACCESS')) {
            return false;
        }
        $value = filter_var(RMN_OPEN_ACCESS, FILTER_VALIDATE_BOOLEAN);
        return $value === true && $this->isLogged();
    }

    /* ================================
     |  USER / SUBSCRIPTION
     |================================ */

    public function me(): ?array
    {
        if ($this->me !== null) {
            return $this->me;
        }

        if (empty($_COOKIE['access_token'])) {
            return $this->me = null;
        }

        $token = sanitize_text_field($_COOKIE['access_token']);

        // Cache สั้นๆ กัน /api/me ถูกยิงซ้ำทุกครั้งที่โหลดหน้า (endpoint นี้ถูกเรียกทุกหน้าผ่าน
        // checkSubscriptionExpiry() บน template_redirect) ใช้ cache key เดียวกับ rmn_get_current_user()
        // ใน rmn-services.php เพื่อให้ทั้ง 2 จุดใช้ cache ร่วมกัน
        $cache_key = 'rmn_me_' . substr(hash('sha256', $token), 0, 16);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $this->me = $cached;
        }

        $response = wp_remote_get(
            'https://services.ratemynurse.org/api/me',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'timeout' => 5,
            ]
        );

        if (is_wp_error($response)) {
            return $this->me = null;
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return $this->me = null;
        }

        $data = json_decode(
            wp_remote_retrieve_body($response),
            true
        );

        set_transient($cache_key, $data, 30);

        return $this->me = $data;
    }

    public function getUserRole(): ?string
    {
        $me = $this->me();

        if (!$me) {
            return null;
        }

        return $me['data']['user_type'] ?? null;
    }

    public function isOwner(int $ownerId): bool
    {
        $me = $this->me();
        return $me && isset($me['data']['id']) && (int)$me['data']['id'] === $ownerId;
    }

    public function isPaidMember(): bool
    {
        if ($this->isBypassActive()) {
            return true;
        }

        $me = $this->me();
        return $me
            && !empty($me['data']['profile']['current_active_subscription'])
            && ($me['data']['profile']['current_active_subscription']['plan'] ?? 'BASIC') !== 'BASIC'
            && $this->isSubscriptionActive();
    }

    /**
     * ตรวจสอบว่า subscription ยังไม่หมดอายุ
     * เช็คจาก expired_at ของ current_active_subscription (format: "d/m/Y H:i")
     */
    public function isSubscriptionActive(): bool
    {
        if ($this->isBypassActive()) {
            return true;
        }

        $me = $this->me();

        if (!$me || empty($me['data']['profile']['current_active_subscription'])) {
            return false;
        }

        $subscription = $me['data']['profile']['current_active_subscription'];
        $expiredAt = $subscription['expired_at'] ?? null;

        // ถ้าไม่มี expired_at → ถือว่า active (ป้องกัน API เก่า)
        if (!$expiredAt) {
            return true;
        }

        // Parse format "d/m/Y H:i" เช่น "13/01/2026 00:00"
        try {
            $tz = new \DateTimeZone('Asia/Bangkok');
            $expiry = \DateTime::createFromFormat('d/m/Y H:i', $expiredAt, $tz);

            if (!$expiry) {
                return true;
            }

            $now = new \DateTime('now', $tz);
            return $now <= $expiry;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function isMemberUser(): bool
    {
        return $this->getUserRole() === self::PROFILE_MEMBER;
    }

    public function isNursingUser(): bool
    {
        return $this->getUserRole() === self::PROFILE_NURSING;
    }

    public function isNursingHomeUser(): bool
    {
        return $this->getUserRole() === self::PROFILE_NURSING_HOME;
    }

    /* ================================
     |  PUBLIC API
     |================================ */

    /**
     * ตรวจสอบสิทธิ์การเข้าถึงโปรไฟล์ (ใช้ได้จากทุกที่)
     * Global rule: BASIC/Guest → nursing-info + nursing-home-info + job-info รวมกันสูงสุด 10/เดือน
     *
     * @param int $profileId ID ของโปรไฟล์
     * @param string $mode 'page' หรือ 'section'
     * @param string $profileType ประเภทโปรไฟล์ (NURSING, NURSING_HOME, JOB)
     */
    public function viewProfileResult(
        int $profileId,
        string $mode = 'page',
        string $profileType = self::PROFILE_NURSING
    ): array {

        // เจ้าของ → ดูได้หมด
        if ($this->isOwner($profileId)) {
            return $this->allow('owner');
        }

        // สมาชิกจ่ายเงิน → ไม่จำกัด
        if ($this->isPaidMember()) {
            return $this->allow('paid');
        }

        // Guest / BASIC → ตรวจลิมิต global (10 โปรไฟล์/เดือน รวมทุกประเภท)
        if ($this->checkAndIncrementView($profileId, $profileType)) {
            return $this->allow('ok');
        }

        // เกินลิมิต
        return [
            'allowed' => false,
            'reason' => 'limit',
            'level' => $mode,
            'profile_type' => $profileType,
        ];
    }

    /**
     * Alias สำหรับ viewProfileResult - ใช้เรียกแบบง่ายจาก template
     * คืน true ถ้าดูได้, false ถ้าเกินลิมิต
     */
    public function canViewProfile(int $profileId, string $profileType = self::PROFILE_NURSING): bool
    {
        $result = $this->viewProfileResult($profileId, 'page', $profileType);
        return $result['allowed'];
    }

    /**
     * ดึงจำนวนโปรไฟล์ที่เหลือให้ดูได้ในเดือนนี้
     */
    public function getRemainingViews(): int
    {
        if ($this->isBypassActive()) {
            return PHP_INT_MAX;
        }

        $viewedKey = $this->getViewedProfilesKey();
        $viewedProfiles = get_transient($viewedKey);

        if ($viewedProfiles === false) {
            return $this->monthlyLimit;
        }

        $viewedCount = count($viewedProfiles);
        return max(0, $this->monthlyLimit - $viewedCount);
    }

    /* ================================
     |  INTERNAL
     |================================ */

    protected function allow(string $reason): array
    {
        return [
            'allowed' => true,
            'reason' => $reason,
            'level' => 'none',
        ];
    }

    /**
     * ตรวจสอบและเพิ่มจำนวนการดูโปรไฟล์
     * Global rule: นับรวมทุกประเภท (NURSING + NURSING_HOME + JOB) เป็นก้อนเดียว
     * จำกัด 10 โปรไฟล์ต่อเดือน สำหรับ BASIC/Guest
     */
    protected function checkAndIncrementView(int $profileId, string $profileType): bool
    {
        $viewedKey = $this->getViewedProfilesKey();
        $viewedProfiles = get_transient($viewedKey);

        if ($viewedProfiles === false) {
            $viewedProfiles = [];
        }

        // ใช้ | เป็น delimiter เพื่อไม่ให้ชนกับ _ ใน NURSING_HOME
        $uniqueProfileKey = "{$profileType}|{$profileId}";

        // ถ้าเคยดูโปรไฟล์นี้แล้ว → ให้ดูต่อได้ (ไม่นับซ้ำ)
        if (in_array($uniqueProfileKey, $viewedProfiles, true)) {
            return true;
        }

        // ตรวจสอบจำนวนโปรไฟล์ที่ดูไปแล้ว (รวมทุกประเภท = global limit)
        if (count($viewedProfiles) >= $this->monthlyLimit) {
            return false;
        }

        // เพิ่มโปรไฟล์นี้เข้าไปในรายการที่ดูแล้ว
        $viewedProfiles[] = $uniqueProfileKey;

        // เก็บไว้ 31 วัน (เผื่อเดือน 31 วัน)
        set_transient($viewedKey, $viewedProfiles, 31 * DAY_IN_SECONDS);

        return true;
    }

    /**
     * สร้าง key สำหรับเก็บรายการโปรไฟล์ที่ดูแล้ว (รวมทุกประเภท = global)
     */
    protected function getViewedProfilesKey(): string
    {
        $me = $this->me();
        $currentMonth = date('Y-m');

        if ($me && isset($me['data']['id'])) {
            return "rmn_viewed_user_{$me['data']['id']}_all_{$currentMonth}";
        }

        // สร้าง visitor cookie ถ้ายังไม่มี (อยู่ 30 วัน แม้ปิดเปิด browser)
        if (empty($_COOKIE['rmn_visitor'])) {
            $visitorId = wp_generate_uuid4();
            setcookie('rmn_visitor', $visitorId, [
                'expires'  => time() + (30 * DAY_IN_SECONDS),
                'path'     => '/',
                'domain'   => $_SERVER['HTTP_HOST'] ?? '',
                'secure'   => is_ssl(),
                'httponly'  => true,
                'samesite'  => 'Lax',
            ]);
            $_COOKIE['rmn_visitor'] = $visitorId;
        }

        $visitorId = sanitize_text_field($_COOKIE['rmn_visitor']);
        return "rmn_viewed_visitor_{$visitorId}_all_{$currentMonth}";
    }

    /**
     * คืน profile type ของ user ปัจจุบัน
     */
    public function getProfileType(): ?string
    {
        if (!$this->isLogged()) {
            return self::ACCESS_GUEST;
        }

        if ($this->isMemberUser()) {
            return self::ACCESS_MEMBER;
        }

        if ($this->isNursingUser()) {
            return self::ACCESS_NURSING;
        }

        if ($this->isNursingHomeUser()) {
            return self::ACCESS_NURSING_HOME;
        }

        return self::ACCESS_GUEST;
    }

    /**
     * คืน plan type: GUEST | BASIC | PREMIUM | ENTERPRISE | PROFESSIONAL | VIP
     */
    public function getPlanType(): string
    {
        $me = $this->me();

        if (!$me) {
            return self::PLAN_GUEST;
        }

        $subscription = $me['data']['profile']['current_active_subscription'] ?? null;

        if (!$subscription) {
            return self::PLAN_BASIC;
        }

        return match ($subscription['plan'] ?? 'BASIC') {
            'PROFESSIONAL'=> self::PLAN_PROFESSIONAL,
            'VIP' => self::PLAN_VIP,
            'ENTERPRISE' => self::PLAN_ENTERPRISE,
            'PREMIUM'    => self::PLAN_PREMIUM,
            default      => self::PLAN_BASIC,
        };
    }

    public function getCurrentPlan(): string
    {
        $me = $this->me();

        if (!$me) {
            return self::PLAN_GUEST;
        }

        // ถ้า subscription หมดอายุแล้ว → ถือเป็น BASIC
        if (!$this->isSubscriptionActive()) {
            return self::PLAN_BASIC;
        }

        return $me['data']['profile']['current_active_subscription']['plan']
            ?? self::PLAN_BASIC;
    }

    public function isPlanValidForUserType(): bool
    {
        $userType = $this->getProfileType();
        $plan = $this->getCurrentPlan();

        if (!$userType) {
            return false;
        }

        return in_array(
            $plan,
            self::$allowedPlansByUserType[$userType] ?? [],
            true
        );
    }

    public function canUsePlan(string $requiredPlan): bool
    {
        $currentPlan = $this->getCurrentPlan();

        return $currentPlan === $requiredPlan;
    }

    // Nursing accept contact from customer
    protected function getAcceptJobKey(): string
    {
        $me = $this->me();
        $currentMonth = date('Y-m');

        if ($me && isset($me['data']['id'])) {
            return "rmn_accept_job_{$me['data']['id']}_{$currentMonth}";
        }

        return '';
    }

    public function canAcceptJob(): bool
    {
        if (!$this->isNursingUser() && !$this->isNursingHomeUser()) {
            return false;
        }

        if ($this->isBypassActive()) {
            return true;
        }

        $plan = $this->getCurrentPlan();

        if ($plan !== self::PLAN_BASIC) {
            return true;
        }

        $key = $this->getAcceptJobKey();
        $count = (int) get_transient($key);

        return $count < self::BASIC_NURSING_ACCEPT_LIMIT;
    }

    public function incrementAcceptJob(): void
    {
        if ($this->isBypassActive()) {
            return;
        }

        $plan = $this->getCurrentPlan();

        if ($plan !== self::PLAN_BASIC) {
            return;
        }

        $key = $this->getAcceptJobKey();
        $count = (int) get_transient($key);
        $count++;

        set_transient($key, $count, 31 * DAY_IN_SECONDS);
    }

    /* ================================
     |  UPLOAD LIMITS (GUEST / BASIC = 5 files/month)
     |================================ */

    protected function getUploadKey(): string
    {
        $me = $this->me();
        $currentMonth = date('Y-m');

        if ($me && isset($me['data']['id'])) {
            return "rmn_upload_{$me['data']['id']}_{$currentMonth}";
        }

        return '';
    }

    public function canUpload(): bool
    {
        if (!$this->isLogged()) {
            return false;
        }

        if ($this->isBypassActive()) {
            return true;
        }

        $plan = $this->getCurrentPlan();

        // Paid plan → ไม่จำกัด
        if ($plan !== self::PLAN_BASIC && $plan !== self::PLAN_GUEST) {
            return true;
        }

        // GUEST / BASIC → จำกัด 5 ไฟล์/เดือน
        $key = $this->getUploadKey();
        if (empty($key)) {
            return false;
        }

        $count = (int) get_transient($key);
        return $count < self::UPLOAD_LIMITS;
    }

    public function incrementUpload(): void
    {
        if ($this->isBypassActive()) {
            return;
        }

        $plan = $this->getCurrentPlan();

        if ($plan !== self::PLAN_BASIC && $plan !== self::PLAN_GUEST) {
            return;
        }

        $key = $this->getUploadKey();
        if (empty($key)) {
            return;
        }

        $count = (int) get_transient($key);
        $count++;

        set_transient($key, $count, 31 * DAY_IN_SECONDS);
    }

    public function getRemainingUploads(): int
    {
        if ($this->isBypassActive()) {
            return PHP_INT_MAX;
        }

        $plan = $this->getCurrentPlan();

        if ($plan !== self::PLAN_BASIC && $plan !== self::PLAN_GUEST) {
            return PHP_INT_MAX;
        }

        $key = $this->getUploadKey();
        if (empty($key)) {
            return 0;
        }

        $count = (int) get_transient($key);
        return max(0, self::UPLOAD_LIMITS - $count);
    }

    /* ================================
     |  CLICK ACTION LIMITS (BASIC/GUEST = 10/month per action)
     |  click_call และ click_contact นับแยกกัน
     |================================ */

    protected function getClickActionKey(string $action): string
    {
        $me = $this->me();
        $currentMonth = date('Y-m');

        if ($me && isset($me['data']['id'])) {
            return "rmn_{$action}_{$me['data']['id']}_{$currentMonth}";
        }

        if (empty($_COOKIE['rmn_visitor'])) {
            $visitorId = wp_generate_uuid4();
            setcookie('rmn_visitor', $visitorId, [
                'expires'  => time() + (30 * DAY_IN_SECONDS),
                'path'     => '/',
                'domain'   => $_SERVER['HTTP_HOST'] ?? '',
                'secure'   => is_ssl(),
                'httponly'  => true,
                'samesite'  => 'Lax',
            ]);
            $_COOKIE['rmn_visitor'] = $visitorId;
        }

        $visitorId = sanitize_text_field($_COOKIE['rmn_visitor']);
        return "rmn_{$action}_visitor_{$visitorId}_{$currentMonth}";
    }

    protected function getClickActionLimit(string $action): int
    {
        return match ($action) {
            'click_call'    => self::CLICK_CALL_MONTHLY_LIMIT,
            'click_contact' => self::CLICK_CONTACT_MONTHLY_LIMIT,
            default         => 0,
        };
    }

    public function canClickAction(string $action): bool
    {
        if (!in_array($action, self::LIMITED_CLICK_ACTIONS, true)) {
            return true;
        }

        if ($this->isPaidMember()) {
            return true;
        }

        $key = $this->getClickActionKey($action);
        $count = (int) get_transient($key);
        $limit = $this->getClickActionLimit($action);

        return $count < $limit;
    }

    public function incrementClickAction(string $action): void
    {
        if (!in_array($action, self::LIMITED_CLICK_ACTIONS, true)) {
            return;
        }

        if ($this->isPaidMember()) {
            return;
        }

        $key = $this->getClickActionKey($action);
        $count = (int) get_transient($key);
        $count++;

        set_transient($key, $count, 31 * DAY_IN_SECONDS);
    }

    public function getRemainingClickActions(string $action): int
    {
        if (!in_array($action, self::LIMITED_CLICK_ACTIONS, true)) {
            return PHP_INT_MAX;
        }

        if ($this->isPaidMember()) {
            return PHP_INT_MAX;
        }

        $key = $this->getClickActionKey($action);
        $count = (int) get_transient($key);
        $limit = $this->getClickActionLimit($action);

        return max(0, $limit - $count);
    }

    /**
     * ดึงข้อมูลโปรไฟล์ทั้งหมดที่ดูแล้วในเดือนนี้
     */
    public function getViewedProfilesList(): array
    {
        $viewedKey = $this->getViewedProfilesKey();
        $viewedProfiles = get_transient($viewedKey);

        if ($viewedProfiles === false) {
            return [];
        }

        return $viewedProfiles;
    }

    /**
     * เช็คว่าโปรไฟล์นี้ถูกดูแล้วหรือยัง
     */
    public function hasViewedProfile(int $profileId, string $profileType): bool
    {
        $uniqueProfileKey = "{$profileType}|{$profileId}";
        $viewedProfiles = $this->getViewedProfilesList();

        return in_array($uniqueProfileKey, $viewedProfiles, true);
    }

    /**
     * Debug: แสดงสถานะทั้งหมดของ AccessGuard ณ ขณะนั้น
     * เรียกใช้: AccessGuard::getInstance()->debug();
     * หรือเปิดหน้าเว็บแล้วต่อท้าย ?rmn_debug=1
     */
    public function debug(): array
    {
        $me = $this->me();
        $profileContext = $this->getCurrentProfileContext();
        $viewedKey = $this->getViewedProfilesKey();
        $viewedProfiles = get_transient($viewedKey);

        $debug = [
            'timestamp'        => date('Y-m-d H:i:s'),
            'current_month'    => date('Y-m'),
            'me' => $me,
            // ---- Bypass Status ----
            'bypass' => [
                'constant_defined'  => defined('RMN_OPEN_ACCESS'),
                'constant_raw'      => defined('RMN_OPEN_ACCESS') ? var_export(RMN_OPEN_ACCESS, true) : '(not defined)',
                'constant_resolved' => defined('RMN_OPEN_ACCESS') ? filter_var(RMN_OPEN_ACCESS, FILTER_VALIDATE_BOOLEAN) : false,
                'is_logged_in'      => $this->isLogged(),
                'is_bypass_active'  => $this->isBypassActive(),
            ],
            // ---- User ----
            'user' => [
                'is_logged_in'   => $this->isLogged(),
                'user_id'        => $me['data']['id'] ?? null,
                'user_type'      => $me['data']['user_type'] ?? null,
                'profile_type'   => $this->getProfileType(),
                'plan_type'      => $this->getPlanType(),
                'current_plan'   => $this->getCurrentPlan(),
                'is_paid_member' => $this->isPaidMember(),
                'is_member'      => $this->isMemberUser(),
                'is_nursing'     => $this->isNursingUser(),
                'is_nursing_home'=> $this->isNursingHomeUser(),
            ],

            // ---- Current Page Detection ----
            'page_detection' => [
                'profile_context'       => $profileContext,
                'query_var_nursing_id'  => $this->getQueryVar('nursing_id'),
                'query_var_nh_id'       => $this->getQueryVar('nursing_home_id'),
                'query_var_job_id'      => $this->getQueryVar('job_id'),
                'current_url'           => $_SERVER['REQUEST_URI'] ?? '(unknown)',
            ],

            // ---- View Limits (Global Rule) ----
            'view_limits' => [
                'monthly_limit'      => $this->monthlyLimit,
                'transient_key'      => $viewedKey,
                'transient_exists'   => $viewedProfiles !== false,
                'viewed_profiles'    => $viewedProfiles ?: [],
                'viewed_count'       => is_array($viewedProfiles) ? count($viewedProfiles) : 0,
                'remaining_views'    => $this->getRemainingViews(),
                'is_limit_reached'   => is_array($viewedProfiles) && count($viewedProfiles) >= $this->monthlyLimit,
            ],

            // ---- Access Check (Current Page) ----
            'access_result' => $profileContext
                ? $this->viewProfileResult($profileContext['id'], 'page', $profileContext['type'])
                : '(not a profile page)',

            // ---- Overlay State ----
            'overlay' => [
                'overlay_rendered'            => self::$overlayRendered,
                'template_redirect_fired'     => (bool)did_action('template_redirect'),
                'wp_head_fired'               => (bool)did_action('wp_head'),
                'cached_access_check_result'  => $this->accessCheckResult,
            ],

            // ---- Visitor Cookie ----
            'visitor' => [
                'rmn_visitor_cookie' => $_COOKIE['rmn_visitor'] ?? '(not set)',
                'access_token_set'   => !empty($_COOKIE['access_token']),
            ],

            // ---- Upload Limits (GUEST/BASIC = 5/month) ----
            'upload_limits' => [
                'upload_limit'       => self::UPLOAD_LIMITS,
                'can_upload'         => $this->isLogged() ? $this->canUpload() : false,
                'remaining_uploads'  => $this->isLogged() ? $this->getRemainingUploads() : 0,
            ],

            // ---- Statistics by Type ----
            'statistics' => $this->getViewStatistics(),
        ];

        return $debug;
    }

    /**
     * Debug: render เป็น HTML panel ที่มุมล่างขวา (เฉพาะ admin หรือ ?rmn_debug=1)
     */
    public function renderDebugPanel(): void
    {
        $debug = $this->debug();
        $json = json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        ?>
        <div id="rmn-debug-panel" style="
            position: fixed;
            bottom: 10px;
            right: 10px;
            max-width: 500px;
            max-height: 70vh;
            overflow: auto;
            background: #1e1e2e;
            color: #cdd6f4;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            z-index: 9999999;
            line-height: 1.5;
        ">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid #45475a; padding-bottom:8px;">
                <strong style="color:#f38ba8; font-size:14px;">AccessGuard Debug</strong>
                <button onclick="document.getElementById('rmn-debug-panel').remove()" style="
                    background:#45475a; color:#cdd6f4; border:none; border-radius:6px;
                    padding:2px 8px; cursor:pointer; font-size:12px;
                ">&times;</button>
            </div>
            <pre style="margin:0; white-space:pre-wrap; word-break:break-all;"><?php echo esc_html($json); ?></pre>
        </div>
        <?php
    }

    /**
     * ดึงสถิติการดูโปรไฟล์แบบละเอียด (global across all types)
     */
    public function getViewStatistics(): array
    {
        $viewedProfiles = $this->getViewedProfilesList();
        $viewedCount = count($viewedProfiles);
        $remaining = max(0, $this->monthlyLimit - $viewedCount);

        // แยกตามประเภท
        $byType = [
            self::PROFILE_NURSING => [],
            self::PROFILE_NURSING_HOME => [],
            self::PROFILE_JOB => [],
        ];

        foreach ($viewedProfiles as $key) {
            // ใช้ | เป็น delimiter → แยกได้ถูกต้องแม้ type มี _
            $parts = explode('|', $key, 2);
            if (count($parts) === 2) {
                [$type, $id] = $parts;
                if (isset($byType[$type])) {
                    $byType[$type][] = (int)$id;
                }
            }
        }

        return [
            'total_viewed' => $viewedCount,
            'monthly_limit' => $this->monthlyLimit,
            'remaining' => $remaining,
            'percentage_used' => $this->monthlyLimit > 0 ? round(($viewedCount / $this->monthlyLimit) * 100, 1) : 0,
            'by_type' => $byType,
            'is_limit_reached' => $viewedCount >= $this->monthlyLimit,
            'current_month' => date('Y-m'),
        ];
    }

}

// Auto-init: ให้ AccessGuard hook เข้า template_redirect ตั้งแต่ init
// เพื่อไม่ต้องพึ่ง getInstance() จากไฟล์อื่นที่อาจเรียกสาย
add_action('init', function () {
    AccessGuard::getInstance();
});
