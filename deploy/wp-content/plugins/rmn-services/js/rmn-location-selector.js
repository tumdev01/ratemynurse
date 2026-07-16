/**
 * RMN Location Selector
 * จัดการ dropdown จังหวัด อำเภอ ตำบล — ใช้ Tom Select (ไม่พึ่ง jQuery)
 */
class RMN_LocationSelector {
  constructor(options = {}) {
    this.provinceSelector = options.provinceSelector || "#province";
    this.districtSelector = options.districtSelector || "#district";
    this.subDistrictSelector = options.subDistrictSelector || "#sub_district";
    this.baseUrl = RMN_CONFIG.api.baseUrl;

    // ตัวแปรสำหรับเก็บชื่อ (ถ้าต้องการ)
    this.provinceName = "";
    this.districtName = "";
    this.subDistrictName = "";

    this.init();
  }

  /**
   * Initialize Location Selectors
   */
  init() {
    // Initialize province dropdown
    this.initProvince();

    // ถ้ามีค่าอยู่แล้ว ให้ initialize district และ sub_district ด้วย
    const provinceEl = document.querySelector(this.provinceSelector);
    const districtEl = document.querySelector(this.districtSelector);
    const currentProvinceId = provinceEl ? provinceEl.value : "";
    const currentDistrictId = districtEl ? districtEl.value : "";

    if (currentProvinceId) {
      this.initDistrict(currentProvinceId);

      if (currentDistrictId) {
        this.initSubDistrict(currentDistrictId);
      }
    }
  }

  /**
   * Initialize Province Dropdown
   * ใช้ window.RMN_PROVINCES (server-cached) ถ้ามี — เลี่ยง API call บน critical path
   */
  initProvince() {
    const onSelect = (value, item) => {
      this.provinceName = item.text;

      const hiddenInput = document.querySelector("#provinceTxt");
      if (hiddenInput) {
        hiddenInput.value = item.text;
      }

      this.clearDistrict();
      this.clearSubDistrict();
      this.initDistrict(value);
    };

    if (window.RMN_PROVINCES && Array.isArray(window.RMN_PROVINCES.data)) {
      this.initDropdownFromData(
        this.provinceSelector,
        window.RMN_PROVINCES.data,
        "กรุณาเลือกจังหวัด",
        onSelect,
      );
    } else {
      this.initDropdown(
        this.provinceSelector,
        `${this.baseUrl}/provinces_list`,
        "กรุณาเลือกจังหวัด",
        onSelect,
      );
    }
  }

  /**
   * Initialize Tom Select จาก static data array (ไม่เรียก API)
   */
  initDropdownFromData(selector, items, placeholder, onSelectCallback) {
    const element = document.querySelector(selector);

    if (!element) {
      console.warn(`Selector ${selector} not found`);
      return;
    }

    if (element.tomselect) {
      element.tomselect.destroy();
    }

    const currentValue = element.value;
    const currentOption = element.options[element.selectedIndex];
    const currentText = currentOption ? currentOption.text : "";

    const data = items.map((item) => ({ id: String(item.id), text: item.name }));

    const instance = new TomSelect(element, {
      placeholder,
      options: data,
      valueField: "id",
      labelField: "text",
      searchField: ["text"],
      plugins: ["clear_button"],
      render: {
        no_results: () => '<div class="no-results">ไม่พบข้อมูล</div>',
      },
      onChange: (value) => {
        // Tom Select ไม่ dispatch native 'change' event ที่ bubble ขึ้นไปหา form เหมือน select2 เดิม
        // (ที่ jQuery .trigger('change') เคยทำให้) — dispatch เองตรงนี้ กันโค้ดอื่นที่ดัก form-level
        // 'change' event (เช่น logic enable/disable ปุ่ม submit) ไม่ทำงาน
        element.dispatchEvent(new Event("change", { bubbles: true }));

        if (!value) return;
        const item = instance.options[value];
        if (item && onSelectCallback) {
          onSelectCallback(value, item);
        }
      },
    });

    if (currentValue && currentText && currentText.trim() !== "") {
      instance.addOption({ id: currentValue, text: currentText });
      instance.setValue(currentValue, true); // silent — ไม่ trigger onChange ตอน seed ค่าเดิม
    } else {
      // กัน Tom Select auto-select ตัวเลือกแรกใน options เป็นค่า default (ต้องเริ่มจากว่างเสมอ
      // จนกว่า user จะเลือกเอง)
      instance.clear(true);
    }
  }

  /**
   * Initialize District Dropdown
   */
  initDistrict(provinceId) {
    this.initDropdown(
      this.districtSelector,
      `${this.baseUrl}/districts_list/${provinceId}`,
      "เลือกอำเภอ/เขต",
      (value, item) => {
        this.districtName = item.text;

        // Update hidden input ถ้ามี
        const hiddenInput = document.querySelector("#districtTxt");
        if (hiddenInput) {
          hiddenInput.value = item.text;
        }

        // Clear sub_district
        this.clearSubDistrict();

        // Initialize sub_district ใหม่
        this.initSubDistrict(value);
      },
    );
  }

  /**
   * Initialize Sub District Dropdown
   */
  initSubDistrict(districtId) {
    this.initDropdown(
      this.subDistrictSelector,
      `${this.baseUrl}/sub_districts_list/${districtId}`,
      "เลือกตำบล/แขวง",
      (value, item) => {
        this.subDistrictName = item.text;

        // Update hidden input ถ้ามี
        const hiddenInput = document.querySelector("#subDistrictTxt");
        if (hiddenInput) {
          hiddenInput.value = item.text;
        }
      },
    );
  }

  /**
   * Initialize Dropdown with Tom Select (remote search ผ่าน axios)
   */
  initDropdown(selector, url, placeholder, onSelectCallback) {
    const element = document.querySelector(selector);

    if (!element) {
      console.warn(`Selector ${selector} not found`);
      return;
    }

    if (element.tomselect) {
      element.tomselect.destroy();
    }

    const currentValue = element.value;
    const currentOption = element.options[element.selectedIndex];
    const currentText = currentOption ? currentOption.text : "";

    const instance = new TomSelect(element, {
      placeholder,
      options: [],
      valueField: "id",
      labelField: "text",
      searchField: ["text"],
      plugins: ["clear_button"],
      loadThrottle: 250,
      shouldLoad: () => true, // ให้โหลดได้แม้ query ว่าง (เปิด dropdown แล้วเห็นรายการทันที เหมือนของเดิม)
      preload: "focus", // โหลดรายการทันทีตอน focus/เปิด dropdown ครั้งแรก โดยไม่ต้องรอพิมพ์ค้นหาก่อน
      load: (query, callback) => {
        axios
          .get(url, {
            params: {
              search: query,
              page: 1,
            },
          })
          .then((response) => {
            if (!response || !response.data || !response.data.data) {
              callback([]);
              return;
            }

            let results = response.data.data.map((item) => ({
              id: String(item.id),
              text: item.name,
            }));

            // Filter ฝั่ง client เพราะ API ไม่ได้ filter ให้ (ของเดิมเป็นแบบนี้อยู่แล้ว)
            if (query) {
              const term = query.toLowerCase();
              results = results.filter(
                (item) => item.text.toLowerCase().indexOf(term) !== -1,
              );
            }

            callback(results);
          })
          .catch((error) => {
            console.error("Tom Select load error:", error);
            callback();
          });
      },
      render: {
        no_results: () => '<div class="no-results">ไม่พบข้อมูล</div>',
        loading: () => '<div class="no-results">กำลังค้นหา...</div>',
      },
      onChange: (value) => {
        // Tom Select ไม่ dispatch native 'change' event ที่ bubble ขึ้นไปหา form เหมือน select2 เดิม
        // (ที่ jQuery .trigger('change') เคยทำให้) — dispatch เองตรงนี้ กันโค้ดอื่นที่ดัก form-level
        // 'change' event (เช่น logic enable/disable ปุ่ม submit) ไม่ทำงาน
        element.dispatchEvent(new Event("change", { bubbles: true }));

        if (!value) return;
        const item = instance.options[value];
        if (item && onSelectCallback) {
          onSelectCallback(value, item);
        }
      },
    });

    // ถ้ามีค่าเดิมอยู่ ให้เซ็ตกลับเข้าไป
    if (currentValue && currentText && currentText.trim() !== "") {
      instance.addOption({ id: currentValue, text: currentText });
      instance.setValue(currentValue, true);
    } else {
      // กัน Tom Select auto-select ตัวเลือกแรกเป็นค่า default
      instance.clear(true);
    }
  }

  /**
   * Clear District Dropdown
   */
  clearDistrict() {
    const element = document.querySelector(this.districtSelector);

    if (element) {
      if (element.tomselect) {
        element.tomselect.destroy();
      }
      element.innerHTML = "";
      element.value = "";
    }

    // Clear text input ถ้ามี
    const hiddenInput = document.querySelector("#districtTxt");
    if (hiddenInput) {
      hiddenInput.value = "";
    }

    this.districtName = "";
  }

  /**
   * Clear Sub District Dropdown
   */
  clearSubDistrict() {
    const element = document.querySelector(this.subDistrictSelector);

    if (element) {
      if (element.tomselect) {
        element.tomselect.destroy();
      }
      element.innerHTML = "";
      element.value = "";
    }

    // Clear text input ถ้ามี
    const hiddenInput = document.querySelector("#subDistrictTxt");
    if (hiddenInput) {
      hiddenInput.value = "";
    }

    this.subDistrictName = "";
  }

  /**
   * Reset All Dropdowns
   */
  resetAll() {
    this.clearDistrict();
    this.clearSubDistrict();

    const element = document.querySelector(this.provinceSelector);
    if (element && element.tomselect) {
      element.tomselect.clear();
    }

    const provinceInput = document.querySelector("#provinceTxt");
    if (provinceInput) {
      provinceInput.value = "";
    }

    this.provinceName = "";
  }

  /**
   * Get Selected Values
   */
  getSelectedValues() {
    const province = document.querySelector(this.provinceSelector);
    const district = document.querySelector(this.districtSelector);
    const subDistrict = document.querySelector(this.subDistrictSelector);

    return {
      province_id: province ? province.value : undefined,
      district_id: district ? district.value : undefined,
      sub_district_id: subDistrict ? subDistrict.value : undefined,
      province_name: this.provinceName,
      district_name: this.districtName,
      sub_district_name: this.subDistrictName,
    };
  }

  /**
   * Set Values (ใช้เมื่อต้องการ set ค่าจากภายนอก)
   */
  setValues(
    provinceId,
    districtId,
    subDistrictId,
    provinceText,
    districtText,
    subDistrictText,
  ) {
    // Set province
    if (provinceId && provinceText) {
      const provinceEl = document.querySelector(this.provinceSelector);
      if (provinceEl && provinceEl.tomselect) {
        provinceEl.tomselect.addOption({ id: provinceId, text: provinceText });
        provinceEl.tomselect.setValue(provinceId, true);
      }
      this.provinceName = provinceText;
    }

    // Set district
    if (districtId && districtText && provinceId) {
      setTimeout(() => {
        this.initDistrict(provinceId);
        setTimeout(() => {
          const districtEl = document.querySelector(this.districtSelector);
          if (districtEl && districtEl.tomselect) {
            districtEl.tomselect.addOption({ id: districtId, text: districtText });
            districtEl.tomselect.setValue(districtId, true);
          }
          this.districtName = districtText;
        }, 300);
      }, 300);
    }

    // Set sub_district
    if (subDistrictId && subDistrictText && districtId) {
      setTimeout(() => {
        this.initSubDistrict(districtId);
        setTimeout(() => {
          const subDistrictEl = document.querySelector(this.subDistrictSelector);
          if (subDistrictEl && subDistrictEl.tomselect) {
            subDistrictEl.tomselect.addOption({ id: subDistrictId, text: subDistrictText });
            subDistrictEl.tomselect.setValue(subDistrictId, true);
          }
          this.subDistrictName = subDistrictText;
        }, 600);
      }, 600);
    }
  }

  /**
   * Destroy (cleanup)
   */
  destroy() {
    [
      this.provinceSelector,
      this.districtSelector,
      this.subDistrictSelector,
    ].forEach((selector) => {
      const element = document.querySelector(selector);
      if (element && element.tomselect) {
        element.tomselect.destroy();
      }
    });
  }
}

// Export สำหรับใช้งาน
window.RMN_LocationSelector = RMN_LocationSelector;
