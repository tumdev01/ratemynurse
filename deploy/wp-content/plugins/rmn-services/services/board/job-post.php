<?php

class JobPost {
    protected $allowed = false;
    protected $token;
    public function __construct() {
        $this->token = $this->getUserToken();
        $this->allowed = $this->authenticated();
        add_shortcode('job-post', array($this, 'shortcodeRender'));
    }

    public function getUserToken()
    {
        if (isset($_COOKIE['access_token'])) {
            return sanitize_text_field($_COOKIE['access_token']);
        }
        return null;
    }

    public function authenticated() {
        return !empty($this->token);
    }

    public function enqueueScripts()
    {
        if (!wp_style_is('job-post-style', 'enqueued')) {
            wp_enqueue_style(
                'job-post-style',
                plugin_dir_url(__FILE__) . 'job-post.css',
                [],
                null
            );
        }
        wp_enqueue_style('flatpickr');
        wp_enqueue_script('flatpickr');
        wp_enqueue_script('flatpickr-month-select');
        wp_enqueue_script('flatpickr-th');
    }

    public function shortcodeRender() {
        $this->enqueueScripts();
        if ( !$this->authenticated() ) {
            return <<<HTML
                <div class="flex flex-col gap-[24px] justify-center items-center">
                    <div class="rounded-full w-[60px] h-[60px] bg-red-500 text-white flex justify-center items-center">
                        <svg class="w-10 h-10 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </div>
                    <h2 class="font-medium !text-xl">เข้าสู่ระบบ หรือ ลงทะเบียน</h2>
                    <p class="text-md">ต้องทำการเข้าสู่ระบบ หรือ ลงทะเบียน เพื่อรับสิทธิ์ในการลงประกาศ</p>
                </div>

            HTML;
        }

        $jobBoardUrl = home_url('/job-board/');

        return <<<HTML
            <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
            <style>
                #description-editor .ql-editor { min-height: 351px; }
                .swal2-popup.swal-confirm-modal { width: 380px !important; padding: 40px 24px 24px !important; border-radius: 12px !important; gap: 16px !important; }
                .swal-confirm-modal .swal2-icon { width: 68px !important; height: 68px !important; margin: 0 auto !important; background: #F7F7F7 !important; border: none !important; border-radius: 50px !important; }
                .swal-confirm-modal .swal2-icon .swal2-icon-content { font-size: 44px !important; color: #8C8A94 !important; }
                .swal-confirm-modal .swal2-icon.swal2-info { color: #8C8A94 !important; }
                .swal-confirm-modal .swal2-html-container { margin: 0 !important; padding: 0 !important; }
                .swal-confirm-modal .swal2-close { font-size: 24px !important; color: #667085 !important; top: 18px !important; right: 19px !important; width: 44px !important; height: 44px !important; }
                .swal-actions-full { width: 100% !important; gap: 16px !important; padding: 0 !important; margin: 0 !important; }
                .swal-btn { flex: 1 !important; height: 48px !important; padding: 8px 14px !important; border-radius: 10px !important; font-family: 'IBM Plex Sans Thai', sans-serif !important; font-size: 16px !important; font-weight: 500 !important; line-height: 24px !important; cursor: pointer !important; }
                .swal-btn-confirm { background-color: #286F51 !important; color: #FFFFFF !important; border: none !important; }
                .swal-btn-cancel { background-color: #FFFFFF !important; color: #5A5A5A !important; border: 1px solid #D9D8DC !important; }
            </style>

            <form id="job-post" class="flex flex-col gap-[24px]">
                <input type="hidden" name="profile_id" id="profile_id">

                <div class="form-group flex flex-col gap-[6px]" id="profile-selector" style="display:none;">
                    <label for="profile_select" class="font-medium">โปรไฟล์ที่ลงประกาศ <span class="req">*</span></label>
                    <select class="border rounded-lg px-3 py-2" name="profile_select" id="profile_select">
                        <option class="disabled selected hidden">กรุณาเลือกโปรไฟล์</option>
                    </select>
                </div>

                <div class="form-group flex flex-col gap-[6px]">
                    <label for="name" class="font-medium">ชื่อประกาศ <span class="req">*</span></label>
                    <input class="text-field" type="text" name="name" id="name" placeholder="ฉันหา..." required>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px]">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="service_type" class="font-medium">ประเภทบริการ <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="service_type" id="service_type" required>
                            <option class="disabled selected hidden">ประเภทบริการ</option>
                            <option value="NURSING">พยาบาล / คนดูแล</option>
                            <option value="NURSING_HOME">ศูนย์ดูแล</option>
                        </select>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="care_type" class="font-medium">ประเภทคนดูแล <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="care_type" id="care_type" required="">
                            <option class="disabled selected hidden">เช่น พยาบาลวิชาชีพ คนดูแล อื่นๆ</option>
                            <option value="RN">พยาบาลวิชาชีพ (RN)</option>
                            <option value="PN">ผู้ช่วยพยาบาล (PN)</option>
                            <option value="NA">พนักงานผู้ช่วยการพยาบาล (NA)</option>
                            <option value="CG">คนดูแล (CG)</option>
                            <option value="MAIN">แม่บ้าน (ดูแล ทำงานบ้านได้ด้วย)</option>
                            <option value="ETC">อื่นๆ</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px]">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="hire_type" class="font-medium">ระยะเวลาจ้าง <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="hire_type" id="hire_type" required>
                            <option class="disabled selected hidden">เช่น รายวัน/สัปดาห์/เดือน/ปี</option>
                            <option value="DAILY">รายวัน</option>
                            <option value="MONTHLY">รายเดือน</option>
                        </select>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="hire_rule" class="font-medium">ลักษณะการจ้าง <span class="req">*</span></label>
                        <select class="border rounded-lg px-3 py-2" name="hire_rule" id="hire_rule">
                            <option class="disabled selected hidden">เช่น อยู่ประจำ ค้างคืน ชั่วคราว ไปกลับ</option>
                            <option value="FULL_STAY">อยู่ประจำ ค้างคืน</option>
                            <option value="FULL_ROUND">อยู่ประจำ ไปกลับ</option>
                            <option value="PART_STAY">ชั่วคราว ค้างคืน</option>
                            <option value="PART_ROUND">ชั่วคราว ไปกลับ</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="service_type" class="font-medium">งบประมาณ <span class="req">*</span></label>
                        <input type="number" name="cost" placeholder="฿ งบประมาณ">
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="hire_type" class="font-medium">วันที่เริ่มงาน <span class="req">*</span></label>
                        <input class="border rounded-lg px-3 py-2 form-control" id="start_date" name="start_date" placeholder="เลือกวันที่เริ่มงาน" required tabindex="0" type="text" readonly="readonly">
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="description" class="font-medium">รายละเอียดงาน <span class="req">*</span></label>
                    <div id="description-editor"></div>
                    <input type="hidden" name="description" id="description">
                </div>

                <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#F7FCF9]">
                    <img src="https://ratemynurse.org/wp-content/uploads/2025/08/Group.png" width="19" height="18" loading="lazy">
                    <span class="text-md text-[#286F51] font-semibold">สถานที่ทำงาน</span>
                </span>

                <div class="flex flex-col">
                    <label for="address" class="font-medium">ที่อยู่ <span class="req">*</span></label>
                    <textarea id="address" name="address" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุที่อยู่"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-[15px] md:gap-[32px]">
                    <div class="flex flex-col">
                        <label for="province_id" class="font-medium">จังหวัด <span class="req">*</span></label>
                        <select id="province" name="province_id" class="border rounded-lg px-3 py-2" required></select>
                    </div>
                    <div class="flex flex-col">
                        <label for="district_id" class="font-medium">อำเภอ/เขต <span class="req">*</span></label>
                        <select id="district" name="district_id" class="border rounded-lg px-3 py-2" required></select>
                    </div>
                    <div class="flex flex-col">
                        <label for="sub_district_id" class="font-medium">ตำบล/แขวง <span class="req">*</span></label>
                        <select id="sub_district" name="sub_district_id" class="border rounded-lg px-3 py-2" required></select>
                    </div>
                </div>

                <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#F7FCF9]">
                    <img src="https://ratemynurse.org/wp-content/uploads/2025/09/contact-vector.png" width="19" height="18" loading="lazy">
                    <span class="text-md text-[#286F51] font-semibold">ข้อมูลติดต่อ</span>
                </span>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="phone" class="font-medium">เบอร์โทรศัพท์ <span class="req">*</span></label>
                        <input type="text" name="phone" id="phone" placeholder="เบอร์โทรศัพท์" required>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="email" class="font-medium">อีเมล์</label>
                        <input id="email" name="email" placeholder="อีเมล (ไม่บังคับ)" type="email">
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="facebook" class="font-medium flex flex-row gap-[4px]"><img src="https://ratemynurse.org/wp-content/uploads/2025/09/facebook.webp" width="20" height="20"> Facebook</label>
                        <input type="text" name="facebook" placeholder="Facebook (ไม่บังคับ)">
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="line_id" class="font-medium flex flex-row gap-[4px]"><img src="https://ratemynurse.org/wp-content/uploads/2025/09/line.webp" width="21" height="20"> Line ID (ไลน์ไอดี)</label>
                        <input id="line_id" name="lineid" placeholder="Line ID (ไม่บังคับ)" type="text">
                    </div>
                </div>

                <div class="w-full bg-[#ECECED]" style="height:1px;"></div>

                <div class="flex inline-flex gap-6 justify-end">
                    <button class="btn reset" type="reset">ยกเลิก</button>
                    <button class="btn submit" type="submit">ยืนยัน</button>
                </div>
            </form>

            <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
            <script>
                const quill = new Quill('#description-editor', {
                    theme: 'snow',
                    placeholder: 'อธิบายรายละเอียดงานที่คุณต้องการ เช่น ข้อมูลงาน, จุดประสงค์ของงาน, แผนการทำงาน, จำนวนชั่วโมง, สถานที่ทำงาน, เงื่อนไขการจ้างงาน',

                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                quill.on('text-change', function() {
                    document.getElementById('description').value = quill.root.innerHTML;
                });

                $(function () {
                    flatpickr('#start_date', {
                        yearModifier: 543,
                        altInput: true,
                        altFormat: 'd F B',
                        locale: 'th',
                        dateFormat: 'Y-m-d',
                        defaultDate: null,
                        onChange (_, d) {
                            month = d
                        },
                        onReady (_, d) {
                            month = null
                        },
                    });
                });
                
                    
                document.addEventListener('DOMContentLoaded', async function() {
                    new RMN_LocationSelector({
                        provinceSelector: '#province',
                        districtSelector: '#district',
                        subDistrictSelector: '#sub_district'
                    });

                    // ดึงข้อมูล user เพื่อ set profile_id
                    try {
                        // หน้านี้อาจถูก cache ข้าม visitor ได้ ห้ามเชื่อข้อมูลที่ฝังมากับ HTML ตรงๆ
                        // ต้องดึงสถานะ login ของผู้ใช้ปัจจุบันสดๆ ผ่าน ajax เสมอ — ใช้
                        // RMN_Utils.getCurrentUser() (cache เป็น promise เดียวกันทั้งหน้า) กันยิงซ้ำ
                        const user = await RMN_Utils.getCurrentUser();
                        if (!user) return;

                        const userType = user.user_type;
                        const profileIdInput = document.getElementById('profile_id');
                        const profileSelector = document.getElementById('profile-selector');
                        const profileSelect = document.getElementById('profile_select');

                        if (userType === 'NURSING_HOME' && user.profiles && user.profiles.length > 0) {
                            profileSelector.style.display = '';

                            if (user.profiles.length === 1) {
                                profileIdInput.value = user.profiles[0].id;
                                profileSelect.innerHTML = '<option value="' + user.profiles[0].id + '" selected>' + user.profiles[0].name + '</option>';
                            } else {
                                profileSelect.innerHTML = '<option value="" disabled selected hidden>กรุณาเลือกโปรไฟล์</option>';
                                user.profiles.forEach(function(p) {
                                    profileSelect.innerHTML += '<option value="' + p.id + '">' + p.name + '</option>';
                                });
                            }

                            profileSelect.addEventListener('change', function() {
                                profileIdInput.value = this.value;
                            });
                        } else if (userType === 'NURSING' && user.profile) {
                            profileIdInput.value = user.profile.id;
                        } else if (userType === 'MEMBER' && user.profile) {
                            profileIdInput.value = user.profile.id;
                        }
                    } catch (err) {
                        console.error('Failed to load user profile:', err);
                    }
                });

                const createFrm = document.getElementById('job-post');
                createFrm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    document.getElementById('description').value = quill.root.innerHTML;
                    const formData = new FormData(e.target);
                    const data = Object.fromEntries(formData.entries());

                    // Popup confirm ก่อนส่ง
                    const confirm = await Swal.fire({
                        icon: 'info',
                        showCloseButton: true,
                        html: '<div style="font-family:IBM Plex Sans Thai,sans-serif;font-size:18px;font-weight:600;color:#1F1F1F;line-height:30px;text-align:center;margin-bottom:12px;">กรุณายืนยันเพื่อยอมรับ<br>เงื่อนไขในการลงประกาศ</div>'
                            + '<ul style="text-align:left;font-family:IBM Plex Sans Thai,sans-serif;font-size:14px;font-weight:400;color:#71717A;line-height:20px;list-style:disc;padding-left:20px;margin:0;">'
                            + '<li>ประกาศของคุณจะมีอายุ 30 (นับจากวันที่ลง)</li>'
                            + '<li>หากพบข้อมูลที่ผิดเงื่อนไขหรือขัดต่อการใช้งานระบบ จะลบประกาศของคุณทันที</li>'
                            + '</ul>',
                        showCancelButton: true,
                        showConfirmButton: true,
                        confirmButtonText: 'ยืนยัน',
                        cancelButtonText: 'ยกเลิก',
                        reverseButtons: true,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal-confirm-modal',
                            actions: 'swal-actions-full',
                            confirmButton: 'swal-btn swal-btn-confirm',
                            cancelButton: 'swal-btn swal-btn-cancel',
                        },
                    });

                    if (!confirm.isConfirmed) return;

                    try {
                        const response = await axios.post('/wp-admin/admin-ajax.php?action=job_post', {
                            data: data
                        });

                        if (response.data.success) {
                            // Toast สำเร็จ มุมขวาบน
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                            Toast.fire({
                                icon: 'success',
                                title: 'ลงประกาศสำเร็จ'
                            }).then(() => {
                                window.location.href = "{$jobBoardUrl}";
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: err.response?.data?.data?.message || 'ไม่สามารถบันทึกได้',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    }
                });

            </script>
        HTML;
    }

}

new JobPost();