@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <form id="createRoom" method="post" action="{{ route('nursing-home.room.update', ['nursing_home_id' => $room->profile_id, 'room_id' => $room->id]) }}" class="flex flex-col gap-[32px] w-full max-w-[870px] mx-auto" enctype="multipart/form-data">
            @csrf
            
            @if(session('success'))
                <div class="flex flex-col justify-start bg-green-500 p-[16px] rounded-md text-white">
                    <span>
                        {{ session('success') }}
                    </span>
                 </div>
            @endif
            @if(session('error'))
                <div class="flex flex-col justify-start bg-red-500 p-[16px] rounded-md text-white">
                    <span>
                        {{ session('error') }}
                    </span>
                 </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">แก้ไขห้องพัก</span>
                <span class="text-[#8C8A94]">กรุณากรอกข้อมูลให้ครบถ้วน</span>
            </div>
            <div id="frm" class="flex flex-col gap-[32px]">

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="name">ชื่อ <span class="req">*</span></label>
                        <input required type="text" name="name" id="name" placeholder="ชื่อห้องพัก"
                            class="border rounded-lg px-3 py-2" value="{{ old('firstname', $room->name ?? '') }}"/>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="type">ประเภทห้อง <span class="req">*</span></label>
                        <select name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option @selected(old('type', $room->type ?? '') == 'SINGLE_ROOM') value="SINGLE_ROOM">ห้องพักเดี่ยว Single Room</option>
                            <option @selected(old('type', $room->type ?? '') == 'TWIN_ROOM') value="TWIN_ROOM">ห้องพักคู่ Twin Room</option>
                        </select>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="description">ข้อความ <span class="req">*</span></label>
                    <textarea required id="description" name="description" class="min-h-[150px] border rounded-lg px-3 py-2" placeholder="ข้อความรายละเอียด">{{ old('description', $room->description ?? '') }}</textarea>
                </div>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="cost_per_day">ราคา/วัน <span class="req">*</span></label>
                        <input required type="number" name="cost_per_day" id="cost_per_day" placeholder="ราคาต่อวัน"
                            class="border rounded-lg px-3 py-2" value="{{ old('cost_per_day', $room->cost_per_day ?? 0) }}"/>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label for="cost_per_month">ราคา/เดือน <span class="req">*</span></label>
                        <input required type="number" name="cost_per_month" id="cost_per_month" placeholder="ราคาต่อเดือน"
                            class="border rounded-lg px-3 py-2" value="{{ old('cost_per_month', $room->cost_per_month ?? 0) }}"/>
                        <label class="error text-xs text-red-600"></label>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="mb-2">รูปภาพ</label>
                    <div class="border border-dashed rounded-lg h-[130px] flex justify-center items-center cursor-pointer" id="uploadArea">
                        <div id="imagesUpload" class="flex flex-row gap-[16px] justify-center">
                            <img id="avatar" src="https://ratemynurse.org/wp-content/uploads/2025/08/upload2.png" loading="lazy" width="70" height="67">
                            <div class="flex flex-col">
                                <label class="text-sm font-semibold">คลิกเพื่ออัปโหลดไฟล์</label>
                                <span class="text-xs">รองรับ .JPG, .PNG | ขนาดไม่เกิน 5 MB</span>
                                <input type="file" id="hiddenRoomUpload" name="images[]" multiple style="display:none">
                            </div>
                        </div>
                    </div>

                    @if ($room->coverImage || $room->images->count() > 0)
                    <div class="mt-4">
                        <label class="mb-2 block font-semibold">รูปภาพปัจจุบัน <span class="text-sm font-normal text-gray-500">(คลิกที่รูปเพื่อตั้งเป็นภาพหน้าปก)</span></label>
                        <div id="existing_images" class="flex flex-row flex-wrap gap-[16px] p-[16px] bg-[#F8F8F8] rounded-[8px]">
                            @if($room->coverImage)
                            <div class="image-item relative" data-id="{{ $room->coverImage->id }}">
                                <img src="{{ $room->coverImage->full_path }}" class="w-[120px] h-[120px] object-cover rounded-lg cursor-pointer border-4 border-green-500" onclick="setCover({{ $room->coverImage->id }})">
                                <span class="cover-badge">ภาพหน้าปก</span>
                                <button type="button" class="delete-btn" onclick="deleteImage({{ $room->coverImage->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </div>
                            @endif
                            @foreach($room->images as $image)
                            <div class="image-item relative" data-id="{{ $image->id }}">
                                <img src="{{ $image->full_path }}" class="w-[120px] h-[120px] object-cover rounded-lg cursor-pointer border-4 border-transparent hover:border-green-300" onclick="setCover({{ $image->id }})">
                                <button type="button" class="delete-btn" onclick="deleteImage({{ $image->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div id="new_images_preview" class="flex flex-row flex-wrap gap-[16px]"></div>
                <input type="hidden" name="delete_images" id="delete_images" value="">
                <input type="hidden" name="cover_image_id" id="cover_image_id" value="{{ $room->coverImage->id ?? '' }}">
                
                <span class="w-full min-h-[1px] divider clear-both"></span>

                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] justify-end">
                    <button type="submit" class="w-[200px] h-[48px] rounded-lg bg-[#286F51] text-white">บันทึกข้อมูล</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('style')
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
    <link href="{{ asset('flatpickr/flatpickr.min.css') }}" rel="stylesheet"/>
    <style>
        .req {color:red}
        .sub_topic:before {
            content: "";
            height:20px;
            width: 6px;
            border-radius: 4px;
            background-color: #286F51;
        }
        .select2-selection {
            border-radius: 0.5rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            padding-top: 0;
            padding-bottom: 0;
            height: 38px !important;
        }
        .select2-dropdown, .select2-selection {border-color: rgb(229, 231, 235) !important;}
        .select2-container--default .select2-selection--single .select2-selection__rendered {line-height: 38px !important;padding-left:0}
        .select2-container--default .select2-selection--single .select2-selection__arrow {height:38px !important;}

        /* Genel stil */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;
            margin: 15px 10px 10px 0;
        }

        /* Giriş stil */
        .toggle-switch .toggle-input {display: none;}

        /* Anahtarın stilinin etrafındaki etiketin stil */
        .toggle-switch .toggle-label {
            position: absolute;
            top: 0;
            left: 0;
            width: 40px;
            height: 24px;
            background-color: #d5d5d5;
            border-radius: 34px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* Anahtarın yuvarlak kısmının stil */
        .toggle-switch .toggle-label::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            background-color: #fff;
            box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }

        /* Anahtarın etkin hale gelmesindeki stil değişiklikleri */
        .toggle-switch .toggle-input:checked + .toggle-label {
        background-color: #4CAF50;
        }

        .toggle-switch .toggle-input:checked + .toggle-label::before {
        transform: translateX(16px);
        }

        /* Light tema */
        .toggle-switch.light .toggle-label {
        background-color: #BEBEBE;
        }

        .toggle-switch.light .toggle-input:checked + .toggle-label {
        background-color: #9B9B9B;
        }

        .toggle-switch.light .toggle-input:checked + .toggle-label::before {
        transform: translateX(6px);
        }

        /* Dark tema */
        .toggle-switch.dark .toggle-label {
        background-color: #4B4B4B;
        }

        .toggle-switch.dark .toggle-input:checked + .toggle-label {
        background-color: #717171;
        }

        .toggle-switch.dark .toggle-input:checked + .toggle-label::before {
        transform: translateX(16px);
        }

        .image-item {
            position: relative;
            display: inline-block;
        }
        .image-item .delete-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #ef4444;
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: background 0.2s;
        }
        .image-item .delete-btn:hover {
            background: #dc2626;
        }
        .image-item .cover-badge {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            background: #22c55e;
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
            white-space: nowrap;
        }
        .image-item img {
            transition: border-color 0.2s;
        }

    </style>
@endsection
@section('javascript')
    <script>
        let selectedFiles = [];
        let deleteImageIds = [];

        // Upload area click
        document.getElementById('uploadArea').addEventListener('click', () => {
            document.getElementById('hiddenRoomUpload').click();
        });

        // File input change
        document.getElementById('hiddenRoomUpload').addEventListener('change', (event) => {
            const files = event.target.files;
            if (files.length > 0) {
                Array.from(files).forEach(file => {
                    if (file.type.startsWith("image/")) {
                        selectedFiles.push(file);
                    }
                });
                renderNewImagesPreview();
                updateFileInput();
            }
        });

        // Render new images preview
        function renderNewImagesPreview() {
            const preview = document.getElementById('new_images_preview');
            preview.innerHTML = '';

            if (selectedFiles.length > 0) {
                const label = document.createElement('label');
                label.className = 'mb-2 block font-semibold w-full';
                label.textContent = 'รูปภาพใหม่ที่จะอัปโหลด';
                preview.appendChild(label);
            }

            selectedFiles.forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'image-item relative';

                const img = document.createElement('img');
                img.className = 'w-[120px] h-[120px] object-cover rounded-lg border-4 border-blue-300';

                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'delete-btn';
                removeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
                removeBtn.onclick = () => removeNewImage(index);

                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);
                preview.appendChild(wrapper);
            });
        }

        // Remove new image
        function removeNewImage(index) {
            selectedFiles.splice(index, 1);
            renderNewImagesPreview();
            updateFileInput();
        }

        // Update file input
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            document.getElementById('hiddenRoomUpload').files = dataTransfer.files;
        }

        // Set cover image
        function setCover(imageId) {
            document.getElementById('cover_image_id').value = imageId;

            // Update UI - remove all cover styles
            document.querySelectorAll('#existing_images .image-item').forEach(item => {
                const img = item.querySelector('img');
                img.classList.remove('border-green-500');
                img.classList.add('border-transparent');
                const badge = item.querySelector('.cover-badge');
                if (badge) badge.remove();
            });

            // Add cover style to selected
            const selectedItem = document.querySelector(`#existing_images .image-item[data-id="${imageId}"]`);
            if (selectedItem) {
                const img = selectedItem.querySelector('img');
                img.classList.remove('border-transparent');
                img.classList.add('border-green-500');

                const badge = document.createElement('span');
                badge.className = 'cover-badge';
                badge.textContent = 'ภาพหน้าปก';
                selectedItem.appendChild(badge);
            }
        }

        // Delete existing image
        function deleteImage(imageId) {
            if (!confirm('ต้องการลบรูปภาพนี้หรือไม่?')) return;

            deleteImageIds.push(imageId);
            document.getElementById('delete_images').value = deleteImageIds.join(',');

            // Remove from UI
            const item = document.querySelector(`#existing_images .image-item[data-id="${imageId}"]`);
            if (item) {
                item.style.opacity = '0.3';
                item.style.pointerEvents = 'none';

                // Add deleted label
                const deletedLabel = document.createElement('span');
                deletedLabel.className = 'absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-red-500 text-white text-xs px-2 py-1 rounded';
                deletedLabel.textContent = 'จะถูกลบ';
                item.appendChild(deletedLabel);
            }

            // If deleted image was cover, clear cover
            const coverId = document.getElementById('cover_image_id').value;
            if (coverId == imageId) {
                document.getElementById('cover_image_id').value = '';
            }
        }
    </script>
@endsection