@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form method="post" 
            class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" 
            action="{{ route('employee.store') }}">
            @csrf
            <input type="hidden" name="user_type" value="EMPLOYEE">
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">สร้างสมาชิกผู้ใช้งาน (Admin)</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน เพื่อสร้างบัญชี</span>
            </div>

            @if(session('error'))
                <div class="flex flex-col justify-start bg-red-500 p-[16px] rounded-md text-white">
                    <span>
                        {{ session('error') }}
                    </span>
                 </div>
            @endif

            <div class="flex flex-col gap-[32px]">
                <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                        <path d="M5 21C5 17.134 8.13401 14 12 14C15.866 14 19 17.134 19 21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                            stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="text-md text-white font-semibold">ข้อมูลทั่วไปของผู้ใช้งาน</span>
                </span>
                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="firstname">ชื่อผู้ใช้งาน <span class="req text-red-600">*</span></label>
                        <input required type="text" name="firstname" id="firstname" placeholder="ระบุชื่อ"
                            class="border rounded-lg px-3 py-2" value="{{ old('firstname') }}"/>
                        <label class="error text-xs text-red-600"></label>
                        @error('firstname')
                            <span class="error text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="lastname">นามสกุล <span class="req text-red-600">*</span></label>
                        <input required type="text" name="lastname" id="lastname" placeholder="ระบุนามสกุล"
                            class="border rounded-lg px-3 py-2" value="{{ old('lastname') }}"/>
                        <label class="error text-xs text-red-600"></label>
                        @error('lastname')
                            <span class="error text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="email">อีเมล์ <span class="req text-red-600">*</span></label>
                        <input required type="email" name="email" id="email" placeholder="ระบุอีเมล์"
                                class="border rounded-lg px-3 py-2" value="{{ old('email') }}"/>
                        @error('email')
                            <span class="error text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="phone">เบอร์โทรศัพท์ <span class="req text-red-600">*</span></label>
                        <input type="text" name="phone" id="phone" placeholder="ระบุเบอร์โทรศัพท์ เริ่มต้นด้วย 0 และไม่ต้องมี -"
                            class="border rounded-lg px-3 py-2"
                            maxlength="10"/>
                        <label id="phone_error" class="error text-xs text-red-600"></label>
                        @error('phone')
                            <span class="error text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="password">รหัสผ่าน <span class="req text-red-600">*</span></label>
                        <input required type="password" name="password" id="password" placeholder="ระบุรหัสผ่าน"
                            class="border rounded-lg px-3 py-2"/>
                        <label id="password_error" class="error text-xs text-red-600"></label>
                        @error('password')
                            <span class="error text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="password_confirmation">ยืนยันรหัสผ่าน <span class="req text-red-600">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="ยืนยันรหัสผ่าน"
                            class="border rounded-lg px-3 py-2"/>
                        <label id="confirm_error" class="error text-xs text-red-600"></label>
                        @error('confirm_error')
                            <span class="error text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-center">
                    <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึก</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('javascript')
    <script>
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("password_confirmation");
    const passwordError = document.getElementById("password_error");
    const confirmError = document.getElementById("confirm_error");

    function validatePassword() {
        const value = password.value;
        const regex = /^(?=.*[A-Z]).{6,}$/; // มีตัวพิมพ์ใหญ่ + ยาว >= 6

        if (!regex.test(value)) {
        passwordError.textContent = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร และมีตัวพิมพ์ใหญ่อย่างน้อย 1 ตัว";
        return false;
        } else {
        passwordError.textContent = "";
        return true;
        }
    }

    function validateConfirmPassword() {
        if (password.value !== confirmPassword.value) {
        confirmError.textContent = "รหัสผ่านไม่ตรงกัน";
        return false;
        } else {
        confirmError.textContent = "";
        return true;
        }
    }

    password.addEventListener("input", validatePassword);
    confirmPassword.addEventListener("input", validateConfirmPassword);

    // ถ้าคุณมี form ใช้ submit
    const form = document.querySelector("form"); // ปรับ selector ให้ตรงกับ form จริง
    if (form) {
        form.addEventListener("submit", function (e) {
        if (!validatePassword() || !validateConfirmPassword()) {
            e.preventDefault(); // ป้องกันการ submit ถ้าไม่ผ่าน
        }
        });
    }

    const phone = document.getElementById("phone");
    const phoneError = document.getElementById("phone_error");

    function validatePhone() {
        const value = phone.value.trim();
        const regex = /^0\d{9}$/; // เริ่มต้นด้วย 0 และมีตัวเลขอีก 9 ตัว รวมเป็น 10 หลัก

        if (!regex.test(value)) {
        phoneError.textContent = "เบอร์โทรศัพท์ต้องขึ้นต้นด้วย 0 และมีทั้งหมด 10 หลัก โดยไม่ต้องมี -";
        return false;
        } else {
        phoneError.textContent = "";
        return true;
        }
    }

    phone.addEventListener("input", validatePhone);

    if (form) {
        form.addEventListener("submit", function (e) {
        if (!validatePhone()) {
            e.preventDefault();
        }
        });
    }
    </script>

@endsection