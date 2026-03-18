<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Portal Disputes</h2>
        <p class="text-sm text-bankos-text-sec mt-1">Customer-raised disputes from the self-service portal</p>
    </x-slot>

    <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach(['open'=>['bg-red-50 border-red-200 dark:bg-red-900/20','text-red-700 dark:text-red-400'],'investigating'=>['bg-amber-50 border-amber-200 dark:bg-amber-900/20','text-amber-700 dark:text-amber-400'],'resolved'=>['bg-green-50 border-green-200 dark:bg-green-900/20','text-green-700 dark:text-green-400'],'rejected'=>['bg-gray-50 border-gray-200 dark:bg-gray-800','text-gray-600 dark:text-gray-400']] as $st=>[$bg,$tc])
        <div class="card p-4 {{ $bg }} border">
            <p class="text-xs font-semibold uppercase tracking-wider {{ $tc }}">{{ ucfirst(str_replace('_',' ',$st)) }}</p>
            <p class="text-2xl font-bold mt-1 {{ $tc }}">{{ $statusCounts[$st] ?? 0 }}</p>
        </div>
        @endforeach
    </div>

    <div class="card">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border flex flex-wrap gap-3">
            <form method="GET" class="flex flex-wrap gap-3 w-full">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search reference, subject, customer…"
                       class="input flex-1 min-w-48 text-sm">
                <select name="status" class="input w-40 text-sm">
                    <option value="">All statuses</option>
                    @foreach(['open','investigating','escalated','resolved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary text-sm">Filter</button>
                @if(request()->anyFilled(['q','status']))
                <a href="{{ route('portal-disputes.index') }}" class="btn btn-secondary text-sm">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase tracking-wider text-bankos-text-sec">
                    <tr>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Raised</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($disputes as $d)
                    @php
                    $sc = match($d->status){
                        'open'          => 'badge-danger',
                        'investigating' => 'badge-pending',
                        'escalated'     => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                        'resolved'      => 'badge-active',
                        default         => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                    };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="px-4 py-3 font-mono text-xs text-bankos-muted">{{ $d->reference }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('customers.show', $d->customer_id) }}" class="font-medium text-bankos-primary hover:underline">{{ $d->first_name }} {{ $d->last_name }}</a>
                            <p class="text-xs text-bankos-muted">{{ $d->customer_number }}</p>
                        </td>
                        <td class="px-4 py-3 capitalize text-bankos-text-sec">{{ str_replace('_',' ',$d->type ?? '—') }}</td>
                        <td class="px-4 py-3 max-w-xs truncate">{{ $d->description ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $sc }} text-xs">{{ strtoupper(str_replace('_',' ',$d->status)) }}</span></td>
                        <td class="px-4 py-3 text-bankos-muted">{{ \Carbon\Carbon::parse($d->created_at)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('portal-disputes.show', $d->id) }}" class="btn btn-secondary text-xs py-1 px-3">Review</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-bankos-muted">No disputes found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($disputes->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">{{ $disputes->links() }}</div>
        @endif
    </div>
</x-app-layout>
