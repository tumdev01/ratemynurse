/**
 * RMN Configuration
 * Global configuration สำหรับ RateMyNurse
 */
const RMN_CONFIG = {
  // API Endpoints
  // *** ค่านี้ต้องเป็น production URL เสมอบนไฟล์ deploy นี้ *** — working copy (local dev) ใช้
  // "http://localhost:9000/api" แทน (ชี้ Laravel container ตรงๆ ผ่าน port ที่ expose ไว้ เพราะ
  // ค่านี้ถูกเรียกจาก browser ตรงๆ ไม่ผ่าน wp_remote_post ฝั่ง server จึง mu-plugin
  // local-dev-overrides.php ดักไม่ได้) — ไฟล์นี้กับ working copy ตั้งใจให้ต่างกันตรงบรรทัดนี้บรรทัดเดียว
  // อย่า sync ทับบรรทัดนี้เด็ดขาด
  api: {
    baseUrl: "https://services.ratemynurse.org/api",
    wpAjax: "/wp-admin/admin-ajax.php",
  },

  // Site URLs
  site: {
    baseUrl: "https://ratemynurse.org",
    profilePath: "/my-profile/",
    accountPath: "/my-account/",
    subscriptionPath: "/subscription/",
    favoritePath: "/my-favorite/",
    contactsPath: "/my-contacts/",
    overviewPath: "/my-overview/",
  },

  // Asset URLs
  assets: {
    defaultAvatar:
      "https://ratemynurse.org/wp-content/uploads/2025/11/cropped-541800960_4063110697234606_2692539723161017286_n.jpg",
    successIcon:
      "https://ratemynurse.org/wp-content/uploads/2025/10/layer_1_success.png",
    emptyNoti:
      "https://ratemynurse.org/wp-content/uploads/2026/01/NotificationEmpty.webp",
    highlightBg:
      "https://ratemynurse.org/wp-content/uploads/2025/10/Highlight-Rate-Mu-Nurse-mobile.webp",
    icons: {
      pie: "https://ratemynurse.org/wp-content/uploads/2025/10/pie.webp",
      calendar:
        "https://ratemynurse.org/wp-content/uploads/2025/10/calendar.webp",
      user: "https://ratemynurse.org/wp-content/uploads/2025/10/user.webp",
      message:
        "https://ratemynurse.org/wp-content/uploads/2025/10/message.webp",
      card: "https://ratemynurse.org/wp-content/uploads/2025/10/card.webp",
      heart: "https://ratemynurse.org/wp-content/uploads/2025/10/heart.webp",
      bell: "https://ratemynurse.org/wp-content/uploads/2025/12/bell.webp",
      letterOpened:
        "https://ratemynurse.org/wp-content/uploads/2025/12/LetterOpened.webp",
    },
    notiIcons: {
      user: "https://ratemynurse.org/wp-content/uploads/2025/12/icon-bell.png",
      subscription:
        "https://ratemynurse.org/wp-content/uploads/2025/12/icon-expired.png",
      ads: "https://ratemynurse.org/wp-content/uploads/2025/12/icon-crown.png",
    },
  },

  // Validation Patterns
  validation: {
    thaiPhone: /^0[0-9]{9}$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  },

  // Toast Settings
  toast: {
    position: "top-end",
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
  },
};

// Freeze เพื่อป้องกันการแก้ไข
Object.freeze(RMN_CONFIG);
