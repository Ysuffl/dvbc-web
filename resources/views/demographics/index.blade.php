@extends('layouts.admin')

@section('content')
<div class="space-y-0 animate-in fade-in duration-300">
    <!-- Header Section -->
    <div class="bg-white border-2 border-slate-900 p-8 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex items-start gap-6">
                <div class="w-2 h-16 bg-slate-900"></div>
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase leading-none">Demographics & Insights</h1>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="bg-slate-900 text-white text-[10px] font-black px-2 py-0.5 tracking-[0.2em] uppercase">Executive Report</span>
                        <p class="text-slate-500 font-bold text-xs uppercase tracking-widest">Comparative Analysis: {{ $previousYear }} VS {{ $currentYear }}</p>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <!-- Filters -->
                <form method="GET" action="{{ route('demographics') }}" id="filterForm" 
                    class="flex items-center bg-slate-50 border-2 border-slate-900 p-1 pl-4">
                    <div class="flex items-center gap-6">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Base Year</span>
                            <select name="year" onchange="this.form.submit()" 
                                class="bg-transparent text-sm font-black text-slate-900 border-none p-0 focus:ring-0 cursor-pointer outline-none min-w-[80px]">
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="h-10 w-0.5 bg-slate-200"></div>

                        <div class="flex flex-col pr-4">
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Compare With</span>
                            <select name="compare_with" onchange="this.form.submit()" 
                                class="bg-transparent text-sm font-black text-slate-900 border-none p-0 focus:ring-0 cursor-pointer outline-none min-w-[80px]">
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $previousYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Action Button -->
                <a href="{{ route('demographics', array_merge(request()->all(), ['export' => 1])) }}" 
                   class="flex items-center gap-3 bg-slate-900 hover:bg-black text-white px-8 py-4 border-2 border-slate-900 transition-all font-black text-xs uppercase tracking-[0.2em] active:translate-y-0.5">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Download Spreadsheet
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-12">
        <!-- Age Analysis -->
        <div class="bg-white border-2 border-slate-900">
            <div class="px-8 py-5 border-b-2 border-slate-900 flex items-center justify-between bg-slate-900">
                <h2 class="text-sm font-black text-white tracking-[0.3em] uppercase">01 // AGE SEGMENT DISTRIBUTION</h2>
                <span class="text-[9px] font-black tracking-[0.2em] uppercase text-slate-400">Dreamville Corporate Data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 uppercase tracking-[0.15em] text-slate-900">
                            <th class="px-8 py-5 font-black text-[10px] border-r-2 border-b-2 border-slate-900 w-1/4">Age Classification</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">{{ $previousYear }} COUNT</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">% SHARE</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">{{ $currentYear }} COUNT</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">% SHARE</th>
                            <th class="px-8 py-5 font-black text-[10px] border-b-2 border-slate-900">Performance Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-900">
                        @foreach($ageData as $age => $row)
                            @php $isKey = in_array($age, ['25-34', '35-44']); @endphp
                            <tr class="{{ $isKey ? 'bg-slate-50' : '' }}">
                                <td class="px-8 py-5 border-r-2 border-slate-900 font-black text-xs uppercase">{{ $age }}</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-bold text-slate-500">{{ $row[$previousYear] }}</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-bold text-slate-400">{{ $row['prev_pct'] }}%</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-black text-slate-900 text-base">{{ $row[$currentYear] }}</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-black text-slate-900 text-base">{{ $row['curr_pct'] }}%</td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col gap-1">
                                        <div class="h-1.5 w-12 bg-slate-200">
                                            <div class="h-full {{ str_contains($row['insight'], 'Naik') ? 'bg-slate-900' : (str_contains($row['insight'], 'Turun') ? 'bg-red-600' : 'bg-slate-400') }}" style="width: {{ $row['curr_pct'] }}%"></div>
                                        </div>
                                        <span class="font-black uppercase text-[9px] tracking-widest {{ $row['insight_color'] ?? 'text-slate-500' }}">
                                            {{ mb_substr($row['insight'], 2) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gender Analysis -->
        <div class="bg-white border-2 border-slate-900">
            <div class="px-8 py-5 border-b-2 border-slate-900 flex items-center justify-between bg-slate-900">
                <h2 class="text-sm font-black text-white tracking-[0.3em] uppercase">02 // GENDER DYNAMICS ANALYSIS</h2>
                <div class="w-4 h-4 bg-white"></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 uppercase tracking-[0.15em] text-slate-900">
                            <th class="px-8 py-5 font-black text-[10px] border-r-2 border-b-2 border-slate-900 w-1/4">Gender Classification</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">{{ $previousYear }} COUNT</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">% SHARE</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">{{ $currentYear }} COUNT</th>
                            <th class="px-8 py-5 font-black text-[10px] text-center border-r-2 border-b-2 border-slate-900">% SHARE</th>
                            <th class="px-8 py-5 font-black text-[10px] border-b-2 border-slate-900">Strategic Insight</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-900">
                        @foreach($genders as $gender => $row)
                            <tr>
                                <td class="px-8 py-5 border-r-2 border-slate-900 font-black text-xs uppercase">{{ $gender }}</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-bold text-slate-500">{{ $row[$previousYear] }}</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-bold text-slate-400">{{ $row['prev_pct'] }}%</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-black text-slate-900 text-base">{{ $row[$currentYear] }}</td>
                                <td class="px-8 py-5 text-center border-r-2 border-slate-900 font-black text-slate-900 text-base">{{ $row['curr_pct'] }}%</td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col gap-1">
                                        <div class="h-1.5 w-12 bg-slate-200 text-clip">
                                            <div class="h-full bg-slate-900" style="width: {{ $row['curr_pct'] }}%"></div>
                                        </div>
                                        <span class="font-black uppercase text-[9px] tracking-widest {{ $row['insight_color'] ?? 'text-slate-500' }}">
                                            {{ mb_substr($row['insight'], 2) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection