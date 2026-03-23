@extends('layouts.admin')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
            <i data-lucide="users" class="w-8 h-8 text-blue-500"></i>
            Customer CRM
        </h1>
        <p class="text-slate-500 text-sm mt-1">Manage all your customer relationships and bookings</p>
    </div>
    <div class="flex items-center gap-2">
        <form action="{{ route('customers') }}" method="GET" class="flex items-center bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm focus-within:ring-2 focus-within:ring-blue-500/20 transition-all">
            <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau telepon..." class="border-none focus:ring-0 text-sm w-64 text-slate-700">
            @if(request('search'))
                <a href="{{ route('customers') }}" class="text-slate-400 hover:text-slate-600 ml-2">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
            @endif
        </form>
    </div>
</div>
<div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr
                class="bg-slate-100 border-b border-slate-200 text-slate-600 text-left text-xs uppercase font-extrabold tracking-wider">
                <th class="px-5 py-4 rounded-tl-lg">Nama Customer</th>
                <th class="px-5 py-4">Meja (Latest)</th>
                <th class="px-5 py-4">Status</th>
                <th class="px-5 py-4">Total Visit</th>
                <th class="px-5 py-4">Keterangan</th>
                <th class="px-5 py-4 rounded-tr-lg">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $c)
            @php
            $latest = $c->bookings->first();
            @endphp
            <tr class="border-b border-slate-100 bg-white text-sm hover:bg-slate-50 transition-colors">
                <td class="px-5 py-4 font-bold text-slate-800">
                    {{ $c->name }}
                    <div class="text-xs text-slate-400 font-normal flex items-center gap-1 mt-1">
                        <i data-lucide="phone" class="w-3 h-3"></i> {{ $c->phone ?? 'No Phone' }}
                    </div>
                </td>
                <td class="px-5 py-4 text-slate-700 font-bold">
                    @if($latest && $latest->tableModel)
                    <div class="flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-blue-500"></i>
                        {{ $latest->tableModel->code }}
                    </div>
                    @else
                    -
                    @endif
                </td>
                <td class="px-5 py-4">
                    @if($latest)
                    <span
                        class="px-3 py-1 bg-{{ $latest->status == 'completed' ? 'teal-50 text-teal-700 ring-1 ring-teal-600/20' : ($latest->status == 'pending' ? 'amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'blue-50 text-blue-700 ring-1 ring-blue-600/20') }} rounded-full text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1">
                        @if($latest->status == 'completed') <i data-lucide="check-circle" class="w-3 h-3"></i>
                        @elseif($latest->status == 'pending') <i data-lucide="clock" class="w-3 h-3"></i>
                        @else <i data-lucide="info" class="w-3 h-3"></i> @endif
                        {{ $latest->status }}
                    </span>
                    @else
                    -
                    @endif
                </td>
                <td class="px-5 py-4 text-slate-600 font-bold">
                    <div class="flex items-center gap-1">
                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                        {{ $c->bookings_count }} <span class="text-slate-400 font-normal">Bookings</span>
                    </div>
                </td>
                <td class="px-5 py-4 text-slate-500 italic text-xs max-w-xs truncate">{{ $latest ? ($latest->notes ??
                    '-') : '-' }}</td>
                <td class="px-5 py-4">
                    @if($c->phone)
                    @php
                    $waNumber = preg_replace('/[^0-9]/', '', $c->phone);
                    if (substr($waNumber, 0, 1) === '0') {
                    $waNumber = '62' . substr($waNumber, 1);
                    }
                    $waMsg = urlencode("Halo {$c->name}, ini reminder booking Anda dari DreamVille.");
                    @endphp
                    <a href="https://web.whatsapp.com/send?phone={{ $waNumber }}&text={{ $waMsg }}" target="_blank"
                        class="inline-flex items-center justify-center gap-2 px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors font-medium text-xs shadow-sm shadow-green-500/30">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" />
                        </svg>
                        WhatsApp
                    </a>
                    @else
                    <span class="text-slate-400 text-xs italic">No phone</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-200">
        {{ $customers->links() }}
    </div>
</div>
@endsection