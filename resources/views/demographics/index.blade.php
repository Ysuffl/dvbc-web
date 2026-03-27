@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold border-l-4 border-slate-800 pl-3 text-slate-800">Demographics & Insights</h1>
            <p class="text-sm text-slate-500 mt-1">Comparisons of Visitor Segments ({{ $previousYear }} vs {{ $currentYear }})</p>
        </div>
        
        <!-- Year Filter -->
        <form method="GET" action="{{ route('demographics') }}" class="flex flex-wrap items-center gap-3 bg-white px-5 py-3 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Base Year:</span>
                <select name="year" onchange="this.form.submit()" class="bg-slate-50 text-sm font-black text-slate-800 border-none rounded-lg focus:ring-2 focus:ring-slate-100 cursor-pointer outline-none px-3 py-1">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="h-4 w-px bg-slate-200 mx-2 hidden sm:block"></div>

            <div class="flex items-center gap-2">
                <i data-lucide="repeat" class="w-4 h-4 text-slate-400"></i>
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">Compare With:</span>
                <select name="compare_with" onchange="this.form.submit()" class="bg-slate-50 text-sm font-black text-slate-800 border-none rounded-lg focus:ring-2 focus:ring-slate-100 cursor-pointer outline-none px-3 py-1">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $previousYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Age Segment Table -->
    <div class="bg-white rounded-2xl shadow-[0_2px_15px_rgba(0,0,0,0.02)] border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-xl font-bold text-[#b98e46]">3. Age Segment (Umur Tamu)</h2>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/dreamville.png') }}" alt="Dreamville" class="h-6 opacity-80 mix-blend-multiply">
                <span class="text-xs font-black tracking-widest uppercase text-slate-400 hidden sm:inline-block">Dreamville Beach Club</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left font-medium text-[15px] text-slate-700">
                <thead class="bg-[#d2a65d] text-white">
                    <tr>
                        <th class="px-6 py-4 font-bold border border-[#c19754]">Age</th>
                        <th class="px-6 py-4 font-bold text-center border border-[#c19754]">{{ $previousYear }}</th>
                        <th class="px-6 py-4 font-bold text-center border border-[#c19754]">%</th>
                        <th class="px-6 py-4 font-bold text-center border border-[#c19754]">{{ $currentYear }}</th>
                        <th class="px-6 py-4 font-bold text-center border border-[#c19754]">%</th>
                        <th class="px-6 py-4 font-bold border border-[#c19754] w-1/4">Insight</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 border-b border-slate-200">
                    @foreach($ageData as $age => $row)
                        @php
                            $isBold = in_array($age, ['25-34', '35-44']);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors {{ $isBold ? 'bg-slate-50/30' : '' }}">
                            <td class="px-6 py-4 border-r border-slate-200 {{ $isBold ? 'font-bold text-slate-900' : 'text-slate-600' }}">{{ $age }}</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 {{ $isBold ? 'font-bold text-slate-900' : 'text-slate-600' }}">{{ $row[$previousYear] }}</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 {{ $isBold ? 'font-bold text-slate-900' : 'text-slate-600' }}">{{ $row['prev_pct'] }}%</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 {{ $isBold ? 'font-bold text-slate-900' : 'text-slate-600' }}">{{ $row[$currentYear] }}</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 {{ $isBold ? 'font-bold text-slate-900' : 'text-slate-600' }}">{{ $row['curr_pct'] }}%</td>
                            <td class="px-6 py-4 flex items-center gap-2 {{ $row['insight_color'] ?? 'text-slate-600' }}">
                                {!! str_replace(['↓', '↑', '🔥', '→'], ['<span class="text-lg">↓</span>', '<span class="text-lg">↑</span>', '<span class="text-lg">🔥</span>', '<span class="text-lg">→</span>'], $row['insight'] ?? '') !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Gender Segment Table (Jumlah Gender yang hadir) -->
    <div class="bg-white rounded-2xl shadow-[0_2px_15px_rgba(0,0,0,0.02)] border border-slate-200 overflow-hidden mt-8">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-xl font-bold text-indigo-600">Gender Dynamics (Tamu Berdasarkan Gender)</h2>
            <div class="flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-indigo-400"></i>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left font-medium text-[15px] text-slate-700">
                <thead class="bg-indigo-500 text-white">
                    <tr>
                        <th class="px-6 py-4 font-bold border border-indigo-600">Gender</th>
                        <th class="px-6 py-4 font-bold text-center border border-indigo-600">{{ $previousYear }}</th>
                        <th class="px-6 py-4 font-bold text-center border border-indigo-600">%</th>
                        <th class="px-6 py-4 font-bold text-center border border-indigo-600">{{ $currentYear }}</th>
                        <th class="px-6 py-4 font-bold text-center border border-indigo-600">%</th>
                        <th class="px-6 py-4 font-bold border border-indigo-600 w-1/4">Insight</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 border-b border-slate-200">
                    @foreach($genders as $gender => $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 border-r border-slate-200 font-bold text-slate-800">{{ $gender }}</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 text-slate-600">{{ $row[$previousYear] }}</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 text-slate-600">{{ $row['prev_pct'] }}%</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 text-slate-600">{{ $row[$currentYear] }}</td>
                            <td class="px-6 py-4 text-center border-r border-slate-200 text-slate-600">{{ $row['curr_pct'] }}%</td>
                            <td class="px-6 py-4 flex items-center gap-2 {{ $row['insight_color'] ?? 'text-slate-600' }}">
                                {!! str_replace(['↓', '↑', '🔥', '→'], ['<span class="text-lg">↓</span>', '<span class="text-lg">↑</span>', '<span class="text-lg">🔥</span>', '<span class="text-lg">→</span>'], $row['insight'] ?? '') !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
