<?php

class Authentication {
    public function __construct() {
        add_shortcode( 'rms_authentication', [$this, 'rmsAuthentication'] );
        add_shortcode( 'rmn_mb_navigation', [$this, 'mb_navigation']);
    }

    public function rmsAuthentication() {
        wp_enqueue_style('flatpickr');
        wp_enqueue_script('flatpickr');
        wp_enqueue_script('flatpickr-month-select');
        wp_enqueue_script('flatpickr-th');

        $jobBoardUrl = home_url('/job-board/');

        $html = <<<HTML
            <style>
                input:focus, textarea:focus {border: 1px solid #D9D8DC;}
                /* สีปกติ (เลือกแล้ว) */
                select:valid {
                    color: #000;
                }
                .ts-wrapper {
                    border: none !important;
                    padding: 0 !important;
                }
                .ts-control {
                    padding-top: 8px;
                    padding-bottom: 8px;
                    min-height: 43px !important;
                    border: 1px solid #D9D8DC;
                    border-radius: 8px;
                }
                .ts-dropdown .option {font-size:14px;}
                .ts-dropdown .ts-control input {
                    font-size: 14px;
                    line-height: 1.75rem;
                }
                .req {color: red;}

                .medical_condition_choice.selected, .history_of_drug_allergy_choice.selected {
                    background-color: #286F51;
                    color: #FFF;
                }
                .flatpickr-calendar, .flatpickr-current-month {font-size: 13px;}

                .authen-loading-overlay {
                    display: none;
                    position: fixed;
                    inset: 0;
                    background: rgba(255,255,255,0.7);
                    z-index: 100000;
                    align-items: center;
                    justify-content: center;
                }
                .authen-loading-overlay.active {
                    display: flex;
                }
                .authen-loading-overlay .authen-spinner {
                    width: 36px;
                    height: 36px;
                    border: 4px solid #D9D8DC;
                    border-top-color: #286F51;
                    border-radius: 50%;
                    animation: authen-spin 0.8s linear infinite;
                }
                @keyframes authen-spin {
                    to { transform: rotate(360deg); }
                }

                @media only screen and (min-width: 769px) {
                    #div_block-684-21 {
                        justify-content: flex-start;
                    }
                    #shortcode-689-21 {
                        height: 100%;
                    }
                    .authen_wrapper {
                        height: 100%;
                        display: flex;
                    }
                    #authen,
                    #otpConfirm,
                    #userType,
                    #serviceType {
                        display: flex;
                        align-self: center;
                        align-items: center;
                        margin: 0 auto;
                    }
                    #memberRegistrationTab,
                    #providerRegistrationTab,
                    #nursingRegistrationTab {
                        flex: 1;
                        overflow-y: auto;
                        scrollbar-width: thin;
                        scrollbar-color: rgba(0, 0, 0, 0.35) transparent;
                    }
                    #memberRegistrationTab::-webkit-scrollbar,
                    #providerRegistrationTab::-webkit-scrollbar,
                    #nursingRegistrationTab::-webkit-scrollbar {
                        width: 6px;
                    }
                    #memberRegistrationTab::-webkit-scrollbar-track,
                    #providerRegistrationTab::-webkit-scrollbar-track,
                    #nursingRegistrationTab::-webkit-scrollbar-track {
                        background: transparent;
                    }
                    #memberRegistrationTab::-webkit-scrollbar-thumb,
                    #providerRegistrationTab::-webkit-scrollbar-thumb,
                    #nursingRegistrationTab::-webkit-scrollbar-thumb {
                        background-color: rgba(0, 0, 0, 0.35);
                        border-radius: 3px;
                    }
                    #memberRegistrationTab::-webkit-scrollbar-thumb:hover,
                    #providerRegistrationTab::-webkit-scrollbar-thumb:hover,
                    #nursingRegistrationTab::-webkit-scrollbar-thumb:hover {
                        background-color: rgba(0, 0, 0, 0.5);
                    }
                    #memberRegistrationTab > .action,
                    #providerRegistrationTab > .action,
                    #nursingRegistrationTab > .action {
                        position: sticky;
                        top: 0;
                        background: #fff;
                        z-index: 5;
                        padding-block: 8px;
                    }
                }

                .tab-content {
                    display: none;
                }
                .tab_content.active_tab {
                    display: flex;
                }
                .tab_title.active_tab {
                    color: #286F51;
                    border-bottom: 2px solid #215B44;
                }
                
                @media only screen and (max-width: 768px) {
                    #div_block-679-21 {
                        max-height: unset;
                        border-radius: 0;
                    }
                    .mb_icon {
                        width: 24px;
                        height: 24px;
                        background-repeat: no-repeat;
                        background-position: center;
                    }
                    #mb_home {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_home.webp);
                        }
                    }
                    #mb_home.current_active, #mb_home:hover {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_home_active.webp);
                        }
                    }
                    #mb_favorites {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2025/10/heart.webp);
                        }
                    }
                    #mb_favorites.current_active, #mb_favorites:hover {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_Fav_active.webp);
                        }
                    }

                    #mb_board {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_board.png);
                        }
                    }
                    #mb_board.current_active, #mb_board:hover {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_board_active.png);
                        }
                    }

                    #mb_profile_authentication {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_user.png);
                        }
                    }
                    #mb_profile_authentication.current_active, #mb_profile_authentication:hover {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_user_active.png);
                        }
                    }
                    #mb_search {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_Magnifer.webp);
                        }
                    }
                    #mb_search.current_active, #mb_search:hover {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_Magnifer_active.webp);
                        }
                    }

                    #mb_overview {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_Pie_Chart.webp);
                        }
                    }
                    #mb_overview.current_active, #mb_overview:hover {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_Pie_Chart_active.webp);
                        }
                    }

                    #mb_profile {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_user.png);
                        }
                    }
                    #mb_profile.current_active, #mb_profile:hover {
                        .mb_icon {
                            background-image: url(https://ratemynurse.org/wp-content/uploads/2026/01/MB_user_active.png);
                        }
                    }
                    
                    #mb_contacts {
                        .mb_icon {
                            width: 24px;
                            height: 24px;
                            background-image: url('https://ratemynurse.org/wp-content/uploads/2025/10/calendar.webp');
                        }
                    }
                }

                .ts-dropdown {
                    z-index: 999999 !important;
                }
            </style>
            <div class="authen_wrapper">
                <div id="authenLoadingOverlay" class="authen-loading-overlay">
                    <div class="authen-spinner"></div>
                </div>
                <!-- Login or Register -->
                <div id="authen" class="flex flex-column gap-6 step max-w-[452px] mx-auto" data-step="1">
                    <div class="action flex flex-row flex-between w-full">
                        <span></span>
                        <span class="close">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                        </span>
                    </div>    
                    <div class="flex flex-col gap-[8px] justify-center">
                        <h2 class="mx-auto text-center text-[#286F51] text-[20px]">เข้าสู่ระบบ</h2>
                        <div class="w-[50px] h-[5px] bg-[#286F51] rounded-[19px] mx-auto"></div>
                    </div>
                    <div class="telphone w-full">
                        <label for="tel_number text-[#5A5A5A]">เบอร์โทรศัพท์</label>
                        <div class="frm_group flex flex-row items-center tel_fields px-[6px] py-[8px]">
                            <select name="tel_zone" id="telzone">
                                <option value="+66" selected>TH</option>
                            </div>
                            <input type="number" id="tel_number" maxlength="10" class="!border-0 !outline-0" placeholder="กรอกเบอร์โทรศัพท์">
                        </div>
                        
                    </div>
                    <button class="btn loginBtn" disabled>เข้าสู่ระบบ</button>
                    <div class="divider flex flex-row items-center">
                        <span class="line"></span>
                        หรือ
                        <span class="line"></span>
                    </div>
                    <div class="social_login flex flex-row gap-6 items-center flex-between hidden" style="display:none;">
                        <button id="google-login" class="social btn flex flex-row items-center justify-center"><img src="https://ratemynurse.org/wp-content/uploads/2025/08/Google.png" width="24" height="25" loading="lazy"> Google</button>
                        <button id="line-login" class="social btn flex flex-row items-center justify-center"><img src="https://ratemynurse.org/wp-content/uploads/2025/08/line.png" width="25" height="25" loading="lazy"> Line</button>
                    </div>
                    <div>ยังไม่มีบัญชีผู้ใช้ <b><span id="registerBtn" style="cursor:pointer">สมัครสมาชิก</span></b></div>
                </div>

                <!-- OTP -->
                <div id="otpConfirm" class="flex flex-column gap-6 step" data-step="2" style="display:none;">
                    <div class="action flex flex-row flex-between w-full">
                        <span class="back" data-to="authen">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/> </svg>
                            <span style="padding-top:3px;line-height:1;">ย้อนกลับ</span>
                        </span>
                        
                        <span class="close">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                        </span>
                    </div>
                    <h2 class="mx-auto">ยืนยันรหัส OTP</h2>
                    <p class="mx-auto text-center">เราได้ส่งรหัส OTP 6 หลักไปยังเบอร์ <span class="tel_txt">+66 954405402</span> ทาง SMS กรุณากรอกรหัสเพื่อยืนยันตัวตนของคุณ</p>
                    <div class="otp-wrap flex flex-column gap-32">
                        <div class="otp-group flex flex-row gap-2">
                            <input class="otp-code" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" name="otp[0][]">
                            <input class="otp-code" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" name="otp[1][]">
                            <input class="otp-code" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" name="otp[2][]">
                            <input class="otp-code" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" name="otp[3][]">
                            <input class="otp-code" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" name="otp[4][]">
                        </div>
                        <span>ยังไม่ได้รับใช่ไหม รับอีกครั้งใน <span class="countdown" data-timer="90">(1:30)</span> <span class="loginBtn" onclick="requestOtp()" style="padding: 0;text-decoration: underline;color: blue;">ขอรับรหัสอีกครั้ง</span></span>
                        <button class="btn" id="otp_confirm" disabled>ยืนยัน</button>
                    </div>
                </div>

                <!-- Register choose user type Member Or Service provider -->
                <div id="userType" class="flex flex-column gap-6 step max-w-[452px] mx-auto" data-step="3" style="display:none">
                    <div class="action flex flex-row flex-between w-full">
                        <span class="back" data-to="authen">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/> </svg>
                            <span style="padding-top:3px;line-height:1;">ย้อนกลับ</span>
                        </span>
                        
                        <span class="close">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                        </span>
                    </div>
                    <div class="flex flex-col gap-[8px] justify-center">
                        <h2 class="mx-auto text-[#286F51] text-center">กรุณาเลือกประเภท</h2>
                        <div class="w-[50px] h-[5px] bg-[#286F51] rounded-[19px] mx-auto"></div>
                        <p class="mx-auto text-center">เลือกประเภทผู้ใช้งานของคุณ เพื่อลงทะเบียน</p>
                    </div>
                    
                    <div class="user_type_wrap flex flex-column gap-6">
                        <a id="memberUser" class="flex flex-row items-center flex-between cursor-pointer">
                            <img src="https://ratemynurse.org/wp-content/uploads/2025/08/Frame-1000005061.webp" width="50" height="50" loading="lazy">
                            <div>
                                <h2>ผู้ใช้บริการ</h2>
                                <span>ฉันต้องการหาพยาบาลดูแลหรือบ้านพักผู้สูงอายุ</span>
                            </div>
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/> </svg>
                        </a>
                        <a id="serviceUser" class="flex flex-row items-center flex-between cursor-pointer">
                            <img src="https://ratemynurse.org/wp-content/uploads/2025/08/Frame-37415.webp" width="50" height="50" loading="lazy">
                            <div>
                                <h2>ผู้ให้บริการ</h2>
                                <span>ฉันเป็นพยาบาลหรือบ้านพักที่ต้องการลงประกาศ</span>
                            </div>
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/> </svg>
                        </a>
                    </div>
                </div>

                <!-- Service Type Nursing / NursingHome -->
                <div id="serviceType" class="flex flex-column gap-6 step" data-step="4" style="display:none">
                    <div class="action flex flex-row flex-between w-full">
                        <span class="back" data-to="userType">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/> </svg>
                            <span style="padding-top:3px;line-height:1;">ย้อนกลับ</span>
                        </span>
                        
                        <span class="close">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                        </span>
                    </div>
                    <div class="flex flex-col gap-[8px] items-center">
                        <h2 class="mx-auto items-center text-[#286F51] text-center">กรุณาเลือกประเภทบริการของคุณ</h2>
                        <div class="w-[50px] h-[5px] bg-[#286F51] rounded-[19px]"></div>
                        <p class="mx-auto text-center">เลือกรูปแบบการให้บริการที่คุณต้องการลงประกาศ</p>
                    </div>
                    <div class="user_type_wrap flex flex-column gap-6 max-w-[452px] mx-auto">
                        <a id="nursingUser" class="flex flex-row items-center flex-between cursor-pointer">
                            <img src="https://ratemynurse.org/wp-content/uploads/2025/08/nurse.webp" width="50" height="50" loading="lazy">
                            <div>
                                <h2>พยาบาล / ผู้ดูแล</h2>
                                <span>ฉันคือพยาบาล / ผู้ดูแล ที่พร้อมให้บริการดูแลผู้สูงอายุที่บ้าน</span>
                            </div>
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/> </svg>
                        </a>
                        <a id="providerUser"  class="flex flex-row items-center flex-between cursor-pointer">
                            <img src="https://ratemynurse.org/wp-content/uploads/2025/08/nursinghome.webp" width="50" height="50" loading="lazy">
                            <div>
                                <h2>บ้านพักดูแลผู้สูงอายุ</h2>
                                <span>ฉันคือสถานดูแลผู้สูงอายุหรือบ้านพักที่พร้อมเปิดรับผู้เข้าพัก</span>
                            </div>
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/> </svg>
                        </a>
                    </div>
                    <p class="text-center">กรุณาตรวจสอบให้แน่ใจก่อนยืนยัน<br/>เนื่องจากไม่สามารถเปลี่ยนแปลงประเภทการให้บริการได้ในภายหลัง</p>
                </div>

                <div id="memberRegistrationTab" class="flex flex-column gap-5 step" data-step="5" style="display:none">
                    <div class="action flex flex-row flex-between w-full">
                        <span class="back" data-to="userType">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/> </svg>
                            <span style="padding-top:3px;line-height:1;">ย้อนกลับ</span>
                        </span>
                        
                        <span class="close">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 mb-[24px]">
                        <h2 class="mx-auto text-[#286F51] font-medium text-center">สมัครสมาชิกกับ Rate My Nurse</h2>
                        <p class="mx-auto text-center">กรุณากรอกข้อมูลให้ครบถ้วน เพื่อสร้างบัญชีของคุณ</p>
                        <span class="divider w-[50px] h-[5px] rounded-lg bg-[#286F51] mx-auto"></span>
                    </div>
                    <form id="memberRegisFrm" action="" class="flex flex-col gap-5">
                        <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                            <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                <label for="fname">ชื่อจริง</label>
                                <input required="" type="text" name="fname" id="fname" placeholder="ชื่อจริง" class="border rounded-lg px-3 py-2">
                            </div>
                            <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                <label for="lname">นามสกุล</label>
                                <input required="" type="text" name="lname" id="lname" placeholder="นามสกุล" class="!border rounded-lg px-3 py-2">
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                            <div class="form-group w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                <label for="phone">เบอร์โทรศัพท์</label>
                                <input required="" type="text" name="phone" id="phone" placeholder="เบอร์โทรศัพท์ 10 หลักและต้องขึ้นต้นด้วย 0" maxlength="10" class="!border rounded-lg px-3 py-2">
                                <span class="error text-red-300 text-sm"></span>
                            </div>
                            <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                <label for="email">อีเมล์</label>
                                <input required="" type="email" name="email" id="email" placeholder="อีเมล์" class="!border rounded-lg px-3 py-2">
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                            <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                <label for="cardid">หมายเลขบัตรประชาชน 13 หลัก</label>
                                <input required="" type="text" name="cardid" id="cardid" placeholder="หมายเลขบัตรประชาชน 13 หลัก" maxlength="13" class="border rounded-lg px-3 py-2">
                                <span class="error text-red-400 text-sm hidden">หมายเลขบัตรประชาชนไม่ถูกต้อง</span>
                            </div>
                        </div>

                        <div class="h-[1px] w-full bg-[#ECECED]"></div>

                        <div class="p-[12px] gap-[12px] bg-[#FBFBFB] rounded-lg flex flex-col">
                            <span>กรุณาอ่านและยอมรับ ข้อตกลงในการใช้งาน และ นโยบายความเป็นส่วนตัว ก่อนดำเนินการต่อ</span>

                            <div class="flex flex-row gap-[8px]">
                                <input type="checkbox" id="agreeTerms" class="rounded-md border border-[#D9D8DC]">
                                <span class="text-[#1F1F1F]">ฉันยอมรับ</span>
                                <a href="#" class="text-[#3262CA] font-semibold underline">ข้อตกลงและเงื่อนไขการใช้งาน</a>
                                <span>และ</span>
                                <a href="#" class="text-[#3262CA] font-semibold underline">นโยบายความเป็นส่วนตัว</a>
                                
                            </div>

                            <div class="flex flex-row gap-[8px]">
                                <input type="checkbox" id="agreeNews" class="rounded-md border border-[#D9D8DC]">
                                <span class="text-[#1F1F1F]">ยินยอมรับข่าวสาร สิทธิประโยชน์ และข้อมูลอัปเดตผ่านทางอีเมล SMS จาก Rate My Nurse</span>
                            </div>

                            <span>ข้าพเจ้ารับทราบว่า ความยินยอมดังกล่าวข้าพเจ้าสามารถยกเลิกได้ตลอดเวลา ข้าพเจ้าเข้าใจและ
                                    ตกลงในข้อกำหนดและเงื่อนไข รวมทั้งเข้าใจถึงเรื่องการเก็บ การใช้ และการเปิดเผยข้อมูลส่วนบุคคลแล้ว</span>
                        </div>

                        <div class="h-[1px] w-full bg-[#ECECED]"></div>

                        <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section justify-between">
                            <a class="close text-[#5A5A5A] font-medium w-full max-w-[172px] h-[48px] border border-[#D9D8DC] rounded-lg text-center leading-[48px]">ยกเลิก</a>
                            <button id="memberCreate" class="text-white font-medium w-[200px] h-[48px] border border-[#286F51] bg-[#286F51] rounded-lg auth-btn disabled">สมัครสมาชิก</button>
                        </div>
                    </form>
                    
                </div>

                <div id="providerRegistrationTab" class="flex flex-column gap-5 step" data-step="7" style="display:none">
                    <div class="action flex flex-row flex-between w-full">
                        <span class="back" data-to="serviceType">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/> </svg>
                            <span style="padding-top:3px;line-height:1;">ย้อนกลับ</span>
                        </span>
                        
                        <span class="close">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 mb-[24px]">
                        <h2 class="mx-auto text-[#286F51] font-medium text-center">สมัครสมาชิกกับ Rate My Nurse</h2>
                        <p class="mx-auto text-center">กรุณากรอกข้อมูลให้ครบถ้วน เพื่อสร้างบัญชีของคุณ</p>
                        <span class="divider w-[50px] h-[5px] rounded-lg bg-[#286F51] mx-auto"></span>
                    </div>
                    <div class="flex flex-col gap-5">
                        <div class="subTab-1 flex flex-column gap-[24px]">
                            <form id="providerRegisFrm" action="" class="flex flex-col gap-5">
                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full flex flex-col gap-2">
                                        <label for="name">ชื่อศูนย์ดูแลผู้สูงอายุ</label>
                                        <input required="" type="text" name="name" id="providername" placeholder="ระบุชื่อศูนย์" class="!border rounded-lg px-3 py-2">
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label for="main_phone">เบอร์โทรศัพท์หลัก</label>
                                        <input required type="text" name="main_phone" id="main_phone" placeholder="เบอร์โทรศัพท์หลัก" maxlength="10" class="!border rounded-lg px-3 py-2">
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label for="res_phone">เบอร์โทรศัพท์สำรอง</label>
                                        <input type="text" name="res_phone" id="res_phone" placeholder="เบอร์โทรศัพท์สำรอง" maxlength="10" class="!border rounded-lg px-3 py-2">
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label for="email">อีเมล <span class="req">*</span></label>
                                        <input required type="email" name="email" id="provideremail" placeholder="example@gmail.com" class="border rounded-lg px-3 py-2">
                                    </div>
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label for="facebook">Facebook Page (ถ้ามี)</label>
                                        <input type="text" name="facebook" id="facebook" placeholder="ชื่อ Facebook Page (ถ้ามี)" class="!border rounded-lg px-3 py-2">
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full flex flex-col gap-2">
                                        <label for="website">เว็ปไซต์ (ถ้ามี)</label>
                                        <input type="text" name="website" id="website" placeholder="https://www.example.com (ถ้ามี)" class="border rounded-lg px-3 py-2">
                                    </div>
                                </div>

                                <div class="h-[1px] w-full bg-[#ECECED]"></div>

                                <div class="p-[12px] gap-[12px] bg-[#FBFBFB] rounded-lg flex flex-col">
                                    <span>กรุณาอ่านและยอมรับ ข้อตกลงในการใช้งาน และ นโยบายความเป็นส่วนตัว ก่อนดำเนินการต่อ</span>

                                    <div class="flex flex-row gap-[8px]">
                                        <input required type="checkbox" id="p_agreeTerms" class="rounded-md border border-[#D9D8DC]">
                                        <span class="text-[#1F1F1F]">ฉันยอมรับ</span>
                                        <a href="#" class="text-[#3262CA] font-semibold underline">ข้อตกลงและเงื่อนไขการใช้งาน</a>
                                        <span>และ</span>
                                        <a href="#" class="text-[#3262CA] font-semibold underline">นโยบายความเป็นส่วนตัว</a>
                                        
                                    </div>

                                    <div class="flex flex-row gap-[8px]">
                                        <input required type="checkbox" id="p_agreeNews" class="rounded-md border border-[#D9D8DC]">
                                        <span class="text-[#1F1F1F]">ยินยอมรับข่าวสาร สิทธิประโยชน์ และข้อมูลอัปเดตผ่านทางอีเมล SMS จาก Rate My Nurse</span>
                                    </div>

                                    <div class="flex flex-row flex-nowrap items-start !items-start gap-[8px]" style="flex-wrap: nowrap;">
                                        <input required type="checkbox" id="p_truth" class="rounded-md border border-[#D9D8DC] mt-[3px]">
                                        <span class="text-[#1F1F1F]">ฉันรับรองว่าข้อมูลทั้งหมดที่กรอกเป็นความจริง และหากพบว่าเป็นเท็จฉันยินยอมให้ยกเลิกการสมัคร</span>
                                    </div>

                                    <span>ข้าพเจ้ารับทราบว่า ความยินยอมดังกล่าวข้าพเจ้าสามารถยกเลิกได้ตลอดเวลา ข้าพเจ้าเข้าใจและ
                                            ตกลงในข้อกำหนดและเงื่อนไข รวมทั้งเข้าใจถึงเรื่องการเก็บ การใช้ และการเปิดเผยข้อมูลส่วนบุคคลแล้ว</span>
                                </div>

                                <div class="h-[1px] w-full bg-[#ECECED]"></div>

                                <div class="grid grid-cols-2 md:flex md:flex-row gap-[24px] md:justify-end justify-between">
                                    <a class="close text-[#5A5A5A] font-medium w-full max-w-[172px] h-[48px] border border-[#D9D8DC] rounded-lg text-center leading-[48px]">
                                        <span class="h-[24px] leading-[24px]">ยกเลิก</span>
                                    </a>
                                    <button id="nextTab" class="next-btn flex flex-row gap-[12px] justify-center items-center text-white font-medium w-full max-w-[172px] h-[48px] border border-[#286F51] bg-[#286F51] rounded-lg auth-btn disabled">
                                        <span class="h-[24px] leading-[24px]">ถัดไป</span>
                                        <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="subTab-2 flex flex-column gap-[24px]" style="display:none">
                            <form id="providerProfileFrm" class="flex flex-col gap-5">
                                <input type="hidden" name="user_id" id="providerUserId">
                                <div class="flex flex-col">
                                    <label for="address">ที่อยู่ <span class="req">*</span></label>
                                    <textarea required id="address" name="address" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="ระบุที่อยู่"></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                                    <div class="flex flex-col">
                                        <label for="weight">จังหวัด <span class="req">*</span></label>
                                        <select id="province" name="province_id" class="border rounded-lg px-3 py-2" required>
                                        </select>
                                    </div>
                                    <div class="flex flex-col">
                                        <label for="height">อำเภอ/เขต <span class="req">*</span></label>
                                        <select id="district" name="district_id" class="border rounded-lg px-3 py-2" required>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                                    <div class="flex flex-col">
                                        <label for="weight">ตำบล/แขวง <span class="req">*</span></label>
                                        <select id="sub_district" name="sub_district_id" class="border rounded-lg px-3 py-2" required>
                                        </select>
                                    </div>
                                    <div class="flex flex-col">
                                        <label for="zipcode">รหัสไปรษณีย์ <span class="req">*</span></label>
                                        <input required type="text" name="zipcode" id="zipcode" placeholder="รหัสไปรษณีย์"
                                            class="!border rounded-lg px-3 py-2"/>
                                    </div>
                                </div>

                                <div class="h-[1px] w-full bg-[#ECECED]"></div>

                                <div class="grid grid-cols-2 md:flex md:flex-row gap-[24px] md:justify-end justify-between">
                                    <a class="close text-[#5A5A5A] font-medium w-full max-w-[172px] h-[48px] border border-[#D9D8DC] rounded-lg text-center leading-[48px]">ยกเลิก</a>
                                    <button id="providerCreate" class="next-btn flex flex-row gap-[12px] justify-center items-center text-white font-medium w-full max-w-[172px] h-[48px] border border-[#286F51] bg-[#286F51] rounded-lg auth-btn disabled">สมัครสมาชิก</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="nursingRegistrationTab" class="flex flex-column gap-5 step" data-step="8" style="display:none">
                    <div class="action flex flex-row flex-between w-full">
                        <span class="nursingback" data-to="subTab-1">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/> </svg>
                            <span style="padding-top:3px;line-height:1;">ย้อนกลับ</span>
                        </span>
                        <span class="close">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 mb-[24px]">
                        <h2 class="mx-auto text-[#286F51] font-medium text-center">สมัครสมาชิกกับ Rate My Nurse</h2>
                        <p class="mx-auto text-center">กรุณากรอกข้อมูลให้ครบถ้วน เพื่อสร้างบัญชีของคุณ</p>
                        <span class="divider w-[50px] h-[5px] rounded-lg bg-[#286F51] mx-auto"></span>
                    </div>
                    <div class="flex flex-col gap-5">
                        <form id="nursingRegisFrm" action="" class="flex flex-col gap-5">
                            <div class="subTab-1 flex flex-column gap-[24px]">
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold">รูปถ่าย <span class="req">*</span></label>
                                    <div id="nursingAvatarUpload" class="border border-dashed rounded-lg h-[130px] flex justify-center items-center cursor-pointer">
                                        <div class="flex flex-row gap-[16px] justify-center items-center">
                                            <img id="nursingAvatarPreview" src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                                            <div class="flex flex-col">
                                                <label class="text-sm font-semibold">คลิกเพื่ออัปโหลดไฟล์</label>
                                                <span class="text-xs">รองรับ .JPG, .PNG | ขนาดไม่เกิน 5 MB</span>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" id="nursingProfilePhoto" name="profile_photo" accept="image/*" style="display:none">
                                    <label class="error text-xs text-red-600"></label>
                                </div>
                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label for="firstname">ชื่อจริง <span class="req">*</span></label>
                                        <input required type="text" name="firstname" id="nursingFirstName" placeholder="ชื่อจริง" class="!border rounded-lg px-3 py-2">
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label for="lastname">นามสกุล <span class="req">*</span></label>
                                        <input required type="text" name="lastname" id="nursingLastName" placeholder="นามสกุล" class="!border rounded-lg px-3 py-2">
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label class="font-semibold" for="nickname">ชื่อเล่น <span class="req">*</span></label>
                                        <input required type="text" name="nickname" id="nursingNickname" placeholder="ชื่อเล่น" class="!border rounded-lg px-3 py-2">
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label class="font-semibold" for="gender">เพศ <span class="req">*</span></label>
                                        <select name="gender" id="nursingGender" class="rounded-lg px-3 py-2 border border-[#e5e7eb]" required>
                                            <option value="">เลือกเพศ</option>
                                            <option value="MALE">ชาย</option>
                                            <option value="FEMALE">หญิง</option>
                                            <option value="OTHER">อื่นๆ</option>
                                        </select>
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full flex flex-col gap-2">
                                        <label class="font-semibold" for="care_type">ประเภทผู้ให้บริการ <span class="req">*</span></label>
                                        <select name="care_type" id="nursingCareType" class="rounded-lg px-3 py-2 border border-[#e5e7eb]" required>
                                            <option value="">เลือกประเภทผู้ให้บริการ</option>
                                            <option value="RN">พยาบาลวิชาชีพ (RN)</option>
                                            <option value="PN">ผู้ช่วยพยาบาล (PN)</option>
                                            <option value="NA">พนักงานผู้ช่วยการพยาบาล (NA)</option>
                                            <option value="CG">คนดูแล</option>
                                            <option value="MAID">แม่บ้าน (ดูแล ทำงานบ้านได้ด้วย)</option>
                                        </select>
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label class="font-semibold" for="phone">เบอร์โทรศัพท์ <span class="req">*</span></label>
                                        <input required type="text" name="phone" id="nursingPhone" placeholder="เบอร์โทรศัพท์" maxlength="10" class="!border rounded-lg px-3 py-2">
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col gap-2">
                                        <label class="font-semibold" for="email">อีเมล <span class="req">*</span></label>
                                        <input required type="email" name="email" id="nursingEmail" placeholder="อีเมล" class="!border rounded-lg px-3 py-2">
                                        <label class="error text-xs text-red-600"></label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-[15px] md:gap-[32px]">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold" for="date_of_birth">วัน/เดือน/ปีเกิด <span class="req">*</span></label>
                                        <div class="border rounded-lg px-3 py-2 flex flex-row gap-[8px] !flex-nowrap">
                                            <input required type="text" name="date_of_birth" id="nursingBirthDate" placeholder="วว/ดด/ปปปป"
                                                class="!border-0 w-full"/>
                                            <img src="https://ratemynurse.org/wp-content/uploads/2025/10/calendar-1.webp" width="16" height="16">
                                        </div>
                                        <label class="error text-xs text-red-600"></label>
                                    </div>

                                    <div class="flex flex-col" style="display:none">
                                        <label class="font-semibold" for="blood">กรุ๊ปเลือด (ถ้ามี)</label>
                                        <select name="blood" id="nursingBlood" class="rounded-lg px-3 py-2 border border-[#e5e7eb]">
                                            <option value="">กรุ๊ปเลือด</option>
                                            <option value="A">A (เอ)</option>
                                            <option value="B">B (บี)</option>
                                            <option value="AB">AB (เอบี)</option>
                                            <option value="O">O (โอ)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="h-[1px] w-full bg-[#ECECED]"></div>

                                <div class="grid grid-cols-2 md:flex md:flex-row gap-[24px] md:justify-end justify-between">
                                    <a class="close text-[#5A5A5A] font-medium w-full max-w-[172px] h-[48px] border border-[#D9D8DC] rounded-lg text-center leading-[48px]">
                                        <span class="h-[24px] leading-[24px]">ยกเลิก</span>
                                    </a>
                                    <button id="nextTab" class="next-btn flex flex-row gap-[12px] justify-center items-center text-white font-medium w-full max-w-[172px] h-[48px] border border-[#286F51] bg-[#286F51] rounded-lg auth-btn disabled">
                                        <span class="h-[24px] leading-[24px]">ถัดไป</span>
                                        <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="subTab-2 flex flex-column gap-[24px]" style="display:none">

                                    <div class="flex flex-col">
                                        <label class="font-semibold" for="">คุณมีโรคประจำตัวหรือไม่ ? <span class="req">*</span></label>
                                        <span>กรุณาตอบคำถามหากมีให้ระบุข้อมูล</span>
                                        <input type="hidden" name="medical_condition" id="medical_condition" value="">
                                        <div class="grid grid-cols-3 gap-[16px] mt-[16px]">
                                            <div class="medical_condition_choice border border-[#ECECED] rounded-[12px] h-[85px] text-center leading-[85px] cursor-pointer" data-value="yes">มี</div>
                                            <div class="medical_condition_choice border border-[#ECECED] rounded-[12px] h-[85px] text-center leading-[85px] cursor-pointer" data-value="no">ไม่มี</div>
                                            <div class="medical_condition_choice border border-[#ECECED] rounded-[12px] h-[85px] text-center leading-[85px] cursor-pointer" data-value="null">ไม่ระบุ</div>
                                        </div>
                                        <div id="medical_condition_wrap" class="hidden flex flex-col mt-[16px]">
                                            <label class="font-semibold" for="">รายละเอียด</label>
                                            <textarea id="medical_condition_detail" name="medical_condition_detail" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เช่นเบาหวาน, ความดันโลหิต"></textarea>
                                        </div>
                                        <label class="error text-xs text-red-600"></label>
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="font-semibold" for="">คุณมีประวัติแพ้ยาหรือไม่ ? <span class="req">*</span></label>
                                        <span>กรุณาตอบคำถามหากมีให้ระบุข้อมูล</span>
                                        <input type="hidden" name="history_of_drug_allergy" id="history_of_drug_allergy" value="">
                                        <div class="grid grid-cols-3 gap-[16px] mt-[16px]">
                                            <div class="history_of_drug_allergy_choice border border-[#ECECED] rounded-[12px] h-[85px] text-center leading-[85px] cursor-pointer" data-value="yes">มี</div>
                                            <div class="history_of_drug_allergy_choice border border-[#ECECED] rounded-[12px] h-[85px] text-center leading-[85px] cursor-pointer" data-value="no">ไม่มี</div>
                                            <div class="history_of_drug_allergy_choice border border-[#ECECED] rounded-[12px] h-[85px] text-center leading-[85px] cursor-pointer" data-value="null">ไม่ระบุ</div>
                                        </div>
                                        <div id="history_of_drug_allergy_wrap" class="hidden flex flex-col mt-[16px]">
                                            <label class="font-semibold" for="">รายละเอียด</label>
                                            <textarea id="history_of_drug_allergy_detail" name="history_of_drug_allergy_detail" class="min-h-[90px] border rounded-lg px-3 py-2" placeholder="เช่นเบาหวาน, ความดันโลหิต"></textarea>
                                        </div>
                                        <label class="error text-xs text-red-600"></label>
                                    </div>

                                    <div class="p-[12px] gap-[12px] bg-[#FBFBFB] rounded-lg flex flex-col">
                                        <span>กรุณาอ่านและยอมรับ ข้อตกลงในการใช้งาน และ นโยบายความเป็นส่วนตัว ก่อนดำเนินการต่อ</span>

                                        <div class="flex flex-row gap-[8px]">
                                            <input required type="checkbox" id="nursing_agreeTerms" class="rounded-md border border-[#D9D8DC]">
                                            <span class="text-[#1F1F1F]">ฉันยอมรับ</span>
                                            <a href="#" class="text-[#3262CA] font-semibold underline">ข้อตกลงและเงื่อนไขการใช้งาน</a>
                                            <span>และ</span>
                                            <a href="#" class="text-[#3262CA] font-semibold underline">นโยบายความเป็นส่วนตัว</a>
                                        </div>

                                        <div class="flex flex-row gap-[8px]">
                                            <input required type="checkbox" id="nursing_agreeNews" class="rounded-md border border-[#D9D8DC]">
                                            <span class="text-[#1F1F1F]">ยินยอมรับข่าวสาร สิทธิประโยชน์ และข้อมูลอัปเดตผ่านทางอีเมล SMS จาก Rate My Nurse</span>
                                        </div>

                                        <div class="flex flex-row flex-nowrap items-start !items-start gap-[8px]" style="flex-wrap: nowrap;">
                                            <input required type="checkbox" id="nursing_truth" class="rounded-md border border-[#D9D8DC] mt-[3px]">
                                            <span class="text-[#1F1F1F]">ฉันรับรองว่าข้อมูลทั้งหมดที่กรอกเป็นความจริง และหากพบว่าเป็นเท็จฉันยินยอมให้ยกเลิกการสมัคร</span>
                                        </div>

                                        <span>ข้าพเจ้ารับทราบว่า ความยินยอมดังกล่าวข้าพเจ้าสามารถยกเลิกได้ตลอดเวลา ข้าพเจ้าเข้าใจและ
                                                ตกลงในข้อกำหนดและเงื่อนไข รวมทั้งเข้าใจถึงเรื่องการเก็บ การใช้ และการเปิดเผยข้อมูลส่วนบุคคลแล้ว</span>
                                    </div>

                                    <div class="h-[1px] w-full bg-[#ECECED]"></div>

                                    <div class="grid grid-cols-2 md:flex md:flex-row gap-[24px] md:justify-end justify-between">
                                        <a class="close text-[#5A5A5A] font-medium w-full max-w-[172px] h-[48px] border border-[#D9D8DC] rounded-lg text-center leading-[48px]">ยกเลิก</a>
                                        <button id="nursingCreate" class="next-btn flex flex-row gap-[12px] justify-center items-center text-white font-medium w-full max-w-[172px] h-[48px] border border-[#286F51] bg-[#286F51] rounded-lg auth-btn disabled">สมัครสมาชิก</button>
                                    </div>
                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script>
                const inputs = document.querySelectorAll('.otp-code');
                const confirmBtn = document.getElementById('otp_confirm');

                // ป้องกันกดซ้ำระหว่างรอ server ตอบกลับ (request OTP / verify OTP)
                let authRequestInProgress = false;
                const authLoadingOverlay = document.getElementById('authenLoadingOverlay');

                function lockAuthUI() {
                    authRequestInProgress = true;
                    if (authLoadingOverlay) authLoadingOverlay.classList.add('active');
                }

                function unlockAuthUI() {
                    authRequestInProgress = false;
                    if (authLoadingOverlay) authLoadingOverlay.classList.remove('active');
                }

                // เช็คว่าเบอร์นี้มีในระบบแล้วหรือยัง — เรียกก่อนสลับไปขั้นตอนถัดไปของฟอร์มสมัครสมาชิก
                // กันไม่ให้กรอกฟอร์มที่เหลือทั้งหมดจนจบแล้วมาเจอ error ซ้ำเบอร์ตอน submit สุดท้าย
                // fail-open: ถ้าเช็คไม่ได้ (เช่น network error) ปล่อยผ่านไปก่อน backend จะเช็คซ้ำตอน submit จริงอีกที
                async function rmnCheckPhoneExists(phoneNumber) {
                    try {
                        const response = await axios.post(RMN_CONFIG.api.baseUrl + '/check-phone', {
                            phone: phoneNumber,
                        });
                        return !!response.data?.exists;
                    } catch (err) {
                        return false;
                    }
                }

                // แสดง modal ยืนยัน OTP หลังสมัครสมาชิกสำเร็จ (ใช้ modal เดียวกับตอน login)
                // onVerified จะถูกเรียกแทน location.reload() ปกติ หลังยืนยัน OTP สำเร็จ
                function rmnShowRegistrationOtpModal(phoneNumber, onVerified) {
                    document.querySelectorAll('.step').forEach(step => {
                        step.style.display = 'none';
                    });

                    document.getElementById('tel_number').value = phoneNumber;
                    const telTxt = document.querySelector('.tel_txt');
                    if (telTxt) telTxt.textContent = phoneNumber;

                    const otpConfirmEl = document.getElementById('otpConfirm');
                    otpConfirmEl.style.display = 'flex';

                    window._rmnOtpVerifiedCallback = onVerified;
                    startCountdown();
                }

                // แสดง error "เบอร์นี้มีผู้ใช้งานแล้ว" ใต้ช่องกรอกเบอร์ + highlight ขอบสีแดง
                // (ใช้ pattern เดียวกับ error อื่นๆ ในฟอร์มนี้ — label.error เป็น sibling ถัดจาก input)
                function rmnShowPhoneDuplicateError(inputEl) {
                    inputEl.classList.add('border-red-500');
                    const errorEl = inputEl.nextElementSibling;
                    if (errorEl && errorEl.classList.contains('error')) {
                        errorEl.innerText = 'เบอร์โทรศัพท์นี้มีผู้ใช้งานแล้ว';
                    }
                }

                function rmnClearPhoneDuplicateError(inputEl) {
                    inputEl.classList.remove('border-red-500');
                    const errorEl = inputEl.nextElementSibling;
                    if (errorEl && errorEl.classList.contains('error')) {
                        errorEl.innerText = '';
                    }
                }

                function checkAllFilled() {
                    let allFilled = true;
                    inputs.forEach(input => {
                        if (input.value.trim() === '') {
                        allFilled = false;
                        }
                    });

                    if (allFilled) {
                        confirmBtn.removeAttribute('disabled');
                    } else {
                        confirmBtn.setAttribute('disabled', true);
                    }
                }

                inputs.forEach((input, index) => {
                    input.addEventListener('input', (e) => {
                        e.target.value = e.target.value.replace(/[^0-9]/g, '');

                        if (e.target.value && index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }

                        checkAllFilled();
                    });

                    input.addEventListener('keydown', (e) => {
                        // Backspace กลับไปช่องก่อนหน้า
                        if (e.key === "Backspace" && !e.target.value && index > 0) {
                            inputs[index - 1].focus();
                        }
                    });
                });

                const closeButtons = document.querySelectorAll('.close');
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const steps = document.querySelectorAll('.step');
                        steps.forEach(step => {
                            step.style.display = 'none';
                        });

                        authnTab = document.getElementById('authen');
                        authnTab.style.display = 'flex';

                        const target = document.getElementById('section-678-21');
                        if (target) {
                            target.style.display = 'none';
                        }
                    });
                });

                
                    // const authenButtons = document.querySelectorAll('.authen');
                    // authenButtons.forEach(btn => {
                    //     btn.addEventListener('click', (e) => {
                    //         e.preventDefault();
                    //         const target = document.getElementById('section-678-21');
                    //         if ( target ) {
                    //             target.style.display = 'block';
                    //         }
                    //     })
                    // });
                

                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.authen');
                    if (!btn) return;

                    e.preventDefault();

                    const target = document.getElementById('section-678-21');
                    if (target) {
                        target.style.display = 'block';
                    }
                });
                
            </script>
            
            <script>
                const telInput = document.getElementById('tel_number');
                const phone    = document.getElementById('phone');
                const loginBtn = document.querySelector('.loginBtn');

                telInput.addEventListener('input', function() {
                    // กรองให้เหลือเฉพาะตัวเลข
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // ตัดเกิน 10 หลักออกอัตโนมัติ
                    if (this.value.length > 10) {
                        this.value = this.value.slice(0, 10);
                    }

                    // ตรวจสอบ pattern: ต้องขึ้นต้นด้วย 0 และมี 10 หลัก
                    const thaiPhonePattern = /^0[0-9]{9}$/;

                    if (thaiPhonePattern.test(this.value)) {
                        loginBtn.removeAttribute('disabled'); // เปิดปุ่ม
                    } else {
                        loginBtn.setAttribute('disabled', true); // ปิดปุ่ม
                    }
                });

                phone.addEventListener('input', function() {
                    // กรองให้เหลือเฉพาะตัวเลข
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // ตัดเกิน 10 หลักออกอัตโนมัติ
                    if (this.value.length > 10) {
                        this.value = this.value.slice(0, 10);
                    }

                    // หา span.error ที่อยู่ใน container ของ input
                    const errorSpan = this.closest('.form-group').querySelector('.error');

                    // ตรวจสอบ pattern: ต้องขึ้นต้นด้วย 0 และมี 10 หลัก
                    const thaiPhonePattern = /^0[0-9]{9}$/;

                    if (thaiPhonePattern.test(this.value)) {
                        errorSpan.textContent = ''; // ไม่มี error
                    } else {
                        errorSpan.textContent = 'กรุณากรอกเบอร์โทรศัพท์ 10 หลัก เริ่มต้นด้วย 0';
                    }
                });

                const registerBtn = document.getElementById('registerBtn');
                const userTypeTab = document.getElementById('userType');
                const serviceType = document.getElementById('serviceType');
                const memberRegistrationTab = document.getElementById('memberRegistrationTab');
                const providerRegistrationTab = document.getElementById('providerRegistrationTab');
                const nursingRegistrationTab = document.getElementById('nursingRegistrationTab');
                // Member Registration clicked : MemberTab


                // Nursing or NursingHome Type clicked

                const otpConfirm  = document.getElementById('otpConfirm');
                registerBtn.addEventListener('click', function() {
                    const steps = document.querySelectorAll('.step');
                    steps.forEach(step => {
                        step.style.display = 'none';
                    });
                    userTypeTab.style.display = 'flex';
                });

                const serviceUser = document.getElementById('serviceUser');
                serviceUser.addEventListener('click', function() {
                    const steps = document.querySelectorAll('.step');
                    steps.forEach(step => {
                        step.style.display = 'none';
                    });
                    serviceType.style.display = 'flex';
                });

                const MemberUser = document.getElementById('memberUser');
                MemberUser.addEventListener('click', function() {
                    const steps = document.querySelectorAll('.step');
                    steps.forEach(step => {
                        step.style.display = 'none';
                    });
                    memberRegistrationTab.style.display = 'flex';
                });

                const providerUser = document.getElementById('providerUser');
                providerUser.addEventListener('click', function() {
                    const steps = document.querySelectorAll('.step');
                    steps.forEach(step => {
                        step.style.display = 'none';
                    });
                    providerRegistrationTab.style.display = 'flex';
                });

                const nursingUser = document.getElementById('nursingUser');
                nursingUser.addEventListener('click', function() {
                    const steps = document.querySelectorAll('.step');
                    steps.forEach(step => {
                        step.style.display = 'none';
                    });
                    nursingRegistrationTab.style.display = 'flex';
                });

                const backBtns = document.querySelectorAll('.back');
                backBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        // ดึง id ของ tab ที่ต้องการไปแสดงจาก data-to
                        const backto = this.dataset.to;

                        // ซ่อนทุก step
                        const steps = document.querySelectorAll('.step');
                        steps.forEach(step => {
                            step.style.display = 'none';
                        });

                        // แสดง tab ที่ต้องการ
                        const tab = document.getElementById(backto);
                        if (tab) {
                            tab.style.display = 'flex';
                        }
                    });
                });

                loginBtn.addEventListener('click', function() {
                    requestOtp();
                });

                confirmBtn.addEventListener('click', async function() {
                    if (authRequestInProgress) return;

                    const phone = document.getElementById('tel_number').value;
                    let otp = '';
                    document.querySelectorAll('.otp-code').forEach(input => otp += input.value);

                    lockAuthUI();
                    try {
                        const response = await axios.post('/wp-admin/admin-ajax.php?action=verify_otp', {
                            phone: phone,
                            otp: otp
                        });
                        if (response.data.success) {
                            // ถ้า modal นี้ถูกเปิดมาจากขั้นตอน "สมัครสมาชิก" (ไม่ใช่ login ปกติ) จะมี
                            // callback รอทำงานต่อ (เช่น แสดง popup สมัครสำเร็จ) แทนที่จะ reload หน้าเฉยๆ
                            if (window._rmnOtpVerifiedCallback) {
                                const cb = window._rmnOtpVerifiedCallback;
                                window._rmnOtpVerifiedCallback = null;
                                cb();
                            } else {
                                // login ปกติสำเร็จ — ปิด modal สมัคร/login แล้วรีเฟรช header ใหม่เลย
                                // ไม่ต้อง reload หน้าทั้งหน้าอีกต่อไป (เร็วกว่า ไม่มี flash ของหน้าเปล่า)
                                document.querySelectorAll('.step').forEach(step => {
                                    step.style.display = 'none';
                                });
                                const authnTab = document.getElementById('authen');
                                if (authnTab) authnTab.style.display = 'flex';
                                const modalTarget = document.getElementById('section-678-21');
                                if (modalTarget) modalTarget.style.display = 'none';

                                // ล้าง cache ผลลัพธ์ getCurrentUser() เก่า (มักเป็น null ตอนยังไม่ login) ก่อนเรียก updateUserUI() ซ้ำ
                                RMN_Utils.invalidateCurrentUserCache();
                                if (window.updateUserUI) window.updateUserUI();
                            }
                        }
                    } catch (err) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: err.response?.data?.data?.message || 'เกิดข้อผิดพลาด',
                            showConfirmButton: false,
                            timerProgressBar: true,
                        });
                    } finally {
                        unlockAuthUI();
                    }
                });

            </script>

            <script>
                function startCountdown() {
                    const otpConfirm = document.getElementById('otpConfirm'); // ให้แน่ใจว่า element นี้มีอยู่
                    const countdownEl = otpConfirm.querySelector('.countdown');
                    let timer = parseInt(countdownEl.dataset.timer, 10) || 90;

                    // กันนับซ้ำ ถ้ากดเข้าหน้านี้หลายครั้ง
                    if (otpConfirm._countdownInterval) clearInterval(otpConfirm._countdownInterval);

                    function updateDisplay() {
                        var minutes = Math.floor(timer / 60);
                        var seconds = timer % 60;
                        countdownEl.textContent = '(' + minutes + ':' + seconds.toString().padStart(2,'0') + ')';
                    }

                    updateDisplay();

                    otpConfirm._countdownInterval = setInterval(function() {
                        timer--;
                        updateDisplay();

                        if (timer <= 0) {
                        clearInterval(otpConfirm._countdownInterval);
                        otpConfirm._countdownInterval = null;
                        countdownEl.textContent = 'ขอรับรหัสอีกครั้งได้เลย';
                        }
                    }, 1000);
                }
                
                function requestOtp() {
                    if (authRequestInProgress) return;

                    const steps = document.querySelectorAll('.step');
                    let phone = document.getElementById('tel_number').value;
                    let tel_txt = document.querySelector('.tel_txt');
                    tel_txt.textContent = phone;

                    lockAuthUI();
                    // เรียก API request OTP
                    axios.post(RMN_CONFIG.api.baseUrl + "/otp/request", {
                        phone: phone
                    })
                    .then(function (response) {
                        console.log("OTP Requested:", response.data);

                        // ถ้า success → ซ่อน step เก่า แสดงหน้า OTP confirm
                        steps.forEach(step => {
                            step.style.display = 'none';
                        });
                        otpConfirm.style.display = 'flex';

                        // เริ่มนับถอยหลัง
                        startCountdown();
                    })
                    .catch(function (error) {
                        if (error.response) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: error.response.data.message || 'เกิดข้อผิดพลาด',
                                showConfirmButton: false,
                                timerProgressBar: true,
                            });
                        } else {
                            console.error(error);
                        }
                    })
                    .finally(function () {
                        unlockAuthUI();
                    });
                }
            </script>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const form = document.getElementById('memberRegisFrm');
                    const btn = document.getElementById('memberCreate');
                    const memberphone = document.getElementById('phone');
                    const cardid = document.getElementById('cardid');
                    const memberinputs = form.querySelectorAll('input[required]');
                    const agreeTerms = document.getElementById('agreeTerms');
                    const agreeNews = document.getElementById('agreeNews');

                    // ฟังก์ชันตรวจสอบบัตรประชาชนไทย
                    function isValidThaiID(id) {
                        if (!/^[0-9]{13}$/.test(id)) return false;
                        let sum = 0;
                        for (let i = 0; i < 12; i++) sum += parseInt(id.charAt(i)) * (13 - i);
                        return (11 - (sum % 11)) % 10 === parseInt(id.charAt(12));
                    }

                    function createPopUp(title = '', description = '', next = null)
                    {
                        return '<div class="backdrop-blur-sm w-full h-full flex flex-col justify-center items-center fixed top-0 p-[30px]"><div class="text-center w-full flex flex-col max-w-[461px] h-full max-h-[491px] rounded-lg p-[24px] justify-center items-center gap-[24px] bg-white"><span>X</span><img src="https://ratemynurse.org/wp-content/uploads/2025/10/layer_1_success.png"/><span class="font-semibold text-[18px]">'+title+'</span><span class="text-[15px]">'+description+'</span><div class="grid grid-cols-2 gap-[24px] font-semibold"><button onclick="this.closest(\'.backdrop-blur-sm\').remove()" class="px-[30px] h-[48px] max-w-[172px] text-center border border-[#D9D8DC] bg-white text-[#5A5A5A] rounded-md leading-[48px]">ไว้ทีหลัง</button><a class="px-[30px] h-[48px] max-w-[172px] text-center border border-[#286F51] bg-[#286F51] text-white rounded-md leading-[48px]" href="{$jobBoardUrl}">ค้นหาเลย</a></div></div></div>';
                    }

                    // ตรวจสอบตัวเลขเท่านั้น
                    let memberPhoneExists = false;
                    memberphone.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                        memberPhoneExists = false; // แก้เบอร์ใหม่แล้ว ต้องเช็คซ้ำอีกครั้งตอน blur
                        validateForm();
                    });

                    // เช็คว่าเบอร์นี้มีในระบบแล้วหรือยัง ทันทีที่กรอกเสร็จ (blur) — ไม่ต้องรอกรอกฟอร์ม
                    // ที่เหลือทั้งหมดจนจบแล้วมาเจอ error ซ้ำเบอร์ตอน submit สุดท้าย
                    memberphone.addEventListener('blur', async function() {
                        if (!/^0[0-9]{9}$/.test(memberphone.value)) return;
                        memberPhoneExists = await rmnCheckPhoneExists(memberphone.value);
                        validateForm();
                    });

                    cardid.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
                        validateForm();
                    });

                    memberinputs.forEach(input => input.addEventListener('input', validateForm));
                    agreeTerms.addEventListener('change', validateForm);
                    agreeNews.addEventListener('change', validateForm);

                    function validateForm() {
                        let allFilled = true;
                        let phoneValid = /^0[0-9]{9}$/.test(memberphone.value) && !memberPhoneExists;
                        let cardValid = isValidThaiID(cardid.value);
                        let checkboxesChecked = agreeTerms.checked && agreeNews.checked;

                        // Reset error highlight
                        memberinputs.forEach(input => input.classList.remove('border-red-500'));
                        form.querySelectorAll('.error').forEach(el => el.classList.add('hidden'));

                        // ตรวจเบอร์โทร
                        if (memberPhoneExists) {
                            memberphone.classList.add('border-red-500');
                            memberphone.nextElementSibling.textContent = 'เบอร์โทรศัพท์นี้มีผู้ใช้งานแล้ว';
                            memberphone.nextElementSibling.classList.remove('hidden');
                        } else if (!phoneValid && memberphone.value.trim() !== '') {
                            memberphone.classList.add('border-red-500');
                            memberphone.nextElementSibling.textContent = '';
                            memberphone.nextElementSibling.classList.remove('hidden');
                        }

                        // ตรวจช่องว่าง
                        memberinputs.forEach(input => {
                            if (input.value.trim() === '') {
                                input.classList.add('border-red-500');
                                allFilled = false;
                            }
                        });

                        // ตรวจบัตรประชาชน
                        if (!cardValid && cardid.value.trim() !== '') {
                            cardid.classList.add('border-red-500');
                            cardid.nextElementSibling.classList.remove('hidden');
                        }

                        // ตรวจ checkbox
                        if (!checkboxesChecked) {
                            agreeTerms.classList.add('border-red-500');
                            agreeNews.classList.add('border-red-500');
                        } else {
                            agreeTerms.classList.remove('border-red-500');
                            agreeNews.classList.remove('border-red-500');
                        }

                        if (allFilled && phoneValid && cardValid && checkboxesChecked) {
                            btn.classList.remove('disabled', 'opacity-50', 'cursor-not-allowed');
                        } else {
                            btn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed');
                        }
                    }

                    function getErrorSpan(input) {
                        let span = input.parentNode.querySelector('.error');
                        if (!span) {
                            span = document.createElement('span');
                            span.classList.add('error', 'text-red-500', 'text-sm');
                            input.insertAdjacentElement('afterend', span);
                        }
                        return span;
                    }

                    form.addEventListener('submit', async e => {
                        e.preventDefault();
                        validateForm();

                        if (btn.classList.contains('disabled')) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: กรุณากรอกข้อมูลให้ครบและถูกต้องก่อนสมัครสมาชิก,
                                showConfirmButton: false,
                                timerProgressBar: true,
                            });
                            return;
                        }

                        // ล้าง error เก่า
                        form.querySelectorAll('.error').forEach(span => {
                            span.textContent = '';
                            span.classList.add('hidden');
                        });

                        const formData = new FormData(form);
                        formData.append('action', 'member_register');

                        try {
                            const response = await axios.post('/wp-admin/admin-ajax.php', formData);
                            const result = response.data;

                            if (result.success) {
                                // สมัครสำเร็จแล้ว แต่ยังไม่ login จริง — ต้องยืนยัน OTP ที่ส่งไปเบอร์ก่อน
                                const registeredPhone = result.data?.phone || memberphone.value;

                                rmnShowRegistrationOtpModal(registeredPhone, () => {
                                    form.reset();
                                    btn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed');
                                    document.getElementById('section-678-21').style.display = 'none';
                                    // ล้าง cache ผลลัพธ์ getCurrentUser() เก่า (มักเป็น null ตอนยังไม่ login) ก่อนเรียก updateUserUI() ซ้ำ
                                RMN_Utils.invalidateCurrentUserCache();
                                if (window.updateUserUI) window.updateUserUI();
                                    let popup = createPopUp('สมัครสมาชิกสำเร็จ', 'เราพร้อมช่วยคุณหาผู้ดูแลมืออาชีพที่ไว้ใจได้ เพื่อคนที่คุณรัก เริ่มค้นหาผู้ดูแลที่ใช่ได้เลยตอนนี้');
                                    document.querySelector('body').insertAdjacentHTML('beforeend', popup);
                                });
                                return;
                            }

                            const fieldMap = {
                                firstname: 'fname',
                                lastname: 'lname',
                                phone: 'phone',
                                email: 'email',
                                cardid: 'cardid'
                            };

                            Object.entries(result.data.errors).forEach(([field, messages]) => {
                                const inputId = fieldMap[field];
                                const input = document.getElementById(inputId); // ใช้ id แทน name
                                if (input) {
                                    const errorSpan = getErrorSpan(input);
                                    errorSpan.textContent = messages[0];
                                    errorSpan.classList.remove('hidden');

                                    // ✅ เพิ่มบรรทัดนี้เพื่อให้ input ขอบแดง
                                    input.classList.add('border-red-500');
                                } else {
                                    console.warn('ไม่พบ input สำหรับ field:', field);
                                }
                            });

                        } catch (error) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด ติดต่อผู้ดูแลระบบ',
                                showConfirmButton: false,
                                timerProgressBar: true,
                            });
                        }
                    }); 
                });
            </script>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    async function updateUserUI() {
                        try {
                            // หน้านี้อาจถูก cache ข้าม visitor ได้ (เช่น WordPress.com Batcache) จึงห้ามเชื่อ
                            // ข้อมูลใดๆ ที่ฝังมากับ HTML ตรงๆ ต้องดึงสถานะ login ของผู้ใช้ปัจจุบันสดๆ ผ่าน
                            // ajax (admin-ajax.php ไม่ถูก cache) เสมอ — ใช้ RMN_Utils.getCurrentUser()
                            // (cache เป็น promise เดียวกันทั้งหน้า) กันยิงซ้ำกับ script อื่นที่ดึง user เหมือนกัน
                            const user = await RMN_Utils.getCurrentUser();

                            if (!user) return;

                            document.querySelectorAll(".authen").forEach(el => el.style.display = 'none');

                            // เรียกซ้ำได้ (เช่น หลัง OTP verify สำเร็จ โดยไม่ต้อง reload หน้า) — ลบ user-menu
                            // เก่าออกก่อนเสมอ กัน insert ซ้ำซ้อนกัน 2 ชุด
                            document.getElementById('rmn-user-menu')?.remove();

                            // แสดง user name
                            const lastAuth = document.querySelector(".mega_menu_wrap .authen:last-child");
                            if (!lastAuth) return;

                            // สร้าง container ครอบทั้งหมด
                            const userMenu = document.createElement("div");
                            userMenu.id = 'rmn-user-menu';

                            document.addEventListener("click", (e) => {
                                const logoutBtn = e.target.closest('.js-logout');
                                if (!logoutBtn) return;

                                e.preventDefault();

                                // กันยิงซ้ำ (สำคัญมากกับ ajax)
                                if (logoutBtn.dataset.loading === '1') return;
                                logoutBtn.dataset.loading = '1';

                                logoutBtn.style.opacity = '0.5';
                                logoutBtn.style.pointerEvents = 'none';

                                const formData = new URLSearchParams();
                                formData.append('action', 'logout');

                                axios.post('/wp-admin/admin-ajax.php', formData)
                                    .then((res) => {
                                        if (res.data.success) {
                                            // ออกจากระบบสำเร็จ redirect ทันที ไม่ต้องรอ toast
                                            // (หน้าจะเปลี่ยนอยู่แล้ว โชว์ toast ค้างไว้ไม่มีประโยชน์ มีแต่ทำให้ช้า)
                                            window.location.href = "https://ratemynurse.org";
                                        } else {
                                            Swal.fire({
                                                toast: true,
                                                position: 'top-end',
                                                icon: 'error',
                                                title: 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง',
                                                showConfirmButton: false,
                                                timerProgressBar: true,
                                                didClose: () => {
                                                    resetBtn(logoutBtn);
                                                }
                                            });
                                        }
                                    })
                                    .catch((err) => {
                                        Swal.fire({
                                            toast: true,
                                            position: 'top-end',
                                            icon: 'error',
                                            title: 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง',
                                            showConfirmButton: false,
                                            timerProgressBar: true,
                                        });
                                        resetBtn(logoutBtn);
                                    });
                            });

                            function resetBtn(btn) {
                                btn.style.opacity = '1';
                                btn.style.pointerEvents = 'auto';
                                delete btn.dataset.loading;
                            }
                            
                            userMenu.className = "relative flex flex-row gap-[12px] user-wrapper pl-[25px]";

                            // ปุ่มชื่อผู้ใช้
                            const userLink = document.createElement("a");
                            userLink.href = "#";
                            userLink.className = "user-info ct-link oxel_megamenu_parent";
                            const userImage = document.createElement("img");
                            const defaultAvatar = 'https://ratemynurse.org/wp-content/uploads/2025/11/Frame-1000004756.webp';
                            userImage.src = defaultAvatar;

                            if (user?.user_type === 'MEMBER') {
                                userImage.src = user?.profile?.coverImage || defaultAvatar;
                            } else if (user?.user_type === 'NURSING_HOME') {
                                // NURSING_HOME: coverImage อยู่ใน profiles แต่ละอัน ไม่มีระดับ user
                                const nhCover = user?.profiles?.[0]?.coverImage;
                                userImage.src = nhCover || defaultAvatar;
                            } else {
                                userImage.src = user?.coverImage || defaultAvatar;
                            }
                            
                            userImage.className = "w-6 h-6 rounded-full";
                            userLink.appendChild(userImage);

                            // NURSING_HOME: หนึ่ง user มีได้หลายสาขา (profiles) — ถ้ามีสาขาเดียวให้โชว์ชื่อสาขา
                            // (user.profiles[0].name) เหมือน Nursing ทั่วไป แต่ถ้ามีหลายสาขาต้องโชว์ชื่อ
                            // เจ้าของบัญชี (firstname/lastname จาก users table) แทน เพราะไม่มีสาขาเดียวที่
                            // เป็นตัวแทนได้ชัดเจน
                            const displayName = user.user_type === 'NURSING'
                            ? (user.profile?.name ?? '')
                            : user.user_type === 'NURSING_HOME'
                                ? (Array.isArray(user.profiles) && user.profiles.length === 1
                                    ? (user.profiles[0]?.name ?? '')
                                    : ((user.firstname ?? '') + ' ' + (user.lastname ?? '')).trim())
                                : ((user.firstname ?? '') + ' ' + (user.lastname ?? '')).trim();

                            const nameText = displayName.length > 12
                            ? displayName.slice(0, 12) + '...'
                            : displayName;

                            const nameSpan = document.createElement('span');
                            nameSpan.className = 'user-name text-[15px]';
                            nameSpan.textContent = nameText;
                            userLink.appendChild(nameSpan); // ไม่ทับเนื้อหาเดิม

                            const userIcon = `
                                <svg class="w-6 h-6 text-white"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24"
                                    fill="none"
                                    viewBox="0 0 24 24">
                                <path stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m19 9-7 7-7-7"/>
                                </svg>
                                `;

                                userLink.insertAdjacentHTML('beforeend', userIcon);

                            // สร้าง dropdown content
                            const dropdown = document.createElement("div");
                            dropdown.className = "hidden profileDropdown md:absolute top-full right-0 mt-2 w-[348px] bg-white rounded-lg shadow-lg border border-gray-200 z-50";
                            const defaultDropdownAvatar = 'https://ratemynurse.org/wp-content/uploads/2025/11/cropped-541800960_4063110697234606_2692539723161017286_n.jpg';
                            let coverImage = defaultDropdownAvatar;
                            if (user?.user_type === 'MEMBER') {
                                coverImage = user?.profile?.coverImage || defaultDropdownAvatar;
                            } else if (user?.user_type === 'NURSING_HOME') {
                                const nhDropdownCover = user?.profiles?.[0]?.coverImage;
                                coverImage = nhDropdownCover || defaultDropdownAvatar;
                            } else {
                                coverImage = user?.coverImage || defaultDropdownAvatar;
                            }
                            
                            let avatar = '<img class="h-[50px] w-[50px] object-cover rounded-full" src="'+coverImage+'" width="50" height="50">';
                            let subscription = ( user.profile?.subscriptions ? user.profile.subscriptions : null);
                            let current_active_subscription = user?.profile?.current_active_subscription?.plan ?? null;
                            if (user.user_type != 'NURSING') {
                                current_active_subscription = user.plan ?? null;
                            }
                            const planLabel = current_active_subscription || 'ยังไม่มีแพ็กเกจ';
                            let coin = '';
                            let plan_icon2 = '';
                            let plan_icon = document.createElement('span');
                            plan_icon.className = 'plan-info absolute top-[22px] left-[26px]';

                            if (current_active_subscription === 'PROFESSIONAL') {
                                coin = '<img src="https://ratemynurse.org/wp-content/uploads/2026/02/Ranking.webp" class="w-[16px] h-[16px]" width="16" height="16" loading="lazy">';

                                plan_icon.innerHTML = `
                                    <img src="https://ratemynurse.org/wp-content/uploads/2025/12/certified_green-1.webp"
                                        class="w-[14px] h-[14px]"
                                        width="14" height="14" loading="eager" fetchpriority="high">
                                `;

                                plan_icon2 = '<img src="https://ratemynurse.org/wp-content/uploads/2025/12/certified_green-1.webp" class="w-[24px] h-[24px] absolute top-[27px] right-[-2px]" width="24" height="24" loading="lazy">';
                            }
                            
                            userLink.appendChild(plan_icon);
                            let subscription_expired = user.profile?.subscriptions?.[0]?.expired_at ?? '<span class="text-red text-sm">ไม่มีข้อมูล</span>';
                            dropdown.innerHTML = `
                                <div class="profile flex flex-row gap-[8px] p-[12px] bg-cover bg-center" style="background-image: url('https://ratemynurse.org/wp-content/uploads/2025/10/Highlight-Rate-Mu-Nurse-mobile.webp');">
                                    <div class="profile_avatar w-[50px] h-[50px] bg-gray-200 relative rounded-full overflow-hidden">\${avatar}\${plan_icon2}</div>
                                    <div class="profile_info flex flex-col gap-[8px] font-semibold relative">
                                        <span class="text-md leading-[20px] text-[#1F1F1F]">\${displayName}</span>
                                        <a class="text-sm leading-[20px] text-[#5A5A5A]">ดูโปรไฟล์</a>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-[8px] bg-[#1D8654] p-[12px] text-xs text-white">
                                    <div class="flex flex-row justify-between">
                                        <div class="flex flex-row gap-[8px]">
                                            <span class="leading-[20px]">ระดับของฉัน :</span>
                                            <span class="py-[4px] px-[8px] bg-white rounded-xl text-[#286F51] font-semibold flex flex-row gap-[5px] items-center">\${planLabel}\${coin}</span>
                                        </div>
                                        <a class="flex flex-row gap-[8px] justify-center">
                                            <span class="leading-[20px]">ต่ออายุแพ็กเกจ</span>
                                            <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                    <span>แพ็กเกจของคุณจะหมดอายุในวันที่ \${subscription_expired}</span>
                                </div>
                            `;

                            if ( user.user_type != 'MEMBER' ) {
                                dropdown.innerHTML += `
                                    <ul class="p-[12px] text-base">
                                        <li class="mb-[8px]">
                                            <a href="/my-overview" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/pie.webp" width="21" height="21">
                                                <span>ภาพรวมของฉัน</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/my-contacts" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/calendar.webp" width="21" height="21">
                                                <span>การนัดหมาย</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/my-account" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/user.webp" width="21" height="21">
                                                <span>แก้ไขข้อมูลส่วนตัว</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/my-profile" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/message.webp" width="21" height="21">
                                                <span>แก้ไขประกาศของฉัน</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/subscription" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/card.webp" width="21" height="21">
                                                <span>การสมัครสมาชิก</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/my-favorite" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/heart.webp" width="21" height="21">
                                                <span>รายการโปรด</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" id="logout" class="js-logout flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/card.webp" width="21" height="21">
                                                <span>ออกจากระบบ</span>
                                            </a>
                                        </li>
                                    </ul>
                                `;
                            } else {
                                dropdown.innerHTML += `
                                    <ul class="p-[12px] text-base">
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/my-account" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/user.webp" width="21" height="21">
                                                <span>แก้ไขข้อมูลส่วนตัว</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/subscription" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/card.webp" width="21" height="21">
                                                <span>การสมัครสมาชิก</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="/my-favorites" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/heart.webp" width="21" height="21">
                                                <span>รายการโปรด</span>
                                            </a>
                                        </li>
                                        <li class="mb-[8px]">
                                            <a href="https://ratemynurse.org/my-contacts" class="flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/12/LetterOpened.webp" width="21" height="21">
                                                <span>ประวัติการติดต่อ</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" id="logout" class="js-logout flex flex-row gap-[8px] px-4 py-3 hover:bg-gray-100 border-t border-[#F2F4F7]">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/card.webp" width="21" height="21">
                                                <span>ออกจากระบบ</span>
                                            </a>
                                        </li>
                                    </ul>
                                `;
                            }

                            // ใส่ทั้งหมดเข้า container
                            // Notifications
                            const notifications = document.createElement("div");
                            notifications.className = "notifications";
                            let notiCountHtml = '';
                            let notiAllItems = notiItems = `
                                <div class="flex flex-col items-center justify-center h-[245px]">
                                    <img src="https://ratemynurse.org/wp-content/uploads/2026/01/NotificationEmpty.webp" width="175" height="175">
                                    <span>ยังไม่มีการแจ้งเตือน</span>
                                </div>
                            `;

                            let notiIcon = '';
                            // only unread
                            if (Array.isArray(user?.unread_notifications) && user?.unread_notifications.length > 0) {
                                notiCountHtml = '<span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>';
                                
                                notiItems = user.unread_notifications.map((notification) => {
                                    let id = notification.id;
                                    let title = notification.title;
                                    let message = notification.message;
                                    let created_at = formatThaiDate(notification.created_at);
                                    switch (notification.type) {
                                        case 'USER':
                                            notiIcon = '<img src="https://ratemynurse.org/wp-content/uploads/2025/12/icon-bell.png" width="50" height="50" loading="lazy">';
                                            break;
                                        case 'SUBSCRIPTION':
                                            notiIcon = '<img src="https://ratemynurse.org/wp-content/uploads/2025/12/icon-expired.png" width="50" height="50" loading="lazy">';
                                            break;
                                        case 'ADS':
                                            notiIcon = '<img src="https://ratemynurse.org/wp-content/uploads/2025/12/icon-crown.png" width="50" height="50" loading="lazy">';
                                            break;
                                        default:
                                            break;
                                    }
                                    return `
                                        <div>
                                            <a href="#" onclick="markNotificationAsRead(event, this)" data-noti="\${id}" class="flex flex-col gap-[8px] hover:bg-gray-100 border-t border-[#F2F4F7] transition-all duration-300 ease-out">
                                                <div class="flex flex-row gap-[12px] p-[16px] !flex-nowrap">
                                                    \${notiIcon}
                                                    <div class="flex flex-col gap-[2px]">
                                                        <span class="font-medium text-[14px] text-[#1F1F1F]">\${title}</span>
                                                        <span class="text-[14px] text-[#5A5A5A]">\${message}</span>
                                                        <span class="text-[12px] text-[#8C8A94]">\${created_at}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    `;
                                }).join('');
                            }
                            // All
                            if (Array.isArray(user?.notifications) && user.notifications.length > 0) {
                                notiAllItems = user.notifications.slice(0, 4).map((notification) => {
                                    let id = notification.id;
                                    let title = notification.title;
                                    let message = notification.message;
                                    let created_at = formatThaiDate(notification.created_at);
                                    switch (notification.type) {
                                        case 'USER':
                                            notiIcon = '<img src="https://ratemynurse.org/wp-content/uploads/2025/12/icon-bell.png" width="50" height="50" loading="lazy">';
                                            break;
                                        case 'SUBSCRIPTION':
                                            notiIcon = '<img src="https://ratemynurse.org/wp-content/uploads/2025/12/icon-expired.png" width="50" height="50" loading="lazy">';
                                            break;
                                        case 'ADS':
                                            notiIcon = '<img src="https://ratemynurse.org/wp-content/uploads/2025/12/icon-crown.png" width="50" height="50" loading="lazy">';
                                            break;
                                        default:
                                            break;
                                    }
                                    return `
                                        <div>
                                            <a href="#" onclick="markNotificationAsRead(\${id})" class="flex flex-col gap-[8px] hover:bg-gray-100 border-t border-[#F2F4F7]p">
                                                <div class="flex flex-row gap-[12px] p-[16px] !flex-nowrap">
                                                    \${notiIcon}
                                                    <div class="flex flex-col gap-[2px]">
                                                        <span class="font-medium text-[14px] text-[#1F1F1F]">\${title}</span>
                                                        <span class="text-[14px] text-[#5A5A5A]">\${message}</span>
                                                        <span class="text-[12px] text-[#8C8A94]">\${created_at}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    `;
                                }).join('');
                            }


                            notifications.innerHTML = `
                                <div id="notification-bell" class="relative cursor-pointer">
                                    <img class="bellIcon" src="https://ratemynurse.org/wp-content/uploads/2025/12/bell.webp" width="21" height="21">
                                    \${notiCountHtml}
                                    <div id="noti-dropdown" class="hidden flex-col absolute top-full right-0 mt-2 w-[348px] bg-white rounded-[16px] shadow-lg border border-gray-200 z-50 w-full md:!w-[525px]">
                                        <div class="p-4 flex-row justify-between items-center">
                                            <h3 class="text-lg font-semibold">การแจ้งเตือน</h3>
                                            <a href="#" onclick="readAllNotifications()">อ่านทั้งหมด</a>
                                        </div>
                                        <div class="flex flex-row flex-nowrap ct-section">
                                            <span data-tab="all_notice" class="tab_title active_tab w-[50%] text-center py-[10px]">ทั้งหมด</span>
                                            <span data-tab="unread_notice" class="tab_title w-[50%] text-center py-[10px]">ยังไม่ได้อ่าน</span>
                                        </div>
                                        <div id="all_notice" class="flex-col gap-[8px] tab_content active_tab">
                                            \${notiAllItems}
                                        </div>
                                        <div id="unread_notice" class="hidden flex-col gap-[8px] tab_content">
                                            \${notiItems}
                                        </div>
                                    </div>
                                </div>
                            `;
                    
                            userMenu.appendChild(notifications);
                            userMenu.appendChild(userLink);
                            userMenu.appendChild(dropdown);

                            // แทรกเข้า DOM
                            lastAuth.parentNode.insertBefore(userMenu, lastAuth.nextSibling);
                                        
                            // toggle dropdown
                            userLink.addEventListener("click", (e) => {
                                e.preventDefault();
                                dropdown.classList.toggle("hidden");
                            });

                            // ปิดเมื่อคลิกข้างนอก
                            document.addEventListener("click", (e) => {
                                if (!userMenu.contains(e.target)) {
                                    dropdown.classList.add("hidden");
                                }
                            });
                        } catch (error) {
                            console.error(error);
                        }

                    }
                    // expose ให้เรียกซ้ำได้จาก script อื่น (เช่น หลัง OTP verify สำเร็จ แทนการ location.reload())
                    window.updateUserUI = updateUserUI;
                    updateUserUI();
                });
            </script>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const menu_tab = document.querySelector('.mobile-tab');
                    const links = menu_tab.querySelectorAll('.a-link');

                    links.forEach(link => {
                        link.addEventListener('click', e => {
                            e.preventDefault();
                            link.classList.toggle('current_tab');
                        });
                    });

                    const userProfileLink = document.getElementById('link-716-21');
                    
                });
            </script>

            <script>
                function getErrorSpan(input) {
                    let span = input.parentNode.querySelector('.error');
                    if (!span) {
                        span = document.createElement('span');
                        span.classList.add('error', 'text-red-500', 'text-sm');
                        input.insertAdjacentElement('afterend', span);
                    }
                    return span;
                }

                function checkProviderAgreement()
                {
                    const pagreeTerms = document.getElementById('p_agreeTerms');
                    const pagreeNews = document.getElementById('p_agreeNews');
                    const ptruth     = document.getElementById('p_truth');
                    let checkboxesChecked = pagreeTerms.checked && pagreeNews.checked && ptruth.checked;
                    const nextTab = document.getElementById('nextTab');

                    if (checkboxesChecked) {
                        nextTab.classList.remove('disabled', 'opacity-50', 'cursor-not-allowed');
                    } else {
                        nextTab.classList.add('disabled', 'opacity-50', 'cursor-not-allowed');
                    }
                }

                document.addEventListener('DOMContentLoaded', () => {
                    const providerRegisFrm = document.getElementById('providerRegisFrm');
                    const nextTabBtn = providerRegisFrm.querySelector('#nextTab'); // ✅ ใช้ querySelector
                    const pagreeTerms = document.getElementById('p_agreeTerms');
                    const pagreeNews = document.getElementById('p_agreeNews');
                    const ptruth     = document.getElementById('p_truth');

                    pagreeTerms.addEventListener('change', checkProviderAgreement);
                    pagreeNews.addEventListener('change', checkProviderAgreement);
                    ptruth.addEventListener('change', checkProviderAgreement);

                    const providerFieldMap = {
                        firstname: 'providername',
                        lastname: 'providername',
                        phone: 'main_phone',
                        email: 'provideremail',
                        address: 'address',
                        province_id: 'province',
                        district_id: 'district',
                        sub_district_id: 'sub_district',
                        zipcode: 'zipcode',
                    };
                    const providerStep1Fields = ['firstname', 'lastname', 'phone', 'email'];

                    // Step 1: แค่ validate ฝั่ง client แล้วสลับไปหน้าถัดไป — ไม่ยิง API
                    // (ข้อมูลทั้งหมดจะถูกส่งไปบันทึกครั้งเดียวตอนกด "สมัครสมาชิก" ในขั้นตอนสุดท้าย
                    // เพื่อกันไม่ให้เกิด account ค้างถ้าขั้นตอนถัดไปกรอกไม่สำเร็จ)
                    providerRegisFrm.addEventListener('submit', async e => {
                        e.preventDefault();

                        checkProviderAgreement();

                        if (!providerRegisFrm.checkValidity()) {
                            providerRegisFrm.reportValidity();
                            return;
                        }

                        const nextBtn = providerRegisFrm.querySelector('#nextTab');
                        if (nextBtn) nextBtn.setAttribute('disabled', true);

                        const mainPhoneInput = document.getElementById('main_phone');
                        const phoneValue = mainPhoneInput.value;
                        lockAuthUI();
                        let phoneExists;
                        try {
                            phoneExists = await rmnCheckPhoneExists(phoneValue);
                        } finally {
                            unlockAuthUI();
                        }

                        if (nextBtn) nextBtn.removeAttribute('disabled');

                        if (phoneExists) {
                            rmnShowPhoneDuplicateError(mainPhoneInput);
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'เบอร์โทรศัพท์นี้มีผู้ใช้งานแล้ว',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                            return;
                        }

                        document.querySelector('.subTab-1').style.display = 'none';
                        document.querySelector('.subTab-2').style.display = 'flex';
                    });

                    const providerProfileFrm = document.getElementById('providerProfileFrm');
                    const providerCreateBtn = providerProfileFrm.querySelector('#providerCreate');

                    providerCreateBtn.setAttribute('disabled', true);

                    function checkProviderProfileRequiredFields() {
                        const addressEl = document.getElementById('address');
                        const provinceEl = document.getElementById('province');
                        const districtEl = document.getElementById('district');
                        const subDistrictEl = document.getElementById('sub_district');
                        const zipcodeEl = document.getElementById('zipcode');

                        const filled =
                            addressEl.value !== '' &&
                            provinceEl.value !== '' &&
                            districtEl.value !== '' &&
                            subDistrictEl.value !== '' &&
                            zipcodeEl.value !== '';

                        if (filled) {
                            providerCreateBtn.removeAttribute('disabled');
                            providerCreateBtn.classList.remove('disabled', 'opacity-50', 'cursor-not-allowed');
                        } else {
                            providerCreateBtn.setAttribute('disabled', true);
                            providerCreateBtn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed');
                        }
                    }

                    // ดัก 'change' ทั้งจาก input/textarea ปกติ และจาก Tom Select (dispatch event เอง
                    // ใน rmn-location-selector.js ให้ bubble ขึ้นมาถึง form เหมือน select2 เดิม)
                    providerProfileFrm.addEventListener('change', checkProviderProfileRequiredFields);
                    providerProfileFrm.addEventListener('input', checkProviderProfileRequiredFields);

                    providerProfileFrm.addEventListener('submit', async e => {
                        e.preventDefault();

                        if (!providerProfileFrm.checkValidity()) {
                            providerProfileFrm.reportValidity();
                            return;
                        }

                        const facebook  = document.getElementById('facebook');
                        const res_phone = document.getElementById('res_phone');
                        const website   = document.getElementById('website');

                        // รวม field จากทั้ง step 1 (providerRegisFrm) และ step 2 (providerProfileFrm)
                        // เป็น payload เดียว ส่งให้ backend ยืนยันแบบ atomic ครั้งเดียว
                        let formData = new FormData(providerRegisFrm);
                        for (const [key, value] of new FormData(providerProfileFrm).entries()) {
                            formData.append(key, value);
                        }
                        formData.append('facebook', facebook.value ?? '');
                        formData.append('res_phone', res_phone.value ?? '');
                        formData.append('website', website.value ?? '');
                        formData.append('action', 'provider_register');

                        try {
                            const response = await axios.post('/wp-admin/admin-ajax.php', formData);
                            const result = response.data;

                            if (result.success) {
                                // สมัครสำเร็จแล้ว แต่ยังไม่ login จริง — ต้องยืนยัน OTP ที่ส่งไปเบอร์ก่อน
                                const registerFrame = document.getElementById('section-678-21');
                                const registeredPhone = result.data?.phone || document.getElementById('main_phone').value;

                                rmnShowRegistrationOtpModal(registeredPhone, () => {
                                    registerFrame.style.display = 'none';
                                    // ล้าง cache ผลลัพธ์ getCurrentUser() เก่า (มักเป็น null ตอนยังไม่ login) ก่อนเรียก updateUserUI() ซ้ำ
                                RMN_Utils.invalidateCurrentUserCache();
                                if (window.updateUserUI) window.updateUserUI();

                                    const popUpSuccessFrameHTML = `
                                    <div id="regisSuccessFrame" style="width: 100vw;height: 100vh;background-color: #000000ab;z-index: 999;position: fixed;top: 0;">
                                        <div style="display: flex;flex-direction: column;gap: 36px;width: 461px;height: 491px;border-radius: 12px;padding: 40px 24px 24px 24px;background-color: white;position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);">
                                            <div class="flex flex-col justify-center">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/layer_1_success.png" width="281" height="200" class="mx-auto">
                                            </div>
                                            <div class="flex flex-col gap-[8px] justify-center items-center">
                                                <h4 class="font-semibold">สมัครสมาชิกสำเร็จ</h4>
                                                <span class="text-base">คุณสามารถลงประกาศงานได้ทันที</span>
                                                <span class="text-base">เพิ่มข้อมูลเพื่อให้เจอบริการของคุณง่ายยิ่งขึ้น</span>
                                            </div>
                                            <div class="flex flex-row justify-center gap-[24px] text-base font-medium">
                                                <button class="close_frame w-[172px] h-[48px] leading-[48px] text-[#5A5A5A] rounded-[10px] text-center border border-[#D9D8DC]">ไว้ทีหลัง</button>
                                                <a href="https://ratemynurse.org/my-profile/" class="w-[172px] h-[48px] leading-[48px] text-white rounded-[10px] bg-[#286F51] text-center">ลงประกาศเลย</a>
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    document.body.insertAdjacentHTML('beforeend', popUpSuccessFrameHTML);

                                    // ปุ่มปิด popup
                                    document.querySelector('.close_frame').addEventListener('click', () => {
                                        document.getElementById('regisSuccessFrame').remove();
                                    });
                                });

                                return;
                            }

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: result.message ?? 'สมัครสมาชิกไม่สำเร็จ',
                                showConfirmButton: false,
                                timerProgressBar: true,
                            });

                            if (result.data && result.data.errors) {
                                let backToStep1 = false;

                                Object.entries(result.data.errors).forEach(([field, messages]) => {
                                    const inputId = providerFieldMap[field];
                                    const input = document.getElementById(inputId);
                                    if (input) {
                                        const errorSpan = getErrorSpan(input);
                                        errorSpan.textContent = messages[0];
                                        errorSpan.classList.remove('hidden');
                                        input.classList.add('border-red-500');

                                        if (providerStep1Fields.includes(field)) {
                                            backToStep1 = true;
                                        }
                                    } else {
                                        console.warn('ไม่พบ input สำหรับ field:', field);
                                    }
                                });

                                if (backToStep1) {
                                    document.querySelector('.subTab-2').style.display = 'none';
                                    document.querySelector('.subTab-1').style.display = 'flex';
                                }
                            }

                        } catch (error) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด ติดต่อผู้ดูแลระบบ',
                                showConfirmButton: false,
                                timerProgressBar: true,
                            });
                        }
                    });

                    const main_phone = document.getElementById('main_phone');
                    const res_phone = document.getElementById('res_phone');
                    main_phone.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');

                        if (this.value.length > 10) {
                            this.value = this.value.slice(0, 10);
                        }

                        rmnClearPhoneDuplicateError(this); // แก้เบอร์ใหม่แล้ว ต้องเช็คซ้ำอีกครั้งตอนกด "ถัดไป"

                        const thaiPhonePattern = /^0[0-9]{9}$/;
                        const errorElement = this.nextElementSibling;

                        if (!thaiPhonePattern.test(this.value)) {
                            errorElement.innerText = 'เบอร์โทรศัพท์ไม่ถูกต้อง : ต้องไม่มี - , ช่องว่าง, ขึ้นต้นด้วย 0 และต้องมี 10 หลัก';
                        } else {
                            errorElement.innerText = '';
                        }
                    });

                    res_phone.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');

                        if (this.value.length > 10) {
                            this.value = this.value.slice(0, 10);
                        }

                        const thaiPhonePattern = /^0[0-9]{9}$/;
                        const errorElement = this.nextElementSibling;

                        if (!thaiPhonePattern.test(this.value)) {
                            errorElement.innerText = 'เบอร์โทรศัพท์ไม่ถูกต้อง : ต้องไม่มี - , ช่องว่าง, ขึ้นต้นด้วย 0 และต้องมี 10 หลัก';
                        } else {
                            errorElement.innerText = '';
                        }
                    });
                });

                // Nursing Registration
                document.addEventListener('DOMContentLoaded', () => {
                    const nursingRegistrationTab = document.getElementById('nursingRegistrationTab');
                    const nursingRegisFrm = document.getElementById('nursingRegisFrm');
                    const nextTabBtn = nursingRegisFrm.querySelector('#nextTab');
                    const medical_condition_choices = nursingRegisFrm.querySelectorAll('.medical_condition_choice');
                    const medical_condition = document.getElementById('medical_condition');
                    const medical_condition_wrap = document.getElementById('medical_condition_wrap');
                    const history_of_drug_allergy_choices = nursingRegisFrm.querySelectorAll('.history_of_drug_allergy_choice');
                    const history_of_drug_allergy = nursingRegisFrm.querySelector('#history_of_drug_allergy');
                    const history_of_drug_allergy_wrap = document.getElementById('history_of_drug_allergy_wrap');
                    const nursing_agreeTerms = nursingRegisFrm.querySelector('#nursing_agreeTerms');
                    const nursing_agreeNews  = nursingRegisFrm.querySelector('#nursing_agreeNews');
                    const nursing_truth      = nursingRegisFrm.querySelector('#nursing_truth');
                    const nursingCreate     = nursingRegisFrm.querySelector('#nursingCreate');
                    const back = nursingRegistrationTab.querySelector('.nursingback');

                    const nursingFirstName = nursingRegisFrm.querySelector('#nursingFirstName');
                    const nursingLastName = nursingRegisFrm.querySelector('#nursingLastName');
                    const nursingNickname = nursingRegisFrm.querySelector('#nursingNickname');
                    const nursingGender = nursingRegisFrm.querySelector('#nursingGender');
                    const nursingCareType = nursingRegisFrm.querySelector('#nursingCareType');
                    const nursingPhone = nursingRegisFrm.querySelector('#nursingPhone');
                    const nursingEmail = nursingRegisFrm.querySelector('#nursingEmail');
                    const nursingBirthDate = nursingRegisFrm.querySelector('#nursingBirthDate');
                    const nursingProfilePhoto = nursingRegisFrm.querySelector('#nursingProfilePhoto');
                    const nursingAvatarUpload = nursingRegisFrm.querySelector('#nursingAvatarUpload');
                    const nursingAvatarPreview = nursingRegisFrm.querySelector('#nursingAvatarPreview');

                    nursingAvatarUpload.addEventListener('click', () => {
                        nursingProfilePhoto.click();
                    });

                    nursingProfilePhoto.addEventListener('change', () => {
                        const file = nursingProfilePhoto.files[0];
                        if (file) {
                            nursingAvatarPreview.src = URL.createObjectURL(file);
                        }
                    });

                    flatpickr(nursingBirthDate, {
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
                    
                    back.addEventListener('click', function() {
                        const subTab1 = nursingRegisFrm.querySelector('.subTab-1');
                        const subTab2 = nursingRegisFrm.querySelector('.subTab-2');
                        if (subTab2.style.display !== 'none') {
                            // อยู่ subTab-2 -> ถอยไป subTab-1 ของฟอร์มพยาบาลเอง
                            subTab2.style.display = 'none';
                            subTab1.style.display = 'flex';
                        } else {
                            // อยู่ subTab-1 (จุดแรกสุดของฟอร์ม) -> ออกจากฟอร์มพยาบาล กลับไปเลือกประเภทบริการ
                            document.querySelectorAll('.step').forEach(step => {
                                step.style.display = 'none';
                            });
                            const serviceTypeTab = document.getElementById('serviceType');
                            if (serviceTypeTab) {
                                serviceTypeTab.style.display = 'flex';
                            }
                        }
                    });

                    nursingCreate.setAttribute('disabled', true);
                    nextTabBtn.setAttribute('disabled', true);

                    nextTabBtn.addEventListener('click', async e => {
                        e.preventDefault();
                        e.stopPropagation();

                        nextTabBtn.setAttribute('disabled', true);
                        lockAuthUI();
                        let phoneExists;
                        try {
                            phoneExists = await rmnCheckPhoneExists(nursingPhone.value);
                        } finally {
                            unlockAuthUI();
                        }
                        nextTabBtn.removeAttribute('disabled');

                        if (phoneExists) {
                            rmnShowPhoneDuplicateError(nursingPhone);
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'เบอร์โทรศัพท์นี้มีผู้ใช้งานแล้ว',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });
                            return;
                        }

                        nursingRegisFrm.querySelector('.subTab-1').style.display = 'none';
                        nursingRegisFrm.querySelector('.subTab-2').style.display = 'flex';
                    });

                    nursingPhone.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');

                        if (this.value.length > 10) {
                            this.value = this.value.slice(0, 10);
                        }

                        rmnClearPhoneDuplicateError(this); // แก้เบอร์ใหม่แล้ว ต้องเช็คซ้ำอีกครั้งตอนกด "ถัดไป"

                        const thaiPhonePattern = /^0[0-9]{9}$/;
                        const errorElement = this.nextElementSibling;

                        if (!thaiPhonePattern.test(this.value)) {
                            errorElement.innerText = 'เบอร์โทรศัพท์ไม่ถูกต้อง : ต้องไม่มี - , ช่องว่าง, ขึ้นต้นด้วย 0 และต้องมี 10 หลัก';
                        } else {
                            errorElement.innerText = '';
                        }
                    });

                    medical_condition_choices.forEach(choice => {
                        choice.addEventListener('click', function () {
                            // ลบ class selected ออกจากทุก element ก่อน
                            medical_condition_choices.forEach(c => c.classList.remove('selected'));

                            // เพิ่ม class selected ให้ element ที่ถูกคลิก
                            this.classList.add('selected');
                            let selectedValue = this.dataset.value;

                            if(selectedValue === 'yes') {
                                medical_condition_wrap.classList.remove('hidden');
                            } else {
                                medical_condition_wrap.classList.add('hidden');
                            }

                            medical_condition.value = selectedValue;
                        });
                    });

                    history_of_drug_allergy_choices.forEach(choice => {
                        choice.addEventListener('click', function() {
                            history_of_drug_allergy_choices.forEach(c => c.classList.remove('selected'));

                            this.classList.add('selected');
                            let selectedValue = this.dataset.value;

                            if (selectedValue === 'yes') {
                                history_of_drug_allergy_wrap.classList.remove('hidden');
                            } else {
                                history_of_drug_allergy_wrap.classList.add('hidden');
                            }

                            history_of_drug_allergy.value = selectedValue;
                        });
                    });

                    nursingRegisFrm.addEventListener('change', () => {
                        if (nursing_agreeTerms.checked && nursing_truth.checked && nursing_agreeNews.checked) {
                            nursingCreate.removeAttribute('disabled');
                            nursingCreate.classList.remove('disabled');
                        } else {
                            nursingCreate.setAttribute('disabled', true);
                            nursingCreate.classList.add('disabled');
                        }

                        if (
                            nursingProfilePhoto.files.length > 0 &&
                            nursingFirstName.value !== '' &&
                            nursingLastName.value !== '' &&
                            nursingNickname.value !== '' &&
                            nursingGender.value !== '' &&
                            nursingCareType.value !== '' &&
                            nursingPhone.value !== '' &&
                            nursingEmail.value !== '' &&
                            nursingBirthDate.value !== ''
                        ) {
                            nextTabBtn.removeAttribute('disabled');
                            nextTabBtn.classList.remove('disabled');
                        } else {
                            nextTabBtn.setAttribute('disabled', true);
                            nextTabBtn.classList.add('disabled');
                        }
                    });

                    nursingRegisFrm.addEventListener('submit', async e => {
                        e.preventDefault();
                        if (!(nursing_agreeTerms.checked && nursing_truth.checked && nursing_agreeNews.checked)) {
                            swal.fire({
                                icon: 'warning',
                                title: 'กรุณาติ๊กยอมรับเงื่อนไขก่อนดำเนินการต่อ',
                                showConfirmButton: true,
                            });
                            return;
                        }
                        const nursingFirstName = document.getElementById('nursingFirstName');
                        const nursingLastName  = document.getElementById('nursingLastName');
                        const nursingNickname = document.getElementById('nursingNickname');
                        const nursingGender   = document.getElementById('nursingGender');
                        const nursingPhone    = document.getElementById('nursingPhone');
                        const nursingEmail    = document.getElementById('nursingEmail');
                        const nursingBirthDate= document.getElementById('nursingBirthDate');
                        const nursingBlood    = document.getElementById('nursingBlood');
                        const nursingCareType = document.getElementById('nursingCareType');
                        const nursingProfilePhoto = document.getElementById('nursingProfilePhoto');
                        const medical_condition = document.getElementById('medical_condition');
                        const history_of_drug_allergy = document.getElementById('history_of_drug_allergy');
                        const medical_condition_detail = document.getElementById('medical_condition_detail');
                        const history_of_drug_allergy_detail = document.getElementById('history_of_drug_allergy_detail');

                        if (!nursingProfilePhoto.files.length) {
                            swal.fire({
                                icon: 'warning',
                                title: 'กรุณาอัปโหลดรูปถ่าย',
                                showConfirmButton: true,
                            });
                            return;
                        }

                        let formData = new FormData();
                        formData.append('nursingFirstName', nursingFirstName.value);
                        formData.append('nursingLastName', nursingLastName.value);
                        formData.append('nursingNickname', nursingNickname.value ?? '');
                        formData.append('nursingGender', nursingGender.value ?? '');
                        formData.append('nursingCareType', nursingCareType.value ?? '');
                        formData.append('nursingPhone', nursingPhone.value ?? '');
                        formData.append('nursingEmail', nursingEmail.value ?? '');
                        formData.append('nursingBirthDate', nursingBirthDate.value ?? '');
                        formData.append('nursingBlood', nursingBlood.value ?? '');
                        formData.append('profile_photo', nursingProfilePhoto.files[0]);
                        formData.append('medical_condition', medical_condition.value ?? '');
                        formData.append('history_of_drug_allergy', history_of_drug_allergy.value ?? '');
                        formData.append('medical_condition_detail', medical_condition_detail.value ?? '');
                        formData.append('history_of_drug_allergy_detail', history_of_drug_allergy_detail.value ?? '');
                        formData.append('action', 'nursing_register');
                        try {
                            const response = await axios.post('/wp-admin/admin-ajax.php', formData);
                            const result = response.data;
                            document.querySelectorAll('.error').forEach(el => el.textContent = '');

                            if (result.success === false) {

                                const errors = result.data.errors;
                                const fieldMap = {
                                    phone: 'nursingPhone',
                                    date_of_birth: 'nursingBirthDate',
                                };

                                Object.keys(errors).forEach(field => {
                                    const messages = errors[field];
                                    const id = fieldMap[field];
                                    const input = document.getElementById(id);

                                    if (input) {
                                        let parent = input.parentElement;
                                        while (parent && !parent.querySelector('.error')) {
                                            parent = parent.parentElement;
                                        }
                                        if (parent) {
                                            const errorLabel = parent.querySelector('.error');
                                            console.log("FOUND ERROR LABEL:", errorLabel);
                                            if (errorLabel) {
                                                errorLabel.textContent = messages[0];
                                            }
                                        }
                                    }
                                });

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: result.data.message,
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                });

                                return;
                            }

                            if( result.success == true ) {
                                // สมัครสำเร็จแล้ว แต่ยังไม่ login จริง — ต้องยืนยัน OTP ที่ส่งไปเบอร์ก่อน
                                const registerFrame = document.getElementById('section-678-21');
                                const registeredPhone = result.data?.phone || nursingPhone.value;

                                rmnShowRegistrationOtpModal(registeredPhone, () => {
                                    registerFrame.style.display = 'none';
                                    // ล้าง cache ผลลัพธ์ getCurrentUser() เก่า (มักเป็น null ตอนยังไม่ login) ก่อนเรียก updateUserUI() ซ้ำ
                                RMN_Utils.invalidateCurrentUserCache();
                                if (window.updateUserUI) window.updateUserUI();

                                    const popUpSuccessFrameHTML = `
                                    <div id="regisSuccessFrame" style="width: 100vw;height: 100vh;background-color: #000000ab;z-index: 999;position: fixed;top: 0;">
                                        <div style="display: flex;flex-direction: column;gap: 36px;width: 461px;height: 491px;border-radius: 12px;padding: 40px 24px 24px 24px;background-color: white;position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);">
                                            <div class="flex flex-col justify-center">
                                                <img src="https://ratemynurse.org/wp-content/uploads/2025/10/layer_1_success.png" width="281" height="200" class="mx-auto">
                                            </div>
                                            <div class="flex flex-col gap-[8px] justify-center items-center">
                                                <h4 class="font-semibold">สมัครสมาชิกสำเร็จ</h4>
                                                <span class="text-base">คุณสามารถลงประกาศงานได้ทันที</span>
                                                <span class="text-base">เพิ่มข้อมูลเพื่อให้เจอบริการของคุณง่ายยิ่งขึ้น</span>
                                            </div>
                                            <div class="flex flex-row justify-center gap-[24px] text-base font-medium">
                                                <button class="close_frame w-[172px] h-[48px] leading-[48px] text-[#5A5A5A] rounded-[10px] text-center border border-[#D9D8DC]">ไว้ทีหลัง</button>
                                                <a href="https://ratemynurse.org/my-profile/" class="w-[172px] h-[48px] leading-[48px] text-white rounded-[10px] bg-[#286F51] text-center">ลงประกาศเลย</a>
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    document.body.insertAdjacentHTML('beforeend', popUpSuccessFrameHTML);

                                    // ปุ่มปิด popup
                                    document.querySelector('.close_frame').addEventListener('click', () => {
                                        document.getElementById('regisSuccessFrame').remove();
                                    });
                                });
                            }

                        } catch (error) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'ไม่สามารถเชื่อมต่อกับเซอร์เวอร์ได้ / มีบางอย่างผิดพลาด กรุณาติดต่อ Admin',
                                showConfirmButton: false,
                                timerProgressBar: true,
                            });
                        }
                    })
                });

                document.addEventListener('DOMContentLoaded', () => {
                    // ===== LOCATION SELECTOR =====
                    // ตรวจสอบว่ามี element province หรือไม่
                    if (document.getElementById('province')) {
                        // สร้าง instance ของ LocationSelector
                        window.locationSelector = new RMN_LocationSelector({
                            provinceSelector: '#province',
                            districtSelector: '#district',
                            subDistrictSelector: '#sub_district'
                        });
                        
                    }
                });
            </script>
        HTML;
        return $html;
    }

    public function mb_navigation()
    {
        $guard   = AccessGuard::getInstance();
        $providerArr = ['NURSING', 'NURSING_HOME'];
        global $post;
        $page_slug = '';
        if ($post) {
            $page_slug = $post->post_name; // slug
        }
        $isHome = $page_slug === 'homepage' ? 'current_active' : '';
        $homeUrl = home_url('/');
        $jobBoardUrl = home_url('/job-board/');

        // หน้านี้ (รวมทั้ง markup ด้านล่าง) อาจถูก cache ข้าม visitor ได้ เช่น WordPress.com Batcache
        // ที่รู้จักแค่ cookie login มาตรฐานของ WP ไม่รู้จัก access_token/is_auth ที่ระบบนี้ตั้งเอง
        // จึงห้าม render ตามสถานะ login ที่นี่ (server-side) เด็ดขาด — ต้อง render ทั้ง 2 สถานะเสมอ
        // โดย default โชว์ปุ่ม "เข้าสู่ระบบ" (ปลอดภัยสุดถ้าเดาไม่ได้) แล้วให้ script ท้ายฟังก์ชันนี้
        // เช็คสถานะจริงผ่าน get_current_user ajax (ไม่ถูก cache) แล้วสลับ/เติมชื่อให้ทีหลัง
        $profileBtn = <<<HTML
            <div id="mb_profile" class="{$isHome} mb_tab flex flex-col justify-center items-center gap-[6px] h-[80px]  text-[#5A5A5A] hover:text-[#286F51] hover:font-medium relative" style="display:none;">
                <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                <span class="text-[12px]">บัญชี</span>
            </div>
            <div id="mb_profile_authentication" class="authen mb_tab flex flex-col justify-center items-center gap-[6px] h-[80px] text-[#5A5A5A] hover:text-[#286F51] hover:font-medium relative">
                <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                <span class="text-[12px] leading-none text-center">เข้าสู่ระบบ</span>
            </div>
        HTML;

        $profileDropdown = <<<HTML
                <div id="mb_profile_dropdown" class="hidden bg-[#F9F9FA] w-full h-full overflow-y-auto pb-[100px] fixed bottom-0 z-[-1]">
                    <div class="head_zone px-[16px] py-[24px] h-[150px] w-full overflow-hidden rounded-bl-[20px] rounded-br-[20px]" style="background: linear-gradient(330deg, #1D3C36 13.74%, #214B3D 26.43%, #28644B 52.72%, #2D7453 76.29%, #2F7A56 95.32%);">
                        <div class="flex flex-row ct-section justify-between items-center">
                            <span class="text-[#FFFFFF] text-[18px] font-semibold">บัญชีของฉัน</span>
                            <div class="flex flex-row gap-[16px] items-center">
                                <a class="gap-[8px] flex flex-row justify-center items-center bg-[#FFFFFF] text-[#5A5A5A] text-[14px] text-medium px-[14px] py-[3px] rounded-[20px]">
                                    <img src="https://ratemynurse.org/wp-content/uploads/2026/01/tabler-icon-eye-search.webp" loading="lazy" width="24" height="24" class="w-[24px] h-[24px]">
                                    <span>ดูประกาศของฉัน</span>
                                </a>
                                <span class="w-[32px] h-[32px] rounded-full bg-white flex justify-center items-center"><img class="bellIcon w-[21px] h-[21px]" src="https://ratemynurse.org/wp-content/uploads/2026/01/ri_notification-4-line.webp" width="21" height="21"></span>
                            </div>
                        </div>
                        <div id="mb_notification-bell" class="relative cursor-pointer">
                            <div id="noti-dropdown" class="hidden flex-col absolute top-full right-0 mt-2 w-[348px] bg-white rounded-[16px] shadow-lg border border-gray-200 z-50 w-full md:!w-[525px]">
                                <div class="p-4 flex-row justify-between items-center">
                                    <h3 class="text-lg font-semibold">การแจ้งเตือน</h3>
                                    <a href="#" onclick="readAllNotifications()">อ่านทั้งหมด</a>
                                </div>
                                <div class="flex flex-row flex-nowrap ct-section">
                                    <span data-tab="all_notice" class="tab_title active_tab w-[50%] text-center py-[10px]">ทั้งหมด</span>
                                    <span data-tab="unread_notice" class="tab_title w-[50%] text-center py-[10px]">ยังไม่ได้อ่าน</span>
                                </div>
                                <div id="all_notice" class="flex-col gap-[8px] tab_content active_tab"></div>
                                <div id="unread_notice" class="hidden flex-col gap-[8px] tab_content"></div>
                            </div>
                        </div>
                    </div>
                    <div class="gap-[16px] flex flex-col px-[16px] mt-[-75px]">
                        <div class="p-[12px] bg-white rounded-[12px] w-full h-[90px] flex flex-row justify-between items-center" style="box-shadow: 0px 3.57px 16px 0px #A3A3A30F;">
                            <div class="flex flex-row gap-[12px]">
                                <div class="profileImg w-[65px] h-[65px] rounded-full relative">
                                    <img src="https://ratemynurse.org/wp-content/uploads/2025/11/Frame-1000004756.webp" loading="lazy" width="65" height="65" class="w-[65px] h-[65px]">
                                    <img src="https://ratemynurse.org/wp-content/uploads/2025/12/certified_green.webp" width="24" height="24" class="w-[24px] h-[24px] absolute bottom-0 right-0">
                                </div>
                                <div class="flex flex-col gap-[8px] font-medium">
                                    <span id="mb_profile_displayname" class="text-[16px] text-[#1F1F1F]"></span>
                                    <a href="https://ratemynurse.org/my-account" class="text-[14px]">แก้ไขข้อมูลส่วนตัว</a>
                                </div>
                            </div>
                            <a href="https://ratemynurse.org/my-account">
                                <svg class="w-6 h-6 text-[#3D3D3D]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                        <div class="p-[12px] rounded-[12px] w-full h-[90px] flex flex-col" style="background: linear-gradient(330deg, #226054 13.74%, #1D8654 95.32%); box-shadow: 0px 3.57px 16px 0px #A3A3A30F;">
                            <div class="text-white text-[12px] sm:text-[14px] flex flex-column gap-[6px]">
                                <div class="flex flex-row gap-[8px]"><span>ระดับของฉัน :</span> <span>Professional</span></div>
                                <span>อัปเกรดแพ็กเกจเพื่อปลดล็อกฟีเจอร์อื่นๆ อีกมากมาย</span>
                                <a href="https://ratemynurse.org/subscription" class="flex flex-row gap-[8px]">
                                    <span>อัปเกรดแพ็กเกจ</span>
                                    <svg class="w-[16px] h-[16px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                            <div class="rounded-[12px] w-full h-[96px] grid grid-cols-2 gap-[16px] text-[16px] text-[#5A5A5A] font-medium">
                            <div id="mb_appointment_item" class="min-h-[96px] flex flex-col justify-center items-center gap-[12px] rounded-[12px] bg-white" style="box-shadow: 0px 3.57px 16px 0px #A3A3A30F;">
                                <img src="https://ratemynurse.org/wp-content/uploads/2026/02/Letter-Opened.webp" width="32" height="32" class="h-[32px] w-[32px]">
                                <span>การนัดหมาย</span>
                            </div>
                            <a href="https://ratemynurse.org/my-favorites" class="h-[96px] flex flex-col justify-center items-center gap-[12px] rounded-[12px] bg-white" style="box-shadow: 0px 3.57px 16px 0px #A3A3A30F;">
                                <img src="https://ratemynurse.org/wp-content/uploads/2026/02/Health.webp" width="32" height="32" class="h-[32px] w-[32px]">
                                <span>รายการโปรด</span>
                            </a>
                        </div>

                        <div class="gap-[12px] flex flex-col text-[16px] font-medium">
                            <span class="text-[#8C8A94]">ตั้งค่าบัญชี</span>
                            <div class="bg-white rounded-[8px] px-[12px] text-[#5A5A5A]">
                                <div class="border-b-[1px] border-[#F2F4F7]">
                                    <a href="https://ratemynurse.org/my-account" class="flex flex-row gap-[8px] w-full py-[18px]">
                                        <svg class="w-[24px] h-[24px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                        <span>แก้ไขข้อมูลส่วนตัว</span>
                                    </a>
                                </div>
                                <div class="border-b-[1px] border-[#F2F4F7]">
                                    <a href="http://" class="flex flex-row gap-[8px] w-full py-[18px]">
                                        <img src="https://ratemynurse.org/wp-content/uploads/2025/10/message.webp" class="w-[22px] h-[21px]" width="22" height="21" loading="lazy">
                                        <span>แก้ไขประกาศของฉัน</span>
                                    </a>
                                </div>
                                <div>
                                    <a href="https://ratemynurse.org/subscription" class="flex flex-row gap-[8px] w-full py-[18px]">
                                        <img src="https://ratemynurse.org/wp-content/uploads/2025/10/card.webp" class="w-[20px] h-[16px]" width="20" height="16" loading="lazy">
                                        <span>การสมัครสมาชิก</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="gap-[12px] flex flex-col text-[16px] font-medium">
                            <span class="text-[#8C8A94]">เงื่อนไขและความเป็นส่วนตัว</span>
                            <div class="bg-white rounded-[8px] px-[12px] text-[#5A5A5A]">
                                <div class="border-b-[1px] border-[#F2F4F7]">
                                    <a href="http://" class="flex flex-row gap-[8px] w-full py-[18px]">
                                        <svg class="w-[24px] h-[24px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                        <span>ข้อกำหนดและเงื่อนไข</span>
                                    </a>
                                </div>
                                <div>
                                    <a href="http://" class="flex flex-row gap-[8px] w-full py-[18px]">
                                        <img src="https://ratemynurse.org/wp-content/uploads/2025/10/message.webp" class="w-[22px] h-[21px]" width="22" height="21" loading="lazy">
                                        <span>นโยบายความเป็นส่วนตัว</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="gap-[12px] flex flex-col text-[16px] font-medium">
                            <div class="bg-white rounded-[8px] px-[12px] text-[#5A5A5A]">
                                <div>
                                    <a href="#" class="js-logout flex flex-row gap-[8px] w-full py-[18px]">
                                        <img src="https://ratemynurse.org/wp-content/uploads/2026/01/tabler-icon-logout.webp" class="w-[24px] h-[24px]" width="24" height="24" loading="lazy">
                                        <span>ออกจากระบบ</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <script>
                    // ดึงสถานะ login สดๆ ผ่าน ajax เสมอ (admin-ajax.php ไม่ถูก cache ต่างจากหน้านี้เอง)
                    // ห้ามตัดสิน "login แล้วหรือยัง" จากอะไรที่ฝังมากับ HTML ตรงๆ
                    // รอ window 'load' เสมอ — axios ถูก enqueue แบบ in_footer จึงโหลดทีหลัง
                    // inline script บล็อกนี้ (ที่อยู่กลาง body) ถ้าเรียก axios ทันทีจะชน ReferenceError
                    window.addEventListener('load', function () {
                        RMN_Utils.getCurrentUser()
                            .then(function (user) {
                                if (!user || !user.profile) return;

                                var name = user.profile.name || '';
                                var nickname = user.profile.nickname ? '(' + user.profile.nickname + ')' : '';
                                var nameEl = document.getElementById('mb_profile_displayname');
                                if (nameEl) nameEl.textContent = (name + ' ' + nickname).trim();

                                var authTab = document.getElementById('mb_profile_authentication');
                                var accountTab = document.getElementById('mb_profile');
                                if (authTab) authTab.style.display = 'none';
                                if (accountTab) accountTab.style.display = 'flex';

                                // MEMBER เท่านั้น: เปลี่ยน "การนัดหมาย" เป็น "ประวัติการติดต่อ" ลิงก์ไป my-contacts
                                // (provider role คงข้อความ/พฤติกรรมเดิมไว้)
                                var apptItem = document.getElementById('mb_appointment_item');
                                if (apptItem && user.user_type === 'MEMBER') {
                                    var apptLabel = apptItem.querySelector('span');
                                    if (apptLabel) apptLabel.textContent = 'ประวัติการติดต่อ';
                                    apptItem.style.cursor = 'pointer';
                                    apptItem.addEventListener('click', function () {
                                        window.location.href = 'https://ratemynurse.org/my-contacts/';
                                    });
                                }
                            })
                            .catch(function () {});
                    });
                </script>
            HTML;

        $myFav_Btn = <<<HTML
            <div id="mb_favorites" class="mb_tab flex flex-col justify-center items-center gap-[6px] h-[80px]  text-[#5A5A5A] hover:text-[#286F51] hover:font-medium relative">
                <a href="https://ratemynurse.org/my-favorites" class="flex flex-column gap-[8px]">
                    <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                    <span class="text-[12px] leading-none text-center">รายการโปรด</span>
                </a>    
            </div>
        HTML;
        $myOverview_Btn = <<<HTML
            <div id="mb_search" class="mb_tab flex flex-col justify-center items-center gap-[6px] h-[80px] text-[#5A5A5A] hover:text-[#286F51] hover:font-medium relative">
                <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                <span class="text-[12px] leading-none text-center">ค้นหา</span>
            </div>
        HTML;

        if ( in_array($guard->getProfileType(), $providerArr ) ) {
            $myFav_Btn = <<<HTML
                <div id="mb_contacts" class="mb_tab flex flex-col justify-center items-center gap-[6px] h-[80px]  text-[#5A5A5A] hover:text-[#286F51] hover:font-medium relative">
                    <a href="https://ratemynurse.org/my-contacts">
                        <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                        <span class="text-[12px] leading-none text-center">การนัดหมาย</span>
                    </a>    
                </div>
            HTML;
            $myOverview_Btn = <<<HTML
                <div id="mb_overview" class="mb_tab flex flex-col justify-center items-center gap-[6px] h-[80px]  text-[#5A5A5A] hover:text-[#286F51] hover:font-medium relative">
                    <a href="https://ratemynurse.org/my-overview">
                        <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                        <span class="text-[12px] leading-none text-center">ภาพรวม</span>
                    </a>    
                </div>
            HTML;
        }

        // ฝังฟอร์มค้นหาจริง (shortcode [search]) ไว้ใน panel กลาง เพื่อให้ปุ่มค้นหาบน
        // mobile bottom nav ใช้ได้ทุกหน้า ไม่ต้องพึ่งการฝัง [search] ผ่าน Oxygen เฉพาะหน้าแรก
        $searchPanelContent = do_shortcode('[search]');

        return <<<HTML
            <div id="mb_nav" class="grid grid-cols-5 px-[8px] rounded-tl-[24px] rounded-tr-[24px] bg-white hover:text-[#286F51] hover:font-medium" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
                <div id="mb_home" class="mb_tab {$isHome} text-[#5A5A5A] hover:text-[#286F51] relative">
                    <a href="{$homeUrl}" class="flex flex-col justify-center items-center gap-[6px] h-[80px]">
                        <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                        <span class="text-[12px] leading-none text-center">หน้าหลัก</span>
                    </a>
                </div>
                {$myOverview_Btn}

                {$myFav_Btn}

                <div id="mb_board" class="mb_tab text-[#5A5A5A] hover:text-[#286F51] hover:font-medium relative">
                    <a href="{$jobBoardUrl}" class="flex flex-col justify-center items-center gap-[6px] h-[80px]">
                        <span class="mb_icon w-[24px] h-[24px] mx-auto block"></span>
                        <span class="text-[12px] leading-none text-center">บอร์ดประกาศ</span>
                    </a>
                </div>
                {$profileBtn}
            </div>
            {$profileDropdown}
            <div id="mb_authentication" class="hidden bg-white w-full h-full overflow-y-auto pb-[100px] fixed bottom-0 z-[-1] p-[15px]">
                Authentication view
            </div>
            <div id="mb_search_panel" class="hidden bg-white w-full h-full overflow-y-auto fixed top-0 left-0 z-[999999]">
                <div class="flex flex-row justify-between items-center p-[15px] border-b border-[#ECECED]">
                    <span class="text-[16px] font-semibold text-[#286F51]">ค้นหา</span>
                    <span id="mb_search_close" class="cursor-pointer">
                        <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"> <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/> </svg>
                    </span>
                </div>
                {$searchPanelContent}
            </div>

        HTML;
    }
}

new Authentication();