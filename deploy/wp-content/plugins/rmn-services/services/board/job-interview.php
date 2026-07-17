<?php

class JobInterview {
    private $data = null;
    private $id = null;
    private $endpoint = 'https://services.ratemynurse.org/api/job/';
    private $token = '';
    private $cache_key_prefix = 'job_interview_';
    private $cache_time = 10 * MINUTE_IN_SECONDS;
    private $page_title = null;
    private $dummie = false;

    public function __construct($id = null) {

        // ✅ DEV MODE → force dummy
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->dummie = false;
        }

        if ($this->dummie) {
            $id = 36;
        }

        $this->id = intval($id) ?: null;
        $this->token = $this->get_token();
        $this->set_meta_title();
    }

    private function get_token() {
        return defined('X_INTERNAL_TOKEN') ? X_INTERNAL_TOKEN : '';
    }

    private function fetch_data() {

        if ($this->data !== null) {
            return $this->data;
        }

        if (!$this->id) {
            return null;
        }

        $cache_key = $this->cache_key_prefix . $this->id;

        // ❗ DEV → ไม่ใช้ cache
        if (defined('WP_DEBUG') && WP_DEBUG) {
            delete_transient($cache_key);
        }

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            $this->data = $cached;
            return $this->data;
        }

        if (!$this->token) {
            return null;
        }

        $endpoint = $this->endpoint . $this->id;

        $response = wp_remote_get($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept'        => 'application/json',
                'X-Internal-Token' => $this->token,
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);

        // ❗ ไม่มี job
        if (empty($decoded['results'])) {
            return null;
        }

        $this->data = $decoded;
        set_transient($cache_key, $this->data, $this->cache_time);

        return $this->data;
    }

    public function set_meta_title() {
        add_filter('pre_get_document_title', function ($title) {

            if (!is_page('job-interview')) {
                return $title;
            }

            $job_id = get_query_var('job_id');
            if (!$job_id) {
                return $title;
            }

            $data = ($this->id == intval($job_id))
                ? $this->fetch_data()
                : (new self(intval($job_id)))->fetch_data();

            if (!empty($data['results']['name'])) {
                return $data['results']['name'] . ' - Rate My Nurse';
            }

            return $title;
        });
    }

    public function get_id() {
        return $this->id;
    }

    public function goBackButton() {
        $data = $this->fetch_data();

        $link = home_url('/job-board');

        if (!empty($data['results']['id'])) {
            $link = home_url('/job-info/' . $data['results']['id']);
        }

        return '<a href="'.$link.'" class="go-back-button flex flex-row gap-[8px] items-center">
            <svg class="w-[24px] h-[24px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="2" fill="none" d="m15 19-7-7 7-7"/>
            </svg>ย้อนกลับ
        </a>';
    }

    public function noPermission() {
        return <<<HTML
            <div class="flex flex-col justify-center gap-[24px]">
                <div id="fancy_icon-15-146" class="ct-fancy-icon">
                        <svg id="svg-fancy_icon-15-146"><use xlink:href="#FontAwesomeicon-info-circle"></use></svg>
                    </div>
                <p id="headline-16-146" class="ct-headline">สำหรับการลงประกาศงานเท่านั้นไม่อนุญาตให้โพสต์สิ่งที่ไม่เกี่ยวข้องกับการหาบริการหรือขัดต่อเงื่อนไข หากตรวจพบจะปิดประกาศทันที</p>
                <div class="flex flex-col gap-[24px] justify-center items-center">
                    <div class="rounded-full w-[60px] h-[60px] bg-red-500 text-white flex justify-center items-center">
                        <svg class="w-10 h-10 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"></path>
                        </svg>
                    </div>
                    <h2 class="font-medium !text-xl">เข้าสู่ระบบ หรือ ลงทะเบียน</h2>
                    <p class="text-md">ต้องทำการเข้าสู่ระบบ หรือ ลงทะเบียน เพื่อรับสิทธิ์ในการลงประกาศ</p>
                </div>
            </div>
        HTML;
    }

    // ✅ helper ใช้ตรวจ
    public function hasJob(): bool {
        return !empty($this->fetch_data());
    }

    public function get_form() {
        if(!$this->hasJob()) {
            return $this->noPermission();
        }

        $data = $this->fetch_data();
        //print_r($data);

        if (empty($data['results'])) {
            return '';
        }

        wp_enqueue_style('flatpickr');
        wp_enqueue_script('flatpickr');
        wp_enqueue_script('flatpickr-th');

        $job_id = $data['results']['id'];
        $back_url = "https://ratemynurse.org/job-info/{$job_id}";

        return <<<HTML
        <style>
            .interview-form textarea:focus,
            .interview-form input:focus { outline: none; border-color: #286F51; }
            .interview-form .checkbox-custom { width: 20px; height: 20px; accent-color: #286F51; cursor: pointer; }
        </style>

        <div class="interview-form flex flex-col gap-[24px] text-[16px]">
            <div class="flex flex-col gap-[6px] text-[#5A5A5A]">
                <span class="font-medium text-[#1F1F1F]">ข้อมูลที่ทำให้ผู้รับบริการตัดสินใจบริการของคุณมากขึ้น</span>
                <span class="text-[14px]">เช่น แนะนำตัวเอง/บริการของคุณ ร่วมถึงประสบการณ์ทำงานดูแลด้านการบริการต่างๆ</span>
            </div>

            <input type="hidden" name="job_id" id="interview_job_id" value="{$job_id}">

            <textarea id="interview_message" name="interview" class="w-full min-h-[180px] border border-[#E2E2E4] rounded-lg px-4 py-3 text-[15px] bg-[#FAFAFA] resize-y" placeholder="อธิบายข้อมูลที่ทำให้ผู้รับบริการตัดสินใจบริการของคุณ"></textarea>

            <div class="flex flex-row justify-between items-center">
                <label class="flex flex-row gap-[8px] items-center cursor-pointer">
                    <input type="checkbox" name="attach_profile" class="checkbox-custom" checked>
                    <span class="text-[#1F1F1F]">แนบโปรไฟล์ของฉันด้วย</span>
                </label>
                <span class="text-[13px] text-[#9A9A9A]">ขั้นต่ำ 100 อักษร</span>
            </div>

            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[24px]">
                <div class="w-full md:w-1/2 flex flex-col gap-[6px]">
                    <label class="font-medium text-[#1F1F1F]">งบประมาณที่เสนอ <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9A9A9A]">&#3647;</span>
                        <input type="number" id="interview_price" name="price" class="w-full border border-[#E2E2E4] rounded-lg pl-8 pr-4 py-[10px] bg-white" placeholder="ระบุงบประมาณที่คุณเสนอ" required>
                    </div>
                </div>
                <div class="w-full md:w-1/2 flex flex-col gap-[6px]">
                    <label class="font-medium text-[#1F1F1F]">วันที่เริ่มงาน <span class="text-red-500">*</span></label>
                    <input type="text" id="interview_start_date" name="start_date" class="w-full border border-[#E2E2E4] rounded-lg px-4 py-[10px] bg-white" placeholder="เลือกวันที่เริ่มงาน" readonly required>
                </div>
            </div>

            <div class="flex flex-row gap-[16px] justify-end pt-[8px]">
                <a href="{$back_url}" class="px-[32px] py-[12px] border border-[#E2E2E4] rounded-lg text-[#5A5A5A] font-medium text-center hover:bg-gray-50 no-underline">ยกเลิก</a>
                <button type="button" id="interview_submit" class="px-[32px] py-[12px] bg-[#286F51] text-white rounded-lg font-medium text-center hover:bg-[#1e5a3e] disabled:opacity-50 disabled:cursor-not-allowed" disabled>ยืนยัน</button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                flatpickr('#interview_start_date', {
                    yearModifier: 543,
                    altInput: true,
                    altFormat: 'd F B',
                    locale: 'th',
                    dateFormat: 'Y-m-d',
                });

                const message = document.getElementById('interview_message');
                const price = document.getElementById('interview_price');
                const startDate = document.getElementById('interview_start_date');
                const submitBtn = document.getElementById('interview_submit');

                function validateForm() {
                    const valid = message.value.trim().length >= 100 && price.value && startDate.value;
                    submitBtn.disabled = !valid;
                }

                message.addEventListener('input', validateForm);
                price.addEventListener('input', validateForm);
                startDate.addEventListener('change', validateForm);

                const observer = new MutationObserver(validateForm);
                const altInput = startDate.nextElementSibling;
                if (altInput) observer.observe(altInput, { attributes: true });

                submitBtn.addEventListener('click', async function() {
                    if (message.value.trim().length < 100) {
                        Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูลอย่างน้อย 100 อักษร', confirmButtonColor: '#286F51' });
                        return;
                    }

                    const payload = {
                        job_id: document.getElementById('interview_job_id').value,
                        message: message.value.trim(),
                        attach_profile: document.querySelector('[name="attach_profile"]').checked ? 1 : 0,
                        price: price.value,
                        start_date: startDate.value
                    };

                    try {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'กำลังส่ง...';
                        const response = await axios.post('/wp-admin/admin-ajax.php?action=job_interview', payload);

                        if (response.data.success) {
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: 'นำเสนองานสำเร็จ',
                                icon: 'success',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = '{$back_url}';
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: err.response?.data?.data?.message || 'ไม่สามารถส่งได้',
                            icon: 'error',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#286F51'
                        });
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'ยืนยัน';
                    }
                });
            });
        </script>
HTML;
    }
}

// เพิ่ม Dynamic Data ให้ Oxygen แบบปลอดภัย
function rmn_add_job_interview_dynamic_data($dynamic_data) {
    $id = get_query_var('job_id', 1);
    if (!$id) $id = 1;

    $job = new JobInterview($id);

    $dynamic_data[] = [
        'name' => __('Interview ID', 'rmn'),
        'mode' => 'content',
        'position' => 'Advanced',
        'data' => 'interview_id',
        'handler' => function($atts) use ($job) {
            return $job->get_id();
        },
    ];

    $dynamic_data[] = [
        'name' => __('Interview Back Button', 'rmn'),
        'mode' => 'content',
        'position' => 'Advanced',
        'data' => 'interview_back_button',
        'handler' => function($atts) use ($job) {
            return $job->goBackButton();
        },
    ];

    $dynamic_data[] = [
        'name' => __('Interview Form', 'rmn'),
        'mode' => 'content',
        'position' => 'Advanced',
        'data' => 'interview_form',
        'handler' => function($atts) use ($job) {
            return $job->get_form();
        },
    ];

    return $dynamic_data;
}
add_filter('oxygen_custom_dynamic_data', 'rmn_add_job_interview_dynamic_data');

// Initialize AccessGuard
AccessGuard::getInstance();