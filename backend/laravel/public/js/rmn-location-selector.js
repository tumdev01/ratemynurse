/**
 * RMN Location Selector (Laravel admin)
 * จัดการ dropdown จังหวัด อำเภอ ตำบล ด้วย Tom Select — พอร์ตมาจาก wp-content/plugins/rmn-services/js/rmn-location-selector.js
 * ต่างกันแค่ baseUrl: ฝั่งนี้เรียก API ของ Laravel เอง (same-origin) ไม่ต้องพึ่ง RMN_CONFIG ของ WP
 */
class RMN_LocationSelector {
  constructor(options = {}) {
    this.provinceSelector = options.provinceSelector || "#province";
    this.districtSelector = options.districtSelector || "#district";
    this.subDistrictSelector = options.subDistrictSelector || "#sub_district";
    this.baseUrl = options.baseUrl || "/api";

    this.provinceName = "";
    this.districtName = "";
    this.subDistrictName = "";

    this.init();
  }

  init() {
    this.initProvince();

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

  initProvince() {
    this.initDropdown(
      this.provinceSelector,
      `${this.baseUrl}/provinces_list`,
      "กรุณาเลือกจังหวัด",
      (value, item) => {
        this.provinceName = item.text;

        const hiddenInput = document.querySelector("#provinceTxt");
        if (hiddenInput) {
          hiddenInput.value = item.text;
        }

        this.clearDistrict();
        this.clearSubDistrict();
        this.initDistrict(value);
      },
    );
  }

  initDistrict(provinceId) {
    this.initDropdown(
      this.districtSelector,
      `${this.baseUrl}/districts_list/${provinceId}`,
      "เลือกอำเภอ/เขต",
      (value, item) => {
        this.districtName = item.text;

        const hiddenInput = document.querySelector("#districtTxt");
        if (hiddenInput) {
          hiddenInput.value = item.text;
        }

        this.clearSubDistrict();
        this.initSubDistrict(value);
      },
    );
  }

  initSubDistrict(districtId) {
    this.initDropdown(
      this.subDistrictSelector,
      `${this.baseUrl}/sub_districts_list/${districtId}`,
      "เลือกตำบล/แขวง",
      (value, item) => {
        this.subDistrictName = item.text;

        const hiddenInput = document.querySelector("#subDistrictTxt");
        if (hiddenInput) {
          hiddenInput.value = item.text;
        }
      },
    );
  }

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
      shouldLoad: () => true,
      preload: "focus",
      load: (query, callback) => {
        axios
          .get(url, { params: { search: query, page: 1 } })
          .then((response) => {
            if (!response || !response.data || !response.data.data) {
              callback([]);
              return;
            }

            let results = response.data.data.map((item) => ({
              id: String(item.id),
              text: item.name,
            }));

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
      instance.setValue(currentValue, true);
    } else {
      instance.clear(true);
    }
  }

  clearDistrict() {
    const element = document.querySelector(this.districtSelector);

    if (element) {
      if (element.tomselect) {
        element.tomselect.destroy();
      }
      element.innerHTML = "";
      element.value = "";
    }

    const hiddenInput = document.querySelector("#districtTxt");
    if (hiddenInput) {
      hiddenInput.value = "";
    }

    this.districtName = "";
  }

  clearSubDistrict() {
    const element = document.querySelector(this.subDistrictSelector);

    if (element) {
      if (element.tomselect) {
        element.tomselect.destroy();
      }
      element.innerHTML = "";
      element.value = "";
    }

    const hiddenInput = document.querySelector("#subDistrictTxt");
    if (hiddenInput) {
      hiddenInput.value = "";
    }

    this.subDistrictName = "";
  }
}

window.RMN_LocationSelector = RMN_LocationSelector;
