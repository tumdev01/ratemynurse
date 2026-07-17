<?php

class Search {
    public function __construct() {
        add_shortcode('search', [$this, 'searchShortcode']);
    }

    private function enqueue_assets() {
        wp_enqueue_script('axios');
        // rmn-search.js (ไฟล์แยก) เป็นโค้ดเก่าที่ตกค้าง อ้างอิง #selectedInfo/#section-269-9 ที่ไม่มีอยู่
        // ใน markup ปัจจุบันเลย และมี class ProvinceSelector ของตัวเองที่ผูก event ซ้ำกับ inline
        // script ด้านล่าง (formRender()) บน element ตัวเดียวกัน — เลิกโหลดไฟล์นี้เพื่อกันชนกัน
    }

    public function searchShortcode($atts) {
        $this->enqueue_assets();
        $form = $this->formRender();

        return <<<HTML
            {$form}
        HTML;
    }

    private function formRender() {
        return <<<HTML
            <style>
            .province-selector {
                max-width: 100%;
                position: relative;
            }

            .search-box {
                position: relative;
                background: white;
                border-radius: 8px;
            }

            .search-input {
                width: 100%;
                padding: 12px 40px 12px 16px;
                font-size: 16px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                outline: none;
                transition: border-color 0.2s;
                cursor: pointer;
            }

            .search-input:focus {
                border-color: #2196F3;
            }

            .search-input.selected {
                color: #1B5E20;
                font-weight: 500;
                background-color: #E8F5E9;
                border-color: #4CAF50;
            }

            .search-input.has-results {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }

            .search-icon {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #999;
                pointer-events: none;
            }

            .results-container {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                max-height: 400px;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                background: white;
                border: 2px solid #2196F3;
                border-top: none;
                border-bottom-left-radius: 8px;
                border-bottom-right-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 1000;
            }

            .results-container.show {
                display: block;
            }

            .province-item {
                padding: 16px;
                border-bottom: 1px solid #f0f0f0;
                cursor: pointer;
                transition: background 0.2s;
            }

            .province-item:last-child {
                border-bottom: none;
            }

            .province-item:hover,
            .province-item:active {
                background: #f5f5f5;
            }

            .province-name {
                font-size: 16px;
                font-weight: 500;
                color: #333;
                margin-bottom: 4px;
            }

            .province-meta {
                font-size: 13px;
                color: #666;
            }

            .zone-badge {
                display: inline-block;
                padding: 2px 8px;
                background: #E3F2FD;
                color: #1976D2;
                border-radius: 4px;
                font-size: 12px;
                margin-right: 8px;
            }

            .loading {
                text-align: center;
                padding: 20px;
                color: #999;
            }

            .no-results {
                text-align: center;
                padding: 40px 20px;
                color: #999;
            }

            .selected-info {
                margin-top: 20px;
                padding: 16px;
                background: #E8F5E9;
                border-radius: 8px;
                border-left: 4px solid #4CAF50;
                display: none;
            }

            .selected-info.show {
                display: block;
            }

            .selected-label {
                font-size: 12px;
                color: #2E7D32;
                margin-bottom: 4px;
            }

            .selected-value {
                font-size: 16px;
                font-weight: 600;
                color: #1B5E20;
            }

            .clear-selection {
                position: absolute;
                right: 45px;
                top: 50%;
                transform: translateY(-50%);
                cursor: pointer;
                color: #999;
                font-size: 20px;
                display: none;
                padding: 0 5px;
            }

            .clear-selection.show {
                display: block;
            }

            .clear-selection:hover {
                color: #666;
            }

            .dropdown-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 999;
            }

            .dropdown-backdrop.show {
                display: block;
            }

            .alert-message {
                padding: 12px 16px;
                background: #FFF3CD;
                border: 1px solid #FFE69C;
                border-radius: 8px;
                color: #856404;
                font-size: 14px;
                margin-bottom: 16px;
                display: none;
            }

            .alert-message.show {
                display: block;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Mobile optimizations */
            @media (max-width: 768px) {
                .results-container {
                    max-height: 60vh;
                }
            }
            </style>

            <div class="rms-search-instance">
            <div class="dropdown-backdrop" id="backdrop"></div>

            <div id="rms_search" class="p-[24px] flex flex-col gap-[24px] justify-center items-start h-full">
                <span class="text-[20px] text-[#286f51] font-semibold">เลือกรูปแบบบริการ</span>
                <div id="search_step1" class="flex flex-row gap-[24px] justify-start cursor-pointer ct-section">
                    <div class="w-[200px] h-[200px] border-[1px] border-[#E5E7EB] rounded-[8px] p-[16px] flex flex-col gap-[16px] justify-center items-center text-black hover:text-white hover:font-medium hover:bg-[#286f51]" data-type="NURSING_HOME">
                        <img src="https://ratemynurse.org/wp-content/uploads/2025/08/nursinghome.webp" width="50" height="50" loading="lazy">
                        <span>ศูนย์ดูแล</span>
                    </div>
                    <div class="w-[200px] h-[200px] border-[1px] border-[#E5E7EB] rounded-[8px] p-[16px] flex flex-col gap-[16px] justify-center items-center text-black hover:text-white hover:font-medium hover:bg-[#286f51]" data-type="NURSING">
                        <img src="https://ratemynurse.org/wp-content/uploads/2025/08/nurse.webp" width="50" height="50" loading="lazy">
                        <span>พยาบาล / ผู้ดูแล</span>
                    </div>
                </div>

                <span class="text-[20px] text-[#286f51] font-semibold">เลือกจังหวัด</span>
                <div class="alert-message" id="alertMessage">⚠️ กรุณาเลือกรูปแบบบริการก่อน</div>
                <div id="search_step2" class="w-full ct-section">
                    <div class="province-selector">
                        <div class="search-box">
                            <input 
                                type="text" 
                                id="provinceSearch" 
                                class="search-input" 
                                placeholder="พิมพ์ชื่อจังหวัดเพื่อค้นหา..."
                                autocomplete="off"
                                readonly
                            >
                            <span class="clear-selection" id="clearSelection">✕</span>
                            <span class="search-icon">🔍</span>
                        </div>
                        
                        <div class="results-container" id="resultsContainer">
                            <div class="loading">กำลังโหลดข้อมูล...</div>
                        </div>
                    </div>

                    <!-- Hidden inputs for form submission -->
                    <input type="hidden" id="province_id">
                    <input type="hidden" id="province_code">
                    <input type="hidden" id="province_zone">
                    <input type="hidden" id="service_type">
                </div>

                <button type="button" id="searchSubmitBtn" class="btn-primary text-white py-[14px] h-[auto]">ค้นหา</button>
            </div>

            <script>
            (function () {
                // สคริปต์นี้ต้องรันแบบ synchronous ทันที (ห้ามครอบด้วย DOMContentLoaded) เพราะพึ่ง
                // document.currentScript ในการหา container ของตัวเอง — currentScript จะเป็น null
                // ถ้าไปอ่านค่าในนั้นตอนอยู่ใน callback ที่ทำงานทีหลัง (เช่น DOMContentLoaded)
                // จำเป็นต้อง scope การค้นหา element ทั้งหมดไว้ใน container ของตัวเอง (ไม่ใช้
                // document.getElementById ตรงๆ) เพราะ shortcode [search] นี้อาจถูกฝังมากกว่า 1 จุด
                // ในหน้าเดียวกัน (เช่น ฝังตรงในหน้า + ฝังซ้ำใน mobile bottom nav search panel) —
                // document.getElementById จะเจอแค่ตัวแรกในหน้าเสมอ ทำให้ instance อื่นไม่มี event ผูกเลย
                const container = document.currentScript.closest('.rms-search-instance');
                const searchInput = container.querySelector('#provinceSearch');
                const resultsContainer = container.querySelector('#resultsContainer');
                const backdrop = container.querySelector('#backdrop');
                const clearBtn = container.querySelector('#clearSelection');
                const alertMessage = container.querySelector('#alertMessage');
                // Province Selector Class
                class ProvinceSelector {
                    constructor() {
                        this.provinces = [];

                        this.init();
                    }

                    async init() {
                        await this.loadProvinces();
                        this.setupEventListeners();
                    }

                    async loadProvinces() {
                        if (window.RMN_PROVINCES && Array.isArray(window.RMN_PROVINCES.data)) {
                            this.provinces = window.RMN_PROVINCES.data;
                            return;
                        }
                        try {
                            const response = await axios.get('https://services.ratemynurse.org/api/provinces_list');
                            this.provinces = response.data.data;
                        } catch (error) {
                            resultsContainer.innerHTML = '<div class="no-results">ไม่สามารถโหลดข้อมูลได้</div>';
                            console.error('Error loading provinces:', error);
                        }
                    }

                    setupEventListeners() {
                        // Click to open dropdown
                        searchInput.addEventListener('click', () => {
                            // Check if service type is selected
                            const serviceType = container.querySelector('#service_type').value;
                            if (!serviceType) {
                                this.showAlert();
                                return;
                            }

                            this.hideAlert();
                            searchInput.removeAttribute('readonly');
                            searchInput.focus();
                            this.renderResults(this.provinces);
                            this.showDropdown();
                        });

                        // Input event
                        searchInput.addEventListener('input', (e) => {
                            const query = e.target.value.toLowerCase().trim();
                            const filtered = this.filterProvinces(query);
                            this.renderResults(filtered);
                            this.showDropdown();
                        });

                        // Backdrop click to close
                        backdrop.addEventListener('click', () => {
                            this.hideDropdown();
                        });

                        // Clear selection
                        clearBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            this.clearSelection();
                        });

                        // Event delegation for province items (mousedown fires before input blur/re-render)
                        resultsContainer.addEventListener('mousedown', (e) => {
                            const item = e.target.closest('.province-item');
                            if (item) {
                                e.preventDefault();
                                this.selectProvince(item);
                            }
                        });

                        // Close on Escape key
                        searchInput.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') {
                                this.hideDropdown();
                            }
                        });
                    }

                    showAlert() {
                        alertMessage.classList.add('show');
                        setTimeout(() => {
                            alertMessage.classList.remove('show');
                        }, 3000);
                    }

                    hideAlert() {
                        alertMessage.classList.remove('show');
                    }

                    showDropdown() {
                        resultsContainer.classList.add('show');
                        backdrop.classList.add('show');
                        searchInput.classList.add('has-results');
                    }

                    hideDropdown() {
                        resultsContainer.classList.remove('show');
                        backdrop.classList.remove('show');
                        searchInput.classList.remove('has-results');
                        searchInput.setAttribute('readonly', 'true');
                    }

                    filterProvinces(query) {
                        if (!query) return this.provinces;
                        
                        return this.provinces.filter(province => 
                            province.name.toLowerCase().includes(query) ||
                            (province.zone && province.zone.toLowerCase().includes(query))
                        );
                    }

                    renderResults(provinces) {
                        if (provinces.length === 0) {
                            resultsContainer.innerHTML = '<div class="no-results">ไม่พบจังหวัดที่ค้นหา</div>';
                            return;
                        }

                        resultsContainer.innerHTML = provinces.map(province => `
                            <div class="province-item" data-id="\${province.id}" data-code="\${province.code}" data-zone="\${province.zone || ''}" data-name="\${province.name}">
                                <div class="province-name">\${province.name}</div>
                                <div class="province-meta">
                                    \${province.zone ? `<span class="zone-badge">ภาค\${province.zone}</span>` : ''}
                                    \${province.code ? `<span>รหัส: \${province.code}</span>` : ''}
                                </div>
                            </div>
                        `).join('');
                    }

                    selectProvince(element) {
                        const nameEl = element.querySelector('.province-name');
                        const data = {
                            id: element.dataset.id,
                            code: element.dataset.code,
                            zone: element.dataset.zone,
                            name: element.dataset.name || (nameEl ? nameEl.textContent.trim() : '')
                        };

                        // Update hidden inputs
                        container.querySelector('#province_id').value = data.id;
                        container.querySelector('#province_code').value = data.code;
                        container.querySelector('#province_zone').value = data.zone;

                        // Show selection
                        this.showSelection(data);

                        // Hide dropdown
                        this.hideDropdown();
                    }

                    showSelection(data) {
                        // Update input to show selected province
                        searchInput.value = data.name;
                        searchInput.classList.add('selected');
                        
                        // Show clear button
                        clearBtn.classList.add('show');
                    }

                    clearSelection() {
                        // Clear input
                        searchInput.value = '';
                        searchInput.classList.remove('selected');
                        
                        // Clear hidden inputs
                        container.querySelector('#province_id').value = '';
                        container.querySelector('#province_code').value = '';
                        container.querySelector('#province_zone').value = '';
                        
                        // Hide dropdown
                        this.hideDropdown();
                    }
                }

                // Initialize Province Selector
                new ProvinceSelector();

                // Service Type Selection
                container.querySelectorAll('[data-type]').forEach(item => {
                    item.addEventListener('click', function() {
                        // Remove active from all
                        container.querySelectorAll('[data-type]').forEach(el => {
                            el.classList.remove('bg-[#286f51]', 'text-white', 'font-medium');
                            el.classList.add('text-black');
                        });

                        // Add active to clicked
                        this.classList.add('bg-[#286f51]', 'text-white', 'font-medium');
                        this.classList.remove('text-black');

                        // Set hidden input
                        container.querySelector('#service_type').value = this.dataset.type;

                        // Hide alert if showing
                        container.querySelector('#alertMessage').classList.remove('show');

                        //console.log('Selected service type:', this.dataset.type);
                    });
                });

                // Search Submit Button
                container.querySelector('#searchSubmitBtn').addEventListener('click', function() {
                    const serviceType = container.querySelector('#service_type').value;
                    const provinceCode = container.querySelector('#province_code').value;
                    const zone   = container.querySelector('#province_zone').value;

                    // Validate service type
                    if (!serviceType) {
                        alert('กรุณาเลือกรูปแบบบริการ');
                        return;
                    }

                    // Validate province
                    if (!provinceCode) {
                        alert('กรุณาเลือกจังหวัด');
                        return;
                    }

                    // Build URL based on service type — ใช้ path สัมพัทธ์ ไม่ hardcode โดเมนเต็ม
                    // กันปัญหาเวลาทดสอบบน localhost แล้วดันเด้งไป production จริง
                    let url = '';
                    let params = '?province=' + provinceCode + '&zone=' + zone;
                    if (serviceType === 'NURSING_HOME') {
                        url = '/nursing-home/' + params;
                    } else if (serviceType === 'NURSING') {
                        url = '/nursing/' + params;
                    }
                    //console.log('Redirecting to:', url);
                    
                    // Redirect to search results
                    window.location.href = url;
                });
            })();
            </script>
            </div>
        HTML;
    }
}

new Search();