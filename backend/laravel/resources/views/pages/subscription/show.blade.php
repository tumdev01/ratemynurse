@extends('layouts.app')

@section('content')
<div class="p-4 sm:ml-64">
    <div class="p-4 mb-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700">

        <div class="flex flex-row justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Subscription Request Detail</h2>
            <a href="{{ route('subscription.dashboard') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                Back
            </a>
        </div>

        {{-- Request Info --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Request Info</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">User:</span>
                    <span class="font-medium">{{ $request->user->firstname ?? '' }} {{ $request->user->lastname ?? '' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Profile Type:</span>
                    <span class="font-medium">{{ class_basename($request->type) }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Profile ID:</span>
                    <span class="font-medium">{{ $request->profile_id }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Plan:</span>
                    <span class="font-medium">{{ $request->plan }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    @if($request->status === 'awaiting_payment')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">{{ $request->status }}</span>
                    @elseif($request->status === 'payment_accepted')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">{{ $request->status }}</span>
                    @elseif($request->status === 'expired')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">{{ $request->status }}</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ $request->status }}</span>
                    @endif
                </div>
                <div>
                    <span class="text-gray-500">Submitted At:</span>
                    <span class="font-medium">{{ $request->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if($request->status === 'awaiting_payment')
                <div class="mt-6">
                    <form id="subForm-accept-{{ $request->id }}" method="POST" action="{{ route('subscription.accept', $request->id) }}" class="hidden">
                        @csrf
                    </form>
                    <form id="subForm-cancel-{{ $request->id }}" method="POST" action="{{ route('subscription.cancel', $request->id) }}" class="hidden">
                        @csrf
                    </form>
                    <select class="border rounded-lg px-3 py-2 text-sm" onchange="handleSubscriptionAction(this, {{ $request->id }})">
                        <option value="" selected disabled>เลือกการดำเนินการ</option>
                        <option value="accept">Accept Payment</option>
                        <option value="cancel">Cancelled</option>
                    </select>
                </div>
            @endif
        </div>

        {{-- Current Active Subscription --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Current Active Subscription</h3>

            @if($currentSubscription)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Plan:</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">{{ $currentSubscription->plan }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Start Date:</span>
                        <span class="font-medium">{{ $currentSubscription->start_date->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">End Date:</span>
                        <span class="font-medium">{{ $currentSubscription->end_date }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Status:</span>
                        @if(now()->lte($currentSubscription->end_date))
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Expired</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center text-gray-500 py-4">No active subscription.</div>
            @endif
        </div>

        {{-- Past Subscriptions --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Past Subscriptions</h3>

            @if($pastSubscriptions->isEmpty())
                <div class="text-center text-gray-500 py-4">No past subscriptions.</div>
            @else
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3">#</th>
                                <th scope="col" class="px-4 py-3">Plan</th>
                                <th scope="col" class="px-4 py-3">Start Date</th>
                                <th scope="col" class="px-4 py-3">End Date</th>
                                <th scope="col" class="px-4 py-3">Deleted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pastSubscriptions as $index => $sub)
                                <tr class="bg-white border-b">
                                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">{{ $sub->plan }}</td>
                                    <td class="px-4 py-3">{{ $sub->start_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $sub->end_date }}</td>
                                    <td class="px-4 py-3">{{ $sub->deleted_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Request Timeline --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Request Timeline</h3>

            @if($request->logs->isEmpty())
                <div class="text-center text-gray-500 py-4">No history.</div>
            @else
                <ol class="relative border-l border-gray-200 ml-3">
                    @foreach($request->logs as $log)
                        <li class="mb-6 ml-6">
                            <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3
                                @if($log->action === 'submitted') bg-blue-100 text-blue-600
                                @elseif($log->action === 'awaiting_payment') bg-yellow-100 text-yellow-600
                                @elseif($log->action === 'payment_accepted') bg-green-100 text-green-600
                                @else bg-gray-100 text-gray-600
                                @endif">
                                @if($log->action === 'submitted')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                                @elseif($log->action === 'awaiting_payment')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm1-6H9V6h2v4z"/></svg>
                                @elseif($log->action === 'payment_accepted')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            <div class="flex flex-col">
                                <h4 class="text-sm font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $log->action)) }}</h4>
                                <time class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</time>
                                <p class="text-xs text-gray-400 mt-1">
                                    By: {{ $log->performed_by }}
                                    @if($log->note) &mdash; {{ $log->note }} @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        {{-- All Subscription Tracking Logs --}}
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">All Subscription Tracking</h3>

            @if($allLogs->isEmpty())
                <div class="text-center text-gray-500 py-4">No tracking logs.</div>
            @else
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3">#</th>
                                <th scope="col" class="px-4 py-3">Request ID</th>
                                <th scope="col" class="px-4 py-3">Action</th>
                                <th scope="col" class="px-4 py-3">Performed By</th>
                                <th scope="col" class="px-4 py-3">Note</th>
                                <th scope="col" class="px-4 py-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allLogs as $index => $log)
                                <tr class="bg-white border-b {{ $log->subscription_request_id === $request->id ? 'bg-blue-50' : '' }}">
                                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">#{{ $log->subscription_request_id }}</td>
                                    <td class="px-4 py-3">
                                        @if($log->action === 'submitted')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                        @elseif($log->action === 'awaiting_payment')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                        @elseif($log->action === 'payment_accepted')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $log->performed_by }}</td>
                                    <td class="px-4 py-3">{{ $log->note ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

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
