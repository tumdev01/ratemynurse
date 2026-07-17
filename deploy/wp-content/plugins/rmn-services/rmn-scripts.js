function clearCompareIfNoToken() {
  // ถ้า auth flag ยังไม่มา → อย่าเพิ่งทำอะไร
  if (typeof window.RMN_AUTH === "undefined") {
    return false;
  }

  if (!RMN_AUTH.hasToken) {
    localStorage.removeItem("compare_nurse_items");

    const modal = document.getElementById("compare-modal");
    if (modal) modal.remove();

    return true;
  }

  return false;
}

// เก็บ icon เปรียบเทียบ/ถูกใจไว้ค้าง ๆ (ไม่ต้อง hover) ถ้ารายการนั้นถูกกดไปแล้ว
// เรียกซ้ำได้เรื่อย ๆ แบบ idempotent — ใช้ localStorage เทียบสถานะปัจจุบันเสมอ
function syncActiveIconStates() {
  const nurseCompareIds =
    JSON.parse(localStorage.getItem("compare_nurse_items")) || [];
  const homeCompareIds =
    JSON.parse(localStorage.getItem("compare_home_item")) || [];

  document.querySelectorAll(".compare-nurse[data-nurseid]").forEach((el) => {
    el.classList.toggle("active", nurseCompareIds.includes(el.dataset.nurseid));
  });
  document
    .querySelectorAll(".compare-nursing_home[data-homeid]")
    .forEach((el) => {
      el.classList.toggle("active", homeCompareIds.includes(el.dataset.homeid));
    });
}

function compareNurse() {
  document.addEventListener("click", async function (e) {
    const compareBtn = e.target.closest(".compare-nurse");
    if (!compareBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const nurse_id = compareBtn.getAttribute("data-nurseid");
    if (!nurse_id) return;

    // 📊 Track click_compare (skip silently ถ้า trackAction ไม่ได้โหลด)
    if (typeof trackAction === "function") {
      const profileId =
        compareBtn.dataset.profile || compareBtn.dataset.profileId || nurse_id;
      const type =
        compareBtn.dataset.type || compareBtn.dataset.profileType || "nursing";
      compareBtn.style.pointerEvents = "none";
      compareBtn.style.opacity = "0.6";
      const result = await trackAction("click_compare", profileId, type);
      compareBtn.style.pointerEvents = "";
      compareBtn.style.opacity = "";
      if (!result.allowed) return;
    }

    let compare_nurse_items =
      JSON.parse(localStorage.getItem("compare_nurse_items")) || [];

    const index = compare_nurse_items.indexOf(nurse_id);
    if (index !== -1) {
      compare_nurse_items.splice(index, 1);
    }

    compare_nurse_items.push(nurse_id);

    if (compare_nurse_items.length > 3) {
      compare_nurse_items = compare_nurse_items.slice(
        compare_nurse_items.length - 3,
      );
    }

    localStorage.setItem(
      "compare_nurse_items",
      JSON.stringify(compare_nurse_items),
    );

    const modal = document.getElementById("compare-modal");
    const isExpanded = modal && modal.classList.contains("expanded");

    renderCompareModal();

    if (isExpanded) {
      setTimeout(() => {
        const newModal = document.getElementById("compare-modal");
        if (newModal) {
          newModal.classList.remove("collapsed");
          newModal.classList.add("expanded");
        }
      }, 0);
    }
  });
}

function renderCompareModal() {
  let compare_modal = document.getElementById("compare-modal");

  // เก็บ state เดิมไว้ก่อน
  let wasExpanded = false;
  if (compare_modal) {
    wasExpanded = compare_modal.classList.contains("expanded");
    compare_modal.remove();
  }

  // สร้างใหม่
  compare_modal = document.createElement("div");
  compare_modal.id = "compare-modal";
  compare_modal.classList.add(
    "compare-modal",
    "fixed",
    "bottom-0",
    "right-0",
    "w-full",
    "max-w-[430px]",
    "shadow-lg",
    "rounded-t-lg",
    "overflow-hidden",
    "flex",
    "flex-col",
  );

  // ใส่ class ตาม state เดิม
  if (wasExpanded) {
    compare_modal.classList.add("expanded");
  } else {
    compare_modal.classList.add("collapsed");
  }

  compare_modal.style.cssText =
    "position: fixed !important; display: flex !important;";

  const compareItems =
    JSON.parse(localStorage.getItem("compare_nurse_items")) || [];

  // สร้าง modal structure ก่อน (แสดง loading)
  compare_modal.innerHTML = `
        <div class="compare-modal-header flex justify-between items-center p-4 bg-[#286F51] cursor-pointer">
            <h2 class="text-[16px] flex flex-row gap-[10px] text-white">
                <img src="https://ratemynurse.org/wp-content/uploads/2025/12/compare-white.webp" width="20" height="18" loading="lazy">
                <span>เปรียบเทียบบริการ</span>
                <span class="count">${compareItems.length}</span>
            </h2>
            <button class="close-compare-modal cursor-pointer font-bold text-xl text-white hover:text-gray-200">
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                </svg>
            </button>
        </div>
        <div class="compare-modal-body flex flex-col gap-[12px] bg-white p-4">
            <p class="text-sm m-0 flex flex-row gap-[8px]">
                <img src="https://ratemynurse.org/wp-content/uploads/2025/12/mingcute_warning-fill.webp" width="16" height="16" loading="lazy">
                <span>คุณสามารถเปรียบเทียบประเภทเดียวกันได้สูงสุด 3 รายการ</span>
            </p>
            <div class="compare-modal-list">
                <div class="text-center py-4 text-gray-500">กำลังโหลดข้อมูล...</div>
            </div>
            <div class="compare-modal-footer">
                <button id="go-to-compare" class="w-full compare-modal-submit cursor-pointer text-white !text-[16px] px-[14px] py-[8px] rounded-[10px] block text-center bg-[#286F51] hover:bg-[#1f5a3f]">เปรียบเทียบ</button>
            </div>
        </div>
    `;

  const goToCompareBtn = compare_modal.querySelector("#go-to-compare");
  goToCompareBtn?.addEventListener("click", () => {
    const compareItems =
      JSON.parse(localStorage.getItem("compare_nurse_items")) || [];
    const idsParam = compareItems.join(",");
    window.location.href = `https://ratemynurse.org/comparison?n=${idsParam}`;
  });

  document.body.appendChild(compare_modal);

  // ดึงข้อมูลจาก API
  const formData = new URLSearchParams();
  formData.append("action", "compare_nurse");
  formData.append("items", JSON.stringify(compareItems));

  axios
    .post("/wp-admin/admin-ajax.php", formData)
    .then((response) => {
      // ดึงข้อมูลจาก response (รองรับหลายชั้น)
      const nurses =
        response.data?.data?.data?.data || response.data?.data || response.data;

      // สร้าง HTML สำหรับแต่ละพยาบาล
      let modalContent = "";

      if (Array.isArray(nurses) && nurses.length > 0) {
        nurses.forEach((nurse) => {
          // คำนวณ rating เฉลี่ย
          let avgRating = 0;
          if (nurse.rates && nurse.rates.length > 0) {
            const totalRating = nurse.rates.reduce(
              (sum, rate) => sum + (rate.rating || 0),
              0,
            );
            avgRating = (totalRating / nurse.rates.length).toFixed(1);
          }

          modalContent += `
                        <div data-nurseid="${nurse.id}" class="compare-nurse-item flex gap-2 py-[12px] border-b justify-between items-center transition-transform duration-300 hover:scale-[1.02] bg-white">
                            <div class="compare_nurse_profile">
                                <div class="flex flex-row gap-[8px]">
                                    <div class="w-[75px] h-[75px] rounded-md overflow-hidden bg-gray-200">
                                        ${
                                          nurse.coverImage
                                            ? `<img src="${nurse.coverImage}" alt="${nurse.firstname}" class="w-full h-full object-cover">`
                                            : `<div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>`
                                        }
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="text-[16px] font-semibold profile-name">
                                            ${nurse.firstname} ${nurse.lastname}
                                        </div>
                                        <div class="profile-rate flex items-center gap-1 text-sm">
                                            <span class="text-yellow-500">★</span>
                                            <span>${avgRating > 0 ? avgRating : "ยังไม่มีรีวิว"}</span>
                                        </div>
                                        <div class="text-[14px] profile-cost">
                                            ${nurse.profile?.cost ? `฿${nurse.profile.cost}` : "ไม่ระบุราคา"}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="remove-compare-nurse cursor-pointer">
                                <svg class="w-6 h-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            </button>
                        </div>
                    `;
        });
      } else {
        modalContent =
          '<div class="text-center py-4 text-gray-500">ไม่พบข้อมูล</div>';
      }

      // อัพเดท modal content
      const modalList = compare_modal.querySelector(".compare-modal-list");
      if (modalList) {
        modalList.innerHTML = modalContent;
      }

      // เพิ่ม event listeners สำหรับปุ่มลบ
      setupRemoveButtons(compare_modal);
    })
    .catch((err) => {
      console.error(err);
      const modalList = compare_modal.querySelector(".compare-modal-list");
      if (modalList) {
        modalList.innerHTML =
          '<div class="text-center py-4 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
      }
    });

  // Event: คลิก header เพื่อ toggle
  const header = compare_modal.querySelector(".compare-modal-header");
  header.addEventListener("click", (e) => {
    if (e.target.closest(".close-compare-modal")) return;
    compare_modal.classList.toggle("collapsed");
    compare_modal.classList.toggle("expanded");
  });

  // Event: ปุ่ม X - แค่พับลง
  compare_modal
    .querySelector(".close-compare-modal")
    ?.addEventListener("click", (e) => {
      e.stopPropagation();
      compare_modal.classList.remove("expanded");
      compare_modal.classList.add("collapsed");
    });
}

function setupRemoveButtons(compare_modal) {
  compare_modal.querySelectorAll(".remove-compare-nurse").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const nurseId = btn
        .closest("[data-nurseid]")
        .getAttribute("data-nurseid");
      let compareItems =
        JSON.parse(localStorage.getItem("compare_nurse_items")) || [];
      compareItems = compareItems.filter((id) => id !== nurseId);
      localStorage.setItem("compare_nurse_items", JSON.stringify(compareItems));

      // ถ้าไม่มี item เหลือแล้ว ซ่อนทั้งหมด
      if (compareItems.length === 0) {
        compare_modal.style.display = "none";
      } else {
        // เก็บ state ก่อน render ใหม่
        const isExpanded = compare_modal.classList.contains("expanded");
        renderCompareModal();

        if (isExpanded) {
          setTimeout(() => {
            const newModal = document.getElementById("compare-modal");
            if (newModal) {
              newModal.classList.remove("collapsed");
              newModal.classList.add("expanded");
            }
          }, 0);
        }
      }
    });
  });
}

function favoriteProvider() {
  document.addEventListener("click", async function (e) {
    const btn = e.target.closest(".add-favorite");
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const profile_id = btn.dataset.profileId;
    const profile_type = btn.dataset.profileType;

    if (!profile_id || !profile_type) return;

    // 📊 Track click_favorite (skip silently ถ้า trackAction ไม่ได้โหลด)
    if (typeof trackAction === "function") {
      btn.style.pointerEvents = "none";
      const result = await trackAction(
        "click_favorite",
        profile_id,
        profile_type,
      );
      btn.style.pointerEvents = "";
      if (!result.allowed) return;
    }

    playFavoriteSound();

    // Toggle heart UI immediately (optimistic)
    btn.classList.toggle("favorited");

    const formData = new URLSearchParams();
    formData.append("action", "toggle_favorite");
    formData.append("profile_id", profile_id);
    formData.append("profile_type", profile_type);

    axios
      .post("/wp-admin/admin-ajax.php", formData)
      .then((response) => {
        const result = response.data?.data || response.data;
        if (result.favorited) {
          btn.classList.add("favorited");
        } else {
          btn.classList.remove("favorited");
        }
      })
      .catch((err) => {
        console.error("Favorite toggle failed:", err);
        // Rollback on error
        btn.classList.toggle("favorited");
      });
  });
}

let favoriteSound = null;

function initFavoriteSound() {
  if (favoriteSound) return;

  favoriteSound = new Audio(
    "https://ratemynurse.org/wp-content/plugins/rmn-services/mixkit-correct-answer-tone-2870.wav",
  );
  favoriteSound.volume = 0.2;
  favoriteSound.preload = "auto";
}

function playFavoriteSound() {
  if (!favoriteSound) {
    initFavoriteSound();
  }

  favoriteSound.currentTime = 0;
  favoriteSound.play().catch(() => {});
}

function formatThaiDate(isoDate) {
  const date = new Date(isoDate);

  const thaiMonths = [
    "ม.ค.",
    "ก.พ.",
    "มี.ค.",
    "เม.ย.",
    "พ.ค.",
    "มิ.ย.",
    "ก.ค.",
    "ส.ค.",
    "ก.ย.",
    "ต.ค.",
    "พ.ย.",
    "ธ.ค.",
  ];

  const day = date.getDate();
  const month = thaiMonths[date.getMonth()];
  const year = date.getFullYear() + 543;
  const hours = date.getHours().toString().padStart(2, "0");
  const minutes = date.getMinutes().toString().padStart(2, "0");

  return `${day} ${month} ${year} ${hours}:${minutes} น.`;
  return `
        <div class="flex flex-row gap-[8px] text-sm">
            <span>${day} ${month} ${year}</span>
            <span>${hours}:${minutes} น.</span>
        </div>
    `;
}

function readAllNotifications() {
  const formData = new URLSearchParams();
  formData.append("action", "read_all_notifications");
  axios
    .post("/wp-admin/admin-ajax.php", formData)
    .then((response) => {})
    .catch((err) => {
      console.error(err);
    });
}

function markNotificationAsRead(event, el) {
  event.preventDefault();

  const notiId = el.dataset.noti;

  // === optimistic UI ===
  el.classList.add("noti-read");

  fetch("/wp-admin/admin-ajax.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action: "rmn_set_notification_as_read",
      noti_id: notiId,
    }),
  })
    .then((res) => res.json())
    .then((res) => {
      if (res.success) {
        setTimeout(() => {
          el.classList.add("noti-hide");
        }, 400);

        setTimeout(() => el.remove(), 750);
      } else {
        rollback(el);
      }
    })
    .catch(() => rollback(el));
}

function rollback(el) {
  el.classList.remove("noti-read");
  alert("ไม่สามารถอัปเดตการแจ้งเตือนได้");
}

function mobileInit() {
  const observer = new MutationObserver(() => {
    const profileDropdown = document.getElementById("mb_profile");
    const mobileContainer = document.getElementById("mb_profile_dropdown");

    if (profileDropdown && mobileContainer) {
      // mobileContainer.innerHTML = '';
      // mobileContainer.appendChild(profileDropdown);

      // mobileContainer.classList.remove('hidden');
      profileDropdown.classList.remove("hidden");

      observer.disconnect(); // เจอแล้วหยุด
    }
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
}

function mobileMenu() {
  const mb_profile_authentication = document.getElementById(
    "mb_profile_authentication",
  );
  if (mb_profile_authentication) {
    mb_profile_authentication.addEventListener("click", (e) => {
      e.preventDefault();
      const target = document.getElementById("section-678-21");
      if (target.style.display === "block") {
        target.style.display = "none";
      } else {
        target.style.display = "block";
      }
    });
  }

  const mb_search = document.getElementById("mb_search");
  const mb_search_panel = document.getElementById("mb_search_panel");
  if (mb_search && mb_search_panel) {
    mb_search.addEventListener("click", (e) => {
      e.preventDefault();
      mb_search_panel.classList.remove("hidden");
    });

    const mb_search_close = document.getElementById("mb_search_close");
    if (mb_search_close) {
      mb_search_close.addEventListener("click", () => {
        mb_search_panel.classList.add("hidden");
      });
    }
  }

  const observer = new MutationObserver(() => {
    const mb_profile = document.getElementById("mb_profile");
    const mb_profile_dropdown = document.getElementById("mb_profile_dropdown");

    if (!mb_profile || !mb_profile_dropdown) return;

    mb_profile.addEventListener("click", function (e) {
      e.stopPropagation();
      mb_profile_dropdown.classList.toggle("hidden");
    });

    observer.disconnect(); // bind แล้ว หยุด observer
  });

  const mb_tab = document.querySelectorAll(".mb_tab");
  if (mb_tab) {
    mb_tab.forEach((tab) => {
      tab.addEventListener("click", function (e) {
        mb_tab.forEach((t) => t.classList.remove("current_active"));
        tab.classList.add("current_active");
      });
    });
  }

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });
}

document.addEventListener("click", function (e) {
  const bellIcon = e.target.closest("#notification-bell .bellIcon");
  const bellWrapper = document.getElementById("notification-bell");
  const notiDropdown = document.getElementById("noti-dropdown");
  if (!bellWrapper || !notiDropdown) return;

  if (bellIcon) {
    notiDropdown.classList.toggle("hidden");
    return;
  }

  if (!bellWrapper.contains(e.target)) {
    notiDropdown.classList.add("hidden");
  }
});

// TAB CLICK
document.addEventListener("click", function (e) {
  const tab = e.target.closest(".tab_title");
  if (!tab) return;

  const targetId = tab.dataset.tab;
  const tabs = document.querySelectorAll(".tab_title");
  const contents = document.querySelectorAll(".tab_content");

  tabs.forEach((t) => t.classList.remove("active_tab"));
  contents.forEach((c) => {
    c.classList.add("hidden");
    c.classList.remove("active_tab");
  });

  tab.classList.add("active_tab");

  const targetContent = document.getElementById(targetId);
  if (targetContent) {
    targetContent.classList.remove("hidden");
    targetContent.classList.add("active_tab");
  }
});

document.addEventListener("DOMContentLoaded", () => {
  if (clearCompareIfNoToken()) {
    return;
  }

  compareNurse();
  favoriteProvider();
  setTimeout(() => {
    const compareItems =
      JSON.parse(localStorage.getItem("compare_nurse_items")) || [];
    if (compareItems.length > 0) {
      renderCompareModal();
    }
  }, 1000);

  // การ์ดโหลดเพิ่มแบบ infinite scroll / ปุ่มโผล่ทีหลัง — sync ตอนโหลดแรก
  // แล้วคอยดัก DOM ที่เปลี่ยน (การ์ดใหม่, compare modal re-render) เพื่อ sync ซ้ำ
  syncActiveIconStates();
  new MutationObserver(() => syncActiveIconStates()).observe(document.body, {
    childList: true,
    subtree: true,
  });
});

document.addEventListener("DOMContentLoaded", () => {
  if (window.innerWidth < 768) {
    //mobileInit();
    mobileMenu();
  }
});
