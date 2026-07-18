window.addEventListener('load', async () => {
  try {
    const container = document.getElementById('nursing-list');
    const limit = parseInt(container.dataset.limit) || 5;
    const certified = container.dataset.certified;
    const ids = container.dataset.ids
      ? container.dataset.ids.split(",").map(Number).filter(Boolean)
      : null;

    // ใช้ server-injected data ถ้ามี (ไม่ต้อง API call ซ้ำ)
    let nurses;
    if (window.RMN_NURSINGS && Array.isArray(window.RMN_NURSINGS)) {
      nurses = window.RMN_NURSINGS;
    } else if (ids && ids.length) {
      // การ์ด nursings-specific — endpoint แยกต่างหาก เบากว่า /api/nursings ปกติ
      const dataResponse = await axios.post(
        "https://services.ratemynurse.org/api/nursings/by-ids",
        { ids: ids },
        {
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-internal-Token": "9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789",
          },
        },
      );
      nurses = dataResponse.data;
    } else {
      const body = {
        limit: 5,
        certified: certified
      };
      const dataResponse = await axios.post(
        "https://services.ratemynurse.org/api/nursings",
        body,
        {
          headers: {
            Accept: "application/json",
            "X-internal-Token": "9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789",
          },
        },
      );
      nurses = dataResponse.data;
    }

    let html = '';

    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
      return null;
    }

    const hasAccessToken = getCookie('is_auth') === '1';

    let favoritedIds = new Set();
    if (hasAccessToken) {
      try {
        const formData = new URLSearchParams();
        formData.append('action', 'get_my_favorite_ids');
        formData.append('profile_type', 'NURSING');
        const favRes = await axios.post('/wp-admin/admin-ajax.php', formData);
        const ids = (favRes.data && favRes.data.data) || [];
        favoritedIds = new Set(ids.map(Number));
      } catch (e) {
        console.error('Failed to load favorited ids', e);
      }
    }

    nurses.forEach(nurse => {
      const provinceName = nurse.profile?.province?.name;
      const displayProvince = provinceName ? `, ${provinceName}` : '';

      const cost = nurse?.profile?.summary_cost?.lower_cost;

      const costDisplay =
        typeof cost === "number"
          ? cost.toLocaleString("th-TH", {
              maximumFractionDigits: 0,
            })
          : "";

      const displayCost =
        typeof cost === "number" && cost > 0
          ? `<span>เริ่มต้น</span> <span class="amount">฿${costDisplay}</span><span> / วัน</span>`
          : `<span>เริ่มต้น</span> <span class="amount">N/A</span><span> / วัน</span>`;

      const avg_score_raw = nurse?.average_score ?? 0;
      const review_count = nurse?.review_count ?? 0;
      const avg_percentage = (avg_score_raw / 5) * 100;

      const skillRaw = nurse?.profile?.skill ?? "";
      let skills = [];

      try {
        skills = skillRaw ? JSON.parse(skillRaw) : [];
      } catch (e) {
        skills = [];
      }

      let skillHTML = "";

      let certifiedIcon = "";
      if (nurse?.profile?.certified === 1) {
        certifiedIcon = `<img src="https://ratemynurse.org/wp-content/uploads/2025/12/certified_green-1.webp" width="24" height="24" loading="lazy">`;
      }

      if (Array.isArray(skills) && skills.length > 0) {
        const displaySkills = skills.slice(0, 2);
        const moreCount = skills.length > 2 ? skills.length - 2 : 0;

        skillHTML = `
          <div class="skill">
            <span>ทักษะความชำนาญ:</span>
            <div class="profile-expert">
              <div class="flex flex-row flex-wrap gap-[6px] items-center text-[14px] text-[#344054]">
                ${displaySkills
                  .map((skill) => `<span>${skill.value}</span>`)
                  .join(", ")}
                ${moreCount > 0 ? `<span>+${moreCount}</span>` : ""}
              </div>
            </div>
          </div>
        `;
      }

      html += `
        <div class="swiper-slide">
          <div class="nurse-item relative">
            ${
              hasAccessToken
                ? `
            <div class="action-icons absolute top-[12px] right-[12px] items-center justify-center gap-[12px]" style="display:none;">
              <span class="action-icon compare-nurse w-[36px] h-[36px] rounded-full cursor-pointer flex justify-center items-center bg-[#F3F3F4] opacity-[76%]" data-nurseid="${nurse.id}"><img class="object-contain" src="https://ratemynurse.org/wp-content/uploads/2025/12/compare.webp" width="17" height="15" loading="lazy"></span>
              <span data-profile-id="${nurse.profile?.id}" data-profile-type="nursing" class="action-icon favorite-nurse add-favorite${favoritedIds.has(Number(nurse.profile?.id)) ? " favorited" : ""} w-[36px] h-[36px] rounded-full cursor-pointer flex justify-center items-center bg-[#F3F3F4] opacity-[76%]" data-nurseid="${nurse.id}"><img class="object-contain" src="https://ratemynurse.org/wp-content/uploads/2025/12/favorite.webp" width="17" height="15" loading="lazy"></span>
            </div>
            `
                : ""
            }
            <div class="profile-img">
              <a href="https://ratemynurse.org/nursing-info/${nurse?.id}"><img style="width:100%;height:100%;object-fit:cover;object-position:top;" src="${nurse.cover_image.full_path}" width="306" height="240" loading="lazy"></a>
            </div>
            <div class="profile-info">
              <div class="profile-location">
                <svg fill="#5A5A5A" width="15px" height="15px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M12.6577283,22.7532553 L12,23.3275712 L11.3422717,22.7532553 C5.81130786,17.9237218 3,13.70676 3,10 C3,4.7506636 7.09705254,1 12,1 C16.9029475,1 21,4.7506636 21,10 C21,13.70676 18.1886921,17.9237218 12.6577283,22.7532553 Z M5,10 C5,12.8492324 7.30661202,16.4335466 12,20.6634039 C16.693388,16.4335466 19,12.8492324 19,10 C19,5.8966022 15.8358849,3 12,3 C8.16411512,3 5,5.8966022 5,10 Z M12,5 C14.7614237,5 17,7.23857625 17,10 C17,12.7614237 14.7614237,15 12,15 C9.23857625,15 7,12.7614237 7,10 C7,7.23857625 9.23857625,5 12,5 Z M12,7 C10.3431458,7 9,8.34314575 9,10 C9,11.6568542 10.3431458,13 12,13 C13.6568542,13 15,11.6568542 15,10 C15,8.34314575 13.6568542,7 12,7 Z"/>
                </svg>
                <span>${nurse.profile?.district?.name ?? "-"} ${displayProvince}</span>
              </div>
              <h3 class="profile-name"><a href="https://ratemynurse.org/nursing-info/${nurse.id}" class="flex flex-row gap-[8px] items-center">${nurse.profile?.name}${certifiedIcon}</a></h3>
              <div class="profile-rate text-[#8C8A94] text-left flex flex-row gap-[8px] items-center">    
                <div class="star-rating" style="--rating-percent: ${avg_percentage}%"></div>
                <span class="text-[12px] sm:text-[14px]">(${review_count})</span>
              </div>
              ${skillHTML}
            </div>
            <div class="cost">
              <div class="profile-cost">${displayCost}</div>
              <a href="https://ratemynurse.org/nursing-info/${nurse.id}" class="btn btn-secondary">ดูรายละเอียดเพิ่มเติม</a>
            </div>
          </div>
        </div>
      `;
    });

    container.innerHTML = `
      <div class="swiper-wrapper">${html}</div>
    `;

    // wait a bit for DOM to apply styles

    setTimeout(() => {
      new Swiper('#nursing-list', {
        slidesPerView: 1.4,
        spaceBetween: 15,
        loop: true,
        navigation: {
          nextEl: '.nursing-swiper-button-next',
          prevEl: '.nursing-swiper-button-prev'
        },
        breakpoints: {
          640: { slidesPerView: 2.5, spaceBetween: 32 },
          768: { slidesPerView: 2.5, spaceBetween: 32 },
          1024: { slidesPerView: 3.5, spaceBetween: 32 },
          1140: { slidesPerView: 4, spaceBetween: 32 }
        },
        on: {
          init: () => {
            document.querySelector('.nursing-swiper-button-prev').style.display = 'block';
            document.querySelector('.nursing-swiper-button-next').style.display = 'block';
          }
        }
      });
    }, 200); // 200ms delay just to be safe
    // compareNurse();
  } catch (error) {
    console.error('Error:', error);
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('admin_token');
    }
  }
});