@extends('layouts.app')

@php
    use App\Enums\SubscriptionPlan;
@endphp

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">

        @if(session('success'))
            <div class="flex flex-col justify-start bg-green-500 p-[16px] rounded-md text-white mb-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="flex flex-row justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Incoming Subscription Requests</h2>
        </div>

        @if($incomingRequests->isEmpty())
            <div class="text-center text-gray-500 py-8">
                No incoming requests.
            </div>
        @else
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">#</th>
                            <th scope="col" class="px-6 py-3">User</th>
                            <th scope="col" class="px-6 py-3">Profile Type</th>
                            <th scope="col" class="px-6 py-3">Profile ID</th>
                            <th scope="col" class="px-6 py-3">Plan</th>
                            <th scope="col" class="px-6 py-3">ราคา</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Submitted At</th>
                            <th scope="col" class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomingRequests as $index => $req)
                            <tr class="bg-white border-b hover:bg-gray-100 cursor-pointer"
                                onclick="window.location='{{ route('subscription.show', $req->id) }}'">
                                <td class="px-6 py-4">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    {{ $req->user->firstname ?? '' }} {{ $req->user->lastname ?? '' }}
                                </td>
                                <td class="px-6 py-4">{{ class_basename($req->type) }}</td>
                                <td class="px-6 py-4">{{ $req->profile_id }}</td>
                                <td class="px-6 py-4">{{ $req->plan }}</td>
                                <td class="px-6 py-4">{{ number_format(SubscriptionPlan::priceFor($req->type, $req->plan)) }} บาท</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        {{ $req->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4" onclick="event.stopPropagation()">
                                    <form id="subForm-accept-{{ $req->id }}" method="POST" action="{{ route('subscription.accept', $req->id) }}" class="hidden">
                                        @csrf
                                    </form>
                                    <form id="subForm-cancel-{{ $req->id }}" method="POST" action="{{ route('subscription.cancel', $req->id) }}" class="hidden">
                                        @csrf
                                    </form>
                                    <select class="border rounded-lg px-2 py-1 text-xs" onchange="handleSubscriptionAction(this, {{ $req->id }})">
                                        <option value="" selected disabled>เลือกการดำเนินการ</option>
                                        <option value="accept">Accept Payment</option>
                                        <option value="cancel">Cancelled</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
@endsection

@section('javascript')
<script>
    function handleSubscriptionAction(select, id) {
        const action = select.value;
        if (!action) return;

        const confirmMsg = action === 'accept' ? 'Accept payment for this request?' : 'Cancel this request?';
        if (!confirm(confirmMsg)) {
            select.value = '';
            return;
        }

        document.getElementById('subForm-' + action + '-' + id).submit();
    }
</script>
@endsection
