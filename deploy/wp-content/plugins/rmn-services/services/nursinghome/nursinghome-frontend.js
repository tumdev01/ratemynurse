document.addEventListener("DOMContentLoaded", async () => {
  // Always attach compare handler — works on info page (single profile) ด้วย ไม่ต้องมี #nursinghome-list
  compareNursingHome();
  setTimeout(() => {
    const compareItems = JSON.parse(localStorage.getItem("compare_home_item")) || [];
    if (compareItems.length > 0) {
      renderHomeCompareModal();
    }
  }, 1000);

  // Carousel logic — skip ถ้าไม่มี container
  const container = document.getElementById("nursinghome-list");
  if (!container) return;

  try {
    const limit = parseInt(container.dataset.limit) || 5;
    const certified = container.dataset.certified;
    const ids = container.dataset.ids
      ? container.dataset.ids.split(",").map(Number).filter(Boolean)
      : null;

    // ใช้ server-injected data ถ้ามี (ไม่ต้อง API call ซ้ำ)
    let nursingHomes;
    if (window.RMN_NURSING_HOMES && Array.isArray(window.RMN_NURSING_HOMES)) {
      nursingHomes = window.RMN_NURSING_HOMES;
    } else if (ids && ids.length) {
      // การ์ด nursing-homes-specific — endpoint แยกต่างหาก เบากว่า /api/nursing-homes ปกติ
      const dataResponse = await axios.post(
        "https://services.ratemynurse.org/api/nursing-homes/by-ids",
        { ids: ids },
        {
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-internal-Token": "9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789",
          },
        },
      );
      nursingHomes = dataResponse.data;
    } else {
      const body = {
        limit: limit,
        certified: certified,
      };
      const dataResponse = await axios.post(
        "https://services.ratemynurse.org/api/nursing-homes",
        body,
        {
          headers: {
            // Authorization: `Bearer ${token}`,
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-internal-Token": "9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789",
          },
        },
      );
      nursingHomes = dataResponse.data;
    }
    let html = "";

    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(";").shift();
      return null;
    }
    const hasAccessToken = getCookie("is_auth") === "1";

    let favoritedIds = new Set();
    if (hasAccessToken) {
      try {
        const formData = new URLSearchParams();
        formData.append("action", "get_my_favorite_ids");
        formData.append("profile_type", "NURSING_HOME");
        const favRes = await axios.post("/wp-admin/admin-ajax.php", formData);
        const ids = (favRes.data && favRes.data.data) || [];
        favoritedIds = new Set(ids.map(Number));
      } catch (e) {
        console.error("Failed to load favorited ids", e);
      }
    }

    nursingHomes.forEach((nurse) => {
      const addressName = nurse.address;

      const subDistrictName = nurse.sub_district?.name;

      const districtName = nurse.district?.name;
      const displayDistrict = districtName ? `, ${districtName}` : "";

      const provinceName = nurse.province?.name;
      const displayProvince = provinceName ? `, ${provinceName}` : "";

      const cost = nurse.cost_per_month;
      const displayCost =
        cost && cost > 0
          ? `เริ่มต้น <span class="amount">฿ ${new Intl.NumberFormat("th-TH").format(cost)}</span> / เดือน`
          : `<span class="amount">N/A</span> / เดือน`;

      const maxLength = 100;
      const description = nurse.description;
      const shortDescription =
        description.length > maxLength
          ? description.substring(0, maxLength) + "..."
          : description;

      const avg_score_raw = nurse?.average_score ?? 0;
      const review_count = nurse?.review_count ?? 0;
      const avg_percentage = (avg_score_raw / 5) * 100;

      let certifiedIcon = "";
      if (nurse?.certified === 1) {
        certifiedIcon = `<img src="https://ratemynurse.org/wp-content/uploads/2025/12/certified_green-1.webp" width="24" height="24" loading="lazy">`;
      }

      html += `
        <div class="swiper-slide">
          <div class="nurse-item">
              ${
                hasAccessToken
                  ? `
              <div class="action-icons absolute top-[12px] right-[12px] items-center justify-center gap-[12px]" style="display:none;">
                <span class="action-icon compare-nursing_home w-[36px] h-[36px] rounded-full cursor-pointer flex justify-center items-center bg-[#F3F3F4] opacity-[76%]" data-homeid="${nurse.id}"><img class="object-contain" src="https://ratemynurse.org/wp-content/uploads/2025/12/compare.webp" width="17" height="15" loading="lazy"></span>
                <span data-profile-id="${nurse.id}" data-profile-type="nursing_home" class="action-icon favorite-nursing_home add-favorite${favoritedIds.has(Number(nurse.id)) ? " favorited" : ""} w-[36px] h-[36px] rounded-full cursor-pointer flex justify-center items-center bg-[#F3F3F4] opacity-[76%]" data-homeid="${nurse.id}"><img class="object-contain" src="https://ratemynurse.org/wp-content/uploads/2025/12/favorite.webp" width="17" height="15" loading="lazy"></span>
              </div>
              `
                  : ""
              }
              <div class="profile-img">
                <a href="https://ratemynurse.org/nursing-home-info/${nurse.id}">
                  <img 
                    style="width:100%;height:100%;object-fit:cover;object-position:top;" 
                    src="${
                      nurse?.cover_image?.full_path &&
                      nurse.cover_image.full_path.trim() !== ""
                        ? nurse.cover_image.full_path
                        : "https://i0.wp.com/ratemynurse.org/wp-content/uploads/2025/07/main-logo.png?fit=107%2C86&ssl=1"
                    }" 
                    width="306" 
                    height="240" 
                    loading="lazy"
                  >
                </a>
              </div>
              <div class="profile-info">
                <div class="profile-location">
                  <svg fill="#5A5A5A" width="15px" height="15px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M12.6577283,22.7532553 L12,23.3275712 L11.3422717,22.7532553 C5.81130786,17.9237218 3,13.70676 3,10 C3,4.7506636 7.09705254,1 12,1 C16.9029475,1 21,4.7506636 21,10 C21,13.70676 18.1886921,17.9237218 12.6577283,22.7532553 Z M5,10 C5,12.8492324 7.30661202,16.4335466 12,20.6634039 C16.693388,16.4335466 19,12.8492324 19,10 C19,5.8966022 15.8358849,3 12,3 C8.16411512,3 5,5.8966022 5,10 Z M12,5 C14.7614237,5 17,7.23857625 17,10 C17,12.7614237 14.7614237,15 12,15 C9.23857625,15 7,12.7614237 7,10 C7,7.23857625 9.23857625,5 12,5 Z M12,7 C10.3431458,7 9,8.34314575 9,10 C9,11.6568542 10.3431458,13 12,13 C13.6568542,13 15,11.6568542 15,10 C15,8.34314575 13.6568542,7 12,7 Z"/>
                  </svg>
                  <span>${subDistrictName}${displayDistrict}${displayProvince}</span>
                </div>
                <h3 class="profile-name"><a href="https://ratemynurse.org/nursing-home-info/${nurse.id}" class="flex flex-row gap-[8px] items-center">${nurse?.name}${certifiedIcon}</a></h3>
                <p>${shortDescription}</p>
                <div class="profile-rate text-[#8C8A94] text-left flex flex-row gap-[8px] items-center">
                  <div class="star-rating" style="--rating-percent: ${avg_percentage}%"></div>
                  <span class="text-[12px] sm:text-[14px]">(${review_count})</span>
                </div>
                <span class="line"></span>
              </div>
              <div class="profile-cost">${displayCost}</div>
          </div>
        </div>
      `;
    });

    container.innerHTML = `
      <div class="swiper-wrapper">${html}</div>
    `;

    // wait a bit for DOM to apply styles

    setTimeout(() => {
      new Swiper("#nursinghome-list", {
        slidesPerView: 1.4,
        spaceBetween: 15,
        loop: true,
        navigation: {
          nextEl: ".nursinghome-swiper-button-next",
          prevEl: ".nursinghome-swiper-button-prev",
        },
        breakpoints: {
          640: { slidesPerView: 2.5, spaceBetween: 32 },
          768: { slidesPerView: 2.5, spaceBetween: 32 },
          1024: { slidesPerView: 3.5, spaceBetween: 32 },
          1140: { slidesPerView: 3.5, spaceBetween: 32 },
        },
        on: {
          init: () => {
            document.querySelector(
              ".nursinghome-swiper-button-prev",
            ).style.display = "block";
            document.querySelector(
              ".nursinghome-swiper-button-next",
            ).style.display = "block";
          },
        },
      });
    }, 200); // 200ms delay just to be safe
  } catch (error) {
    console.error("Error:", error);
    if (error.response && error.response.status === 401) {
      localStorage.removeItem("admin_token");
    }
  }

  function compareNursingHome() {
    document.addEventListener("click", function (e) {
      const compareBtn = e.target.closest(".compare-nursing_home");

      if (compareBtn) {
        e.preventDefault();
        e.stopPropagation();

        const homeid = compareBtn.getAttribute("data-homeid");
        let compare_home_item =
          JSON.parse(localStorage.getItem("compare_home_item")) || [];

        const index = compare_home_item.indexOf(homeid);
        if (index !== -1) {
          compare_home_item.splice(index, 1);
        }

        compare_home_item.push(homeid);

        if (compare_home_item.length > 3) {
          compare_home_item = compare_home_item.slice(
            compare_home_item.length - 3,
          );
        }

        localStorage.setItem(
          "compare_home_item",
          JSON.stringify(compare_home_item),
        );

        const modal = document.getElementById("compare-home-modal");
        const isExpanded = modal && modal.classList.contains("expanded");

        renderHomeCompareModal();

        if (isExpanded) {
          setTimeout(() => {
            const newModal = document.getElementById("compare-home-modal");
            if (newModal) {
              newModal.classList.remove("collapsed");
              newModal.classList.add("expanded");
            }
          }, 0);
        }
      }
    });
  }

  function renderHomeCompareModal() {
    let compare_home_modal = document.getElementById("compare-home-modal");
    let compare_modal = document.getElementById("compare-modal");
    // เก็บ state เดิมไว้ก่อน
    let wasExpanded = false;
    if (compare_home_modal) {
      wasExpanded = compare_home_modal.classList.contains("expanded");
      compare_home_modal.remove();
    }

    // สร้างใหม่
    compare_home_modal = document.createElement("div");
    compare_home_modal.id = "compare-home-modal";
    compare_home_modal.classList.add(
      "compare-modal",
      "fixed",
      "bottom-0",
      "w-full",
      "max-w-[430px]",
      "shadow-lg",
      "rounded-t-lg",
      "overflow-hidden",
      "flex",
      "flex-col",
    );

    // เช็คว่ามี compare_modal ไหม
    compare_home_modal.classList.add(
      compare_modal ? "right-[460px]" : "right-0",
    );

    // ใส่ class ตาม state เดิม
    if (wasExpanded) {
      compare_home_modal.classList.add("expanded");
    } else {
      compare_home_modal.classList.add("collapsed");
    }

    compare_home_modal.style.cssText =
      "position: fixed !important; display: flex !important;";

    const compareItems =
      JSON.parse(localStorage.getItem("compare_home_item")) || [];

    // สร้าง modal structure ก่อน (แสดง loading)
    compare_home_modal.innerHTML = `
        <div class="compare-modal-header flex justify-between items-center p-4 bg-[#286F51] cursor-pointer">
            <h2 class="text-[16px] flex flex-row gap-[10px] text-white">
                <img src="https://ratemynurse.org/wp-content/uploads/2025/12/compare-white.webp" width="20" height="18" loading="lazy">
                <span>เปรียบเทียบศูนย์</span>
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
                <button class="w-full compare-modal-submit cursor-pointer text-white !text-[16px] px-[14px] py-[8px] rounded-[10px] block text-center bg-[#286F51] hover:bg-[#1f5a3f]">เปรียบเทียบ</button>
            </div>
        </div>
    `;

    const goToCompareBtn = compare_home_modal.querySelector(
      ".compare-modal-submit",
    );
    goToCompareBtn?.addEventListener("click", () => {
      const compareItems =
        JSON.parse(localStorage.getItem("compare_home_item")) || [];
      const idsParam = compareItems.join(",");
      window.location.href = `https://ratemynurse.org/comparison?h=${idsParam}`;
    });

    document.body.appendChild(compare_home_modal);

    const formData = new URLSearchParams();
    formData.append("action", "compare_nursinghome");
    formData.append("items", JSON.stringify(compareItems));

    axios
      .post("/wp-admin/admin-ajax.php", formData)
      .then((response) => {
        // ดึงข้อมูลจาก response (รองรับหลายชั้น)
        const nurses =
          response.data?.data?.data?.data ||
          response.data?.data?.data ||
          response.data?.data;
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
                                            ? `<img src="${nurse.coverImage}" alt="${nurse.name}" class="w-full h-full object-cover">`
                                            : `<div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>`
                                        }
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="text-[16px] font-semibold profile-name">
                                            ${nurse.name}
                                        </div>
                                        <div class="profile-rate flex items-center gap-1 text-sm">
                                            <span class="text-yellow-500">★</span>
                                            <span>${avgRating > 0 ? avgRating : "ยังไม่มีรีวิว"}</span>
                                        </div>
                                        <div class="text-[14px] profile-cost">
                                            ${nurse.cost_per_month ? `฿${nurse.cost_per_month}` : "ไม่ระบุราคา"}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="remove-compare-home cursor-pointer">
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
        const modalList = compare_home_modal.querySelector(
          ".compare-modal-list",
        );
        if (modalList) {
          modalList.innerHTML = modalContent;
        }

        // เพิ่ม event listeners สำหรับปุ่มลบ
        setupRemoveCompareHomeButtons(compare_home_modal);
      })
      .catch((err) => {
        console.error(err);
        const modalList = compare_home_modal.querySelector(
          ".compare-modal-list",
        );
        if (modalList) {
          modalList.innerHTML =
            '<div class="text-center py-4 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        }
      });

    const nursingHomeModalheader = compare_home_modal.querySelector(
      ".compare-modal-header",
    );
    nursingHomeModalheader.addEventListener("click", (e) => {
      if (e.target.closest(".close-compare-modal")) return;

      compare_home_modal.classList.toggle("collapsed");
      compare_home_modal.classList.toggle("expanded");
    });

    // Event: ปุ่ม X - แค่พับลง
    compare_home_modal
      .querySelector(".close-compare-modal")
      ?.addEventListener("click", (e) => {
        e.stopPropagation();
        compare_home_modal.classList.remove("expanded");
        compare_home_modal.classList.add("collapsed");
      });
  }

  function setupRemoveCompareHomeButtons(compare_modal) {
    compare_modal.querySelectorAll(".remove-compare-home").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.stopPropagation();
        const nurseId = btn
          .closest("[data-nurseid]")
          .getAttribute("data-nurseid");
        let compareItems =
          JSON.parse(localStorage.getItem("compare_home_item")) || [];
        compareItems = compareItems.filter((id) => id !== nurseId);
        localStorage.setItem("compare_home_item", JSON.stringify(compareItems));

        // ถ้าไม่มี item เหลือแล้ว ซ่อนทั้งหมด
        if (compareItems.length === 0) {
          compare_modal.style.display = "none";
        } else {
          // เก็บ state ก่อน render ใหม่
          const isExpanded = compare_modal.classList.contains("expanded");
          renderHomeCompareModal();

          if (isExpanded) {
            setTimeout(() => {
              const newModal = document.getElementById("compare-home-modal");
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
});
