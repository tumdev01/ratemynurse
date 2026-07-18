window.addEventListener("load", async () => {
  let page = 1;
  let isLoading = false;
  let hasMore = true;
  let token = localStorage.getItem("admin_token");

  const container = document.getElementById("nursing_grid_results");
  const limit = parseInt(container.dataset.perpage) || 8;
  const certified = container.dataset.certified || null;
  let order = container.dataset.order || "DESC";
  let orderby = container.dataset.orderby || "created_at";
  const province = container.dataset.province || null;
  const zone = container.dataset.zone || null;
  const inputSearch = document.getElementById("search_nursing");
  const submitSearch = document.getElementById("link_button-38-28");
  const sortbySelect = document.getElementById("sortby");

  /* ================= FAVORITED STATE ================= */
  function getAuthCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
  }
  const hasAccessTokenTop = getAuthCookie("is_auth") === "1";
  let favoritedIds = new Set();

  async function loadFavoritedIds() {
    if (!hasAccessTokenTop) return;
    try {
      const formData = new URLSearchParams();
      formData.append("action", "get_my_favorite_ids");
      formData.append("profile_type", "NURSING");
      const res = await axios.post("/wp-admin/admin-ajax.php", formData);
      const ids = (res.data && res.data.data) || [];
      favoritedIds = new Set(ids.map(Number));
    } catch (err) {
      console.error("Failed to load favorited ids", err);
    }
  }

  /* ================= SORT ================= */
  sortbySelect?.addEventListener("change", function () {
    const [, dir] = this.value.split(":");
    orderby = "created_at";
    order = dir;
    resetAndSearch();
  });

  /* ================= SEARCH ================= */
  async function resetAndSearch() {
    page = 1;
    hasMore = true;
    isFirstLoad = true;
    container.innerHTML = "";
    await loadNursings(page);
    document.getElementById("shortcode-37-28")?.scrollIntoView({ behavior: "smooth" });
  }

  // กด Enter ใน input
  inputSearch?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      resetAndSearch();
    }
  });

  // คลิก button
  submitSearch?.addEventListener("click", (e) => {
    e.preventDefault();
    resetAndSearch();
  });

  /* ================= TOKEN ================= */
  async function getToken() {
    if (!token) {
      try {
        const res = await axios.post(
          "https://services.ratemynurse.org/api/login",
          {
            email: "api@mail.com",
            password: "1",
          }
        );
        token = res.data.access_token;
        localStorage.setItem("admin_token", token);
      } catch (err) {
        console.error("Login failed", err);
        return false;
      }
    }
    return true;
  }

  /* ================= LOAD ================= */
  let isFirstLoad = true;

  async function loadNursings(pageNumber) {
    if (isLoading || !hasMore) return;
    isLoading = true;

    await getToken();

    try {
      const body = {
        limit,
        page: pageNumber,
        certified,
        order,
        orderby,
        province,
        zone,
        search: inputSearch?.value.trim() || null, // ← เพิ่มตรงนี้
      };

      const res = await axios.post(
        "https://services.ratemynurse.org/api/nursings-listing",
        body,
        {
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-internal-Token': '9f2d7a4c1b3e8f6d0a1b2c3d4e5f6789'
          },
        }
      );

      let total_amount = res.data.total;

      const amountHTML = document.getElementById('total_amount');
      amountHTML.textContent = total_amount ?? 0;

      const nurses = res.data.data || [];

      // ← เพิ่มการตรวจสอบว่ามีหน้าต่อไปไหม
      hasMore = res.data.has_more ?? (nurses.length === limit);

      if (isFirstLoad) {
        container.innerHTML = '';
        isFirstLoad = false;
      }

      if (nurses.length === 0) {
        if (pageNumber === 1) {
          container.innerHTML = '<p class="text-center">ไม่พบข้อมูล</p>';
        }
        hasMore = false;
        return;
      }

      const html = nurses.map(renderNurse).join("");
      container.insertAdjacentHTML("beforeend", html);

    } catch (err) {
      console.error('Load error:', err);
      if (err.response) {
        console.error('Response data:', err.response.data);
        console.error('Response status:', err.response.status);
      }
      hasMore = false; // ← หยุดโหลดเมื่อเกิด error
    } finally {
      isLoading = false;
    }
  }

  /* ================= RENDER ================= */
  function renderNurse(nurse) {
    const profile = nurse.profile || nurse; // รองรับทั้ง 2 โครงสร้าง
    const owner = nurse.owner;

    const provinceName = profile?.province?.name;
    const displayProvince = provinceName ? `, ${provinceName}` : "";

    const costRaw = profile?.summary_cost?.lower_cost;
    const cost = Number(costRaw);

    const displayCost =
      Number.isFinite(cost) && cost > 0
        ? `<span>เริ่มต้น</span> <span class="amount">฿${cost.toLocaleString("en-US")}</span><span> / วัน</span>`
        : `<span>เริ่มต้น</span> <span class="amount">N/A</span><span> / วัน</span>`;

    // ดึง cover image จาก owner
    const coverImagePath = nurse.cover_image?.full_path ||
      nurse?.cover_image?.path ||
      "https://via.placeholder.com/300x400?text=No+Image";

    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(";").shift();
      return null;
    }

    const hasAccessToken = getCookie("is_auth") === "1";
    const nurseId =  nurse.id;

    const escapeHTML = (str = "") =>
      str.replace(
        /[&<>"']/g,
        (m) =>
          ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
          })[m],
      );

    const truncateThai = (text = "", max = 70) => {
      const segmenter = new Intl.Segmenter("th", { granularity: "grapheme" });
      const chars = [...segmenter.segment(text)];
      if (chars.length <= max) return text;
      return (
        chars
          .slice(0, max)
          .map((s) => s.segment)
          .join("") + "..."
      );
    };

    const aboutRaw = profile?.about ?? "";
    const about = aboutRaw
      ? `<p class="profile-description">${escapeHTML(truncateThai(aboutRaw, 70))}</p>`
      : "";

    const about_list = aboutRaw
      ? `<p class="profile-description">${escapeHTML(truncateThai(aboutRaw, 200))}</p>`
      : "";

    const review_count = profile.review_count ?? 0;
    const average_score = Number(profile.average_score) || 0;

    const review_percentage =
      review_count > 0 ? Math.min(100, Math.round((average_score / 5) * 100)) : 0;

    // สำหรับแสดงผลเป็นข้อความ
    const review_percentage_text = `${review_percentage}%`;
    let skills = '';
    if ( profile?.skill) {
        let skillList = [];
        try { skillList = typeof profile.skill === 'string' ? JSON.parse(profile.skill) : profile.skill; } catch (e) { skillList = []; }
        if (Array.isArray(skillList) && skillList.length > 0) {
            const allSkills = skillList.map(s => s.value).join(', ');
            const moreCount = skillList.length - 1;
            const gridSkills = moreCount > 0
                ? `${skillList[0].value} <span class="more-skills">+${moreCount}</span>`
                : skillList[0].value;
            skills = `
                <div class="profile-skills skills-grid"><span>ความเชี่ยวชาญพิเศษ : </span><span>${gridSkills}</span></div>
                <div class="profile-skills skills-list"><span>ความเชี่ยวชาญพิเศษ : </span><span>${allSkills}</span></div>
            `;
        }
    }
    let exp_year = '';
    if ( profile?.exp_year ) {
      exp_year = `<span class="profile-exp">ประสบการณ์ทำงาน : ${profile.exp_year} ปี</span>`;
    }
    let certified = '';
    if ( profile?.certified === 1) {
        certified = `<img src="https://ratemynurse.org/wp-content/uploads/2025/12/certified_green-1.webp" width="24" height="24" loading="lazy">`;
    }

    return `
    <div class="nurse-item relative">
      ${
        hasAccessToken
          ? `
            <div class="action-icons absolute top-[12px] right-[12px] items-center justify-center gap-[12px]" style="display:none;">
              <span class="action-icon compare-nurse w-[36px] h-[36px] rounded-full cursor-pointer flex justify-center items-center bg-[#F3F3F4] opacity-[76%]" data-nurseid="${nurseId}"><img class="object-contain" src="https://ratemynurse.org/wp-content/uploads/2025/12/compare.webp" width="17" height="15" loading="lazy"></span>
              <span data-profile-id="${profile?.id}" data-profile-type="nursing" class="action-icon favorite-nurse add-favorite${favoritedIds.has(Number(profile?.id)) ? " favorited" : ""} w-[36px] h-[36px] rounded-full cursor-pointer flex justify-center items-center bg-[#F3F3F4] opacity-[76%]" data-nurseid="${nurseId}"><img class="object-contain" src="https://ratemynurse.org/wp-content/uploads/2025/12/favorite.webp" width="17" height="15" loading="lazy"></span>
            </div>
            `
          : ""
      }
      <div class="profile-img">
        <a href="https://ratemynurse.org/nursing-info/${nurseId}">
          <img src="${coverImagePath}"
               style="width:100%;height:100%;object-fit:cover"
               loading="lazy"
               onerror="this.src='https://via.placeholder.com/300x400?text=No+Image'">
        </a>
      </div>

      <div class="additional">
        <div class="profile-info">
          <div class="profile-location flex flex-row gap-[8px] items-center">
            <svg class="w-[16px] h-[16px] text-[#5A5A5A]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.8 13.938h-.011a7 7 0 1 0-11.464.144h-.016l.14.171c.1.127.2.251.3.371L12 21l5.13-6.248c.194-.209.374-.429.54-.659l.13-.155Z"/>
            </svg>
            <span>${profile?.district?.name ?? "-"}${displayProvince}</span>
          </div>

          <h3 class="profile-name">
            <a href="https://ratemynurse.org/nursing-info/${nurseId}" class="flex flex-row gap-[8px] items-center">
              ${profile?.name ?? "ไม่ระบุชื่อ"}
              ${certified}
            </a>
          </h3>

          <div class="profile-rate text-[#8C8A94] text-left flex flex-row gap-[8px] items-center">
            <div class="star-rating" style="--rating-percent: ${review_percentage_text}"></div>
            <span class="text-[12px] sm:text-[14px]">(${review_count})</span>
          </div>

          <div class="about-grid">${about}</div>
          <div class="about-list">${about_list}</div>

          ${skills}

          ${exp_year}
        </div>

        <div class="cost">
          <div class="profile-cost">${displayCost}</div>
          <a href="https://ratemynurse.org/nursing-info/${nurseId}"
             class="btn btn-secondary">
            ดูรายละเอียดเพิ่มเติม
          </a>
        </div>
      </div>
    </div>
  `;
  }

  /* ================= FIRST LOAD ================= */
  await loadFavoritedIds();
  loadNursings(page);

  /* ================= SCROLL ================= */
  window.addEventListener("scroll", () => {
    if (
      window.innerHeight + window.scrollY >=
      document.body.offsetHeight - 300 &&
      !isLoading &&
      hasMore
    ) {
      page++;
      loadNursings(page);
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const view_sw = document.querySelectorAll(".view_sw");
  const nursing_grid_results = document.getElementById("nursing_grid_results");

  view_sw.forEach((el) => {
    el.addEventListener("click", function (e) {
      e.preventDefault();
      view_sw.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      const current_view = this.dataset.view;
      nursing_grid_results.classList.remove("grid", "list");
      nursing_grid_results.classList.add(current_view);
    });
  });
});
