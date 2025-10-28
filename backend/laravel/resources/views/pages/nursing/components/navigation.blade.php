<div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row gap-6">
    <a href="{{ route('nursing.edit', $nursing->id) }}" class="flex items-center gap-2 {{ request()->routeIs('nursing.edit') ? 'text-green-800 font-medium underline' : '' }}">
        <svg class="w-6 h-6 text-inherit" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="square" stroke-linejoin="round" stroke-width="2" d="M7 19H5a1 1 0 0 1-1-1v-1a3 3 0 0 1 3-3h1m4-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm7.441 1.559a1.907 1.907 0 0 1 0 2.698l-6.069 6.069L10 19l.674-3.372 6.07-6.07a1.907 1.907 0 0 1 2.697 0Z"/>
        </svg>
        <span>ข้อมูลทั่วไป</span>
    </a>
    <a href="{{ route('nursing.history', $nursing->id) }}" class="flex items-center gap-2 {{ request()->routeIs('nursing.history') ? 'text-green-800 font-medium underline' : '' }}">
        <svg class="w-6 h-6 text-inherit" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 4h3a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3m0 3h6m-6 7 2 2 4-4m-5-9v4h4V3h-4Z"/>
        </svg>
        <span>ประวัติ</span>
    </a>
    <a href="{{ route('nursing.detail', $nursing->id) }}" class="flex items-center gap-2 {{ request()->routeIs('nursing.detail') ? 'text-green-800 font-medium underline' : '' }}">
        <svg class="w-6 h-6 text-inherit" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        <span>รายละเอียด</span>
    </a>
    <a href="{{ route('nursing.cost', $nursing->id) }}" class="flex items-center gap-2 {{ request()->routeIs('nursing.cost') ? 'text-green-800 font-medium underline' : '' }}">
        <svg class="w-6 h-6 text-inherit" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
        </svg>
        <span>ค่าบริการ</span>
    </a>
    <a href="#" class="flex items-center gap-2 {{ request()->routeIs('nursing.rate') ? 'text-green-800 font-medium underline' : '' }}">
        <svg class="w-6 h-6 text-inherit" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-width="2" d="M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z"/>
        </svg>
        <span>คะแนนรีวิว</span>
    </a>
</div>