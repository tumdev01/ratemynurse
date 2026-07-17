@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-row justify-between">
        <h1>
            <a class="underline" href="{{ route('member.index') }}">{{ __('กลับหน้ารายการ') }}</a>
        </h1>
    </div>

    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 flex flex-col justify-between">
        <div class="w-full max-w-[870px] mx-auto flex flex-col gap-[32px]">
            <div class="flex flex-col justify-start bg-[#F0F9F4] p-[16px] rounded-md">
                <span class="htitle text-[16px] md:text-lg text-[#286F51]">ข้อมูลสมาชิก {{ $member->firstname }} {{ $member->lastname }}</span>
                <span class="text-[#8C8A94]">สถานะ: {{ $member->status ? 'ใช้งาน' : 'ระงับ' }}</span>
            </div>

            @if ($member->coverImage?->full_path)
                <img src="{{ $member->coverImage->full_path }}" alt="{{ $member->firstname }}" class="w-32 h-32 object-cover rounded-md shadow">
            @endif

            <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                <span class="text-md text-white font-semibold">ข้อมูลบัญชี</span>
            </span>
            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label>ชื่อ - นามสกุล</label>
                    <span>{{ $member->firstname }} {{ $member->lastname }}</span>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label>อีเมล์</label>
                    <span>{{ $member->email }}</span>
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label>เบอร์โทรศัพท์</label>
                    <span>{{ $member->phone ?? '-' }}</span>
                </div>
                <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                    <label>สมัครเมื่อ</label>
                    <span>{{ optional($member->created_at)->format('d/m/Y H:i') ?? '-' }}</span>
                </div>
            </div>

            @if ($member->profile)
                <span class="w-full min-h-[1px] divider clear-both"></span>
                <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                    <span class="text-md text-white font-semibold">ข้อมูลโปรไฟล์</span>
                </span>
                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label>เพศ</label>
                        <span>{{ $member->profile->gender ?? '-' }}</span>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label>วันเกิด</label>
                        <span>{{ $member->profile->date_of_birth ?? '-' }}</span>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full flex flex-col">
                        <label>ที่อยู่</label>
                        <span>
                            {{ $member->profile->address ?? '-' }}
                            {{ optional($member->profile->subDistrict)->name }}
                            {{ optional($member->profile->district)->name }}
                            {{ optional($member->profile->province)->name }}
                            {{ $member->profile->zipcode }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label>เลขบัตรประชาชน</label>
                        <span>{{ $member->profile->cardid ?? '-' }}</span>
                    </div>
                    <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                        <label>Facebook / Line ID</label>
                        <span>{{ $member->profile->facebook ?? '-' }} / {{ $member->profile->lineid ?? '-' }}</span>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                    <div class="w-full flex flex-col">
                        <label>ผู้ติดต่อฉุกเฉิน</label>
                        <span>
                            {{ $member->profile->contact_person_name ?? '-' }}
                            ({{ $member->profile->contact_person_relation ?? '-' }})
                            {{ $member->profile->contact_person_phone ?? '-' }}
                        </span>
                    </div>
                </div>

                @if ($member->profile->subscriptions && $member->profile->subscriptions->count())
                    <span class="w-full min-h-[1px] divider clear-both"></span>
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <span class="text-md text-white font-semibold">แพ็กเกจสมาชิก</span>
                    </span>
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">แพ็กเกจ</th>
                                <th class="px-6 py-3">เริ่มเมื่อ</th>
                                <th class="px-6 py-3">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800">
                            @foreach ($member->profile->subscriptions as $subscription)
                                <tr>
                                    <td class="px-6 py-4">{{ $subscription->plan }}</td>
                                    <td class="px-6 py-4">{{ optional($subscription->start_date)->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if (now()->gt($subscription->end_date))
                                            <span class="text-red-600 font-medium">หมดอายุแล้ว</span>
                                        @else
                                            <span class="text-green-600 font-medium">ใช้งานอยู่</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if ($subscriptionRequest)
                    <span class="w-full min-h-[1px] divider clear-both"></span>
                    <span class="topic w-full flex flex-row gap-[8px] px-[12px] py-[8px] rounded-lg bg-[#286F51]">
                        <span class="text-md text-white font-semibold">คำขอสมัครแพ็กเกจ</span>
                    </span>
                    <div class="flex flex-col md:flex-row gap-[16px] md:gap-[32px] ct-section">
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label>แพ็กเกจที่ขอ</label>
                            <span>{{ $subscriptionRequest->plan }}</span>
                        </div>
                        <div class="w-full md:w-[calc(50%-16px)] flex flex-col">
                            <label>สถานะ</label>
                            @if ($subscriptionRequest->status === 'awaiting_payment')
                                <span class="text-yellow-600 font-medium">รอการชำระเงิน</span>
                            @elseif ($subscriptionRequest->status === 'cancelled')
                                <span class="text-gray-600 font-medium">ยกเลิก</span>
                            @elseif ($subscriptionRequest->status === 'expired')
                                <span class="text-red-600 font-medium">คำสั่งซื้อหมดอายุ</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
