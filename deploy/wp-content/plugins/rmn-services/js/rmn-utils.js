/**
 * RMN Utility Functions
 * Helper functions ที่ใช้ร่วมกันทั้งระบบ
 */

// เก็บ promise ของ get_current_user ไว้ที่ module scope (นอก RMN_Utils ที่ถูก freeze)
// กันไม่ให้หลาย script บนหน้าเดียวกัน (desktop nav, mobile nav, job-post ฯลฯ) ยิง ajax ซ้ำกันเอง
let _rmnCurrentUserPromise = null;

const RMN_Utils = {
  /**
   * Format Thai Date
   */
  formatThaiDate(dateString) {
    if (!dateString) return "";

    const date = new Date(dateString);
    if (isNaN(date.getTime())) return "";

    const options = {
      year: "numeric",
      month: "long",
      day: "numeric",
      timeZone: "Asia/Bangkok",
    };

    return date.toLocaleDateString("th-TH", options);
  },

  /**
   * Validate Thai Phone Number
   */
  validateThaiPhone(phoneNumber) {
    if (!phoneNumber) return false;
    return RMN_CONFIG.validation.thaiPhone.test(phoneNumber);
  },

  /**
   * Validate Email
   */
  validateEmail(email) {
    if (!email) return false;
    return RMN_CONFIG.validation.email.test(email);
  },

  /**
   * Show Toast Message
   */
  showToast(icon, title, timer = null) {
    return Swal.fire({
      toast: true,
      position: RMN_CONFIG.toast.position,
      icon: icon,
      title: title,
      showConfirmButton: false,
      timer: timer || RMN_CONFIG.toast.timer,
      timerProgressBar: RMN_CONFIG.toast.timerProgressBar,
    });
  },

  /**
   * Show Loading Toast
   */
  showLoading(title = "กำลังโหลด...") {
    return Swal.fire({
      toast: true,
      position: RMN_CONFIG.toast.position,
      title: title,
      showConfirmButton: false,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  },

  /**
   * Show Success Popup Modal
   */
  showSuccessPopup(options = {}) {
    const defaults = {
      title: "สมัครสมาชิกสำเร็จ",
      message1: "คุณสามารถลงประกาศงานได้ทันที",
      message2: "เพิ่มข้อมูลเพื่อให้เจอบริการของคุณง่ายยิ่งขึ้น",
      laterUrl: null,
      actionUrl: RMN_CONFIG.site.baseUrl + RMN_CONFIG.site.profilePath,
      actionText: "ลงประกาศเลย",
      laterText: "ไว้ทีหลัง",
      onClose: null,
    };

    const settings = { ...defaults, ...options };

    const popup = document.createElement("div");
    popup.id = "rmnSuccessPopup";
    popup.className =
      "fixed inset-0 bg-black bg-opacity-70 z-[999] flex items-center justify-center p-4";

    popup.innerHTML = `
            <div class="flex flex-col gap-9 w-full max-w-[461px] bg-white rounded-xl p-10 shadow-2xl animate-fadeIn">
                <div class="flex justify-center">
                    <img src="${RMN_CONFIG.assets.successIcon}" width="281" height="200" alt="Success" class="animate-bounce-once">
                </div>
                <div class="flex flex-col gap-2 text-center">
                    <h4 class="font-semibold text-xl text-gray-900">${settings.title}</h4>
                    <p class="text-base text-gray-600">${settings.message1}</p>
                    <p class="text-base text-gray-600">${settings.message2}</p>
                </div>
                <div class="flex flex-col sm:flex-row justify-center gap-4 text-base font-medium">
                    <button class="rmn-close-popup w-full sm:w-[172px] h-12 text-gray-600 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                        ${settings.laterText}
                    </button>
                    <a href="${settings.actionUrl}" class="w-full sm:w-[172px] h-12 leading-[48px] text-white rounded-lg bg-[#286F51] text-center hover:bg-[#1e5a3e] transition-colors">
                        ${settings.actionText}
                    </a>
                </div>
            </div>
        `;

    document.body.appendChild(popup);

    // Event listeners
    const closeBtn = popup.querySelector(".rmn-close-popup");
    closeBtn.addEventListener("click", () => {
      popup.remove();
      if (settings.laterUrl) {
        window.location.href = settings.laterUrl;
      }
      if (settings.onClose) {
        settings.onClose();
      }
    });

    // Close on backdrop click
    popup.addEventListener("click", (e) => {
      if (e.target === popup) {
        closeBtn.click();
      }
    });

    return popup;
  },

  /**
   * Get or Create Error Span
   */
  getErrorSpan(input) {
    let span = input.parentNode.querySelector(".error");
    if (!span) {
      span = document.createElement("span");
      span.className = "error text-red-500 text-sm mt-1 hidden";
      input.insertAdjacentElement("afterend", span);
    }
    return span;
  },

  /**
   * Clear All Form Errors
   */
  clearAllErrors(form = document) {
    form.querySelectorAll(".error").forEach((el) => {
      el.textContent = "";
      el.classList.add("hidden");
    });

    form.querySelectorAll(".border-red-500").forEach((el) => {
      el.classList.remove("border-red-500");
    });
  },

  /**
   * Display Form Errors
   */
  displayFormErrors(errors, fieldMap = {}, form = document) {
    this.clearAllErrors(form);

    Object.entries(errors).forEach(([field, messages]) => {
      const inputId = fieldMap[field] || field;
      const input = form.querySelector(`#${inputId}`);

      if (input) {
        const errorSpan = this.getErrorSpan(input);
        errorSpan.textContent = Array.isArray(messages)
          ? messages[0]
          : messages;
        errorSpan.classList.remove("hidden");
        input.classList.add("border-red-500");
      }
    });
  },

  /**
   * Sanitize Phone Input (เฉพาะตัวเลข 10 หลัก)
   */
  sanitizePhoneInput(input) {
    input.value = input.value.replace(/[^0-9]/g, "").slice(0, 10);
    return input.value;
  },

  /**
   * Setup Phone Input Validation
   */
  setupPhoneValidation(input) {
    input.addEventListener("input", () => {
      this.sanitizePhoneInput(input);

      const errorSpan = this.getErrorSpan(input);
      if (input.value && !this.validateThaiPhone(input.value)) {
        errorSpan.textContent =
          "เบอร์โทรศัพท์ไม่ถูกต้อง: ต้องขึ้นต้นด้วย 0 และมี 10 หลัก";
        errorSpan.classList.remove("hidden");
      } else {
        errorSpan.textContent = "";
        errorSpan.classList.add("hidden");
      }
    });
  },

  /**
   * Truncate Text
   */
  truncateText(text, maxLength) {
    if (!text) return "";
    const chars = [...text];
    return chars.length > maxLength
      ? chars.slice(0, maxLength).join("") + "..."
      : text;
  },

  /**
   * Debounce Function
   */
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * Get Cookie Value
   */
  getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
  },

  /**
   * Check if User is Authenticated
   */
  isAuthenticated() {
    return this.getCookie("access_token") !== null;
  },

  /**
   * Format Number with Commas
   */
  formatNumber(num) {
    if (!num) return "0";
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  },

  /**
   * Parse JSON Safely
   */
  parseJSON(jsonString, defaultValue = null) {
    try {
      return JSON.parse(jsonString);
    } catch (e) {
      console.error("JSON Parse Error:", e);
      return defaultValue;
    }
  },

  /**
   * ดึงข้อมูล user ปัจจุบันผ่าน ajax action "get_current_user" — cache ผลลัพธ์ไว้เป็น promise เดียว
   * ตลอดอายุของหน้า (ไม่ refetch ซ้ำ) กันหลาย script บนหน้าเดียวกันยิง ajax เดียวกันซ้ำหลายรอบ
   * (ยังต้องดึงผ่าน ajax เสมอ ห้ามเชื่อข้อมูลที่ฝังมากับ HTML ตรงๆ เพราะหน้าอาจถูก cache ข้าม visitor ได้)
   */
  getCurrentUser() {
    if (!_rmnCurrentUserPromise) {
      _rmnCurrentUserPromise = axios
        .get("/wp-admin/admin-ajax.php", {
          params: { action: "get_current_user" },
        })
        .then((response) => {
          if (response.data && response.data.success) {
            return (response.data.data && response.data.data.data) || null;
          }
          return null;
        })
        .catch(() => null);
    }
    return _rmnCurrentUserPromise;
  },
};

// Freeze เพื่อป้องกันการแก้ไข
Object.freeze(RMN_Utils);
