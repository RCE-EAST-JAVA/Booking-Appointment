@extends('layouts.admin')

@section('title', 'Jadwal Tamu')
@section('header_title', 'Kelola Jadwal Tamu')

@section('content')
<div x-data="{
    activeTab: 'table',
    rejectModalOpen: false,
    rescheduleModalOpen: false,
    selectedId: null,
    selectedCode: '',
    selectedName: '',
    rejectReason: '',
    proposedDate: '{{ date('Y-m-d') }}',
    proposedSlot: '09:00 - 10:00',
    rescheduleReason: '',

    openRejectModal(id, code, name) {
        this.selectedId = id;
        this.selectedCode = code;
        this.selectedName = name;
        this.rejectReason = '';
        this.rejectModalOpen = true;
    },

    openRescheduleModal(id, code, name) {
        this.selectedId = id;
        this.selectedCode = code;
        this.selectedName = name;
        this.rescheduleReason = '';
        this.rescheduleModalOpen = true;
    }
}">

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase block">Hari Ini</span>
                <span class="text-xl font-extrabold text-slate-900">{{ $stats['today'] }}</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/30 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-amber-800 uppercase block">Pending</span>
                <span class="text-xl font-extrabold text-amber-900">{{ $stats['pending'] }}</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/30 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-emerald-800 uppercase block">Disetujui</span>
                <span class="text-xl font-extrabold text-emerald-900">{{ $stats['approved'] }}</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-indigo-200 bg-indigo-50/30 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                <i data-lucide="calendar-sync" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-indigo-800 uppercase block">Rescheduled</span>
                <span class="text-xl font-extrabold text-indigo-900">{{ $stats['rescheduled'] }}</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase block">Selesai</span>
                <span class="text-xl font-extrabold text-slate-900">{{ $stats['completed'] }}</span>
            </div>
        </div>
    </div>
    <!-- OVERVIEW JADWAL HARI INI & MENDATANG -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Card 1: Jadwal Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center font-bold">
                        <i data-lucide="clock-4" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Jadwal Hari Ini</h3>
                        <p class="text-[11px] text-slate-500 font-medium">{{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-bold border border-brand-200">
                    {{ $todayAppointments->count() }} Tamu
                </span>
            </div>

            <div class="p-4 flex-grow space-y-3 overflow-y-auto max-h-[360px]">
                @forelse($todayAppointments as $apt)
                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-grow">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200">
                                {{ $apt->time_slot }} WIB
                            </span>
                            <span class="text-xs font-bold text-slate-900 truncate">{{ $apt->student_name }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 truncate">
                            <strong class="text-slate-700">{{ $apt->purpose }}</strong> &bull; NIM: {{ $apt->nim }} ({{ $apt->department }})
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @php
                            $st = match($apt->status) {
                                'pending' => ['bg' => 'bg-amber-100 text-amber-800', 'txt' => 'Pending'],
                                'approved' => ['bg' => 'bg-emerald-100 text-emerald-800', 'txt' => 'Disetujui'],
                                'rescheduled' => ['bg' => 'bg-indigo-100 text-indigo-800', 'txt' => 'Rescheduled'],
                                'completed' => ['bg' => 'bg-slate-200 text-slate-800', 'txt' => 'Selesai'],
                                default => ['bg' => 'bg-rose-100 text-rose-800', 'txt' => ucfirst($apt->status)],
                            };
                        @endphp
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $st['bg'] }}">
                            {{ $st['txt'] }}
                        </span>

                        @if($apt->status === 'pending')
                            <form action="{{ route('admin.appointments.approve', $apt->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Setujui" class="p-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        @elseif($apt->status === 'approved')
                            <form action="{{ route('admin.appointments.complete', $apt->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Tandai Selesai" class="p-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg transition-colors">
                                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400">
                    <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                    <p class="text-xs font-semibold">Tidak ada jadwal bimbingan / tamu untuk hari ini.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Card 2: Jadwal Mendatang -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                        <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Jadwal Mendatang</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Janji bimbingan hari selanjutnya</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200">
                    {{ $upcomingAppointments->count() }} Mendatang
                </span>
            </div>

            <div class="p-4 flex-grow space-y-3 overflow-y-auto max-h-[360px]">
                @forelse($upcomingAppointments as $apt)
                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-grow">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[11px] font-bold text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200 shadow-2xs">
                                {{ \Carbon\Carbon::parse($apt->appointment_date)->translatedFormat('D, d M Y') }}
                            </span>
                            <span class="text-xs font-mono font-bold text-brand-700">
                                {{ $apt->time_slot }} WIB
                            </span>
                        </div>
                        <p class="text-xs font-bold text-slate-900 mt-1 truncate">{{ $apt->student_name }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ $apt->purpose }} &bull; {{ $apt->department }}</p>
                    </div>

                    <div class="flex-shrink-0 text-right">
                        @php
                            $st = match($apt->status) {
                                'pending' => ['bg' => 'bg-amber-100 text-amber-800', 'txt' => 'Pending'],
                                'approved' => ['bg' => 'bg-emerald-100 text-emerald-800', 'txt' => 'Disetujui'],
                                'rescheduled' => ['bg' => 'bg-indigo-100 text-indigo-800', 'txt' => 'Rescheduled'],
                                default => ['bg' => 'bg-slate-100 text-slate-700', 'txt' => ucfirst($apt->status)],
                            };
                        @endphp
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $st['bg'] }}">
                            {{ $st['txt'] }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400">
                    <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                    <p class="text-xs font-semibold">Belum ada janji bimbingan mendatang.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Filter & View Switcher Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs mb-6 space-y-4 md:space-y-0 md:flex md:items-center md:justify-between">
        
        <!-- Filter Form -->
        <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow">
            <!-- View Mode Hidden -->
            <input type="hidden" name="view" :value="activeTab">

            <!-- Filter Status -->
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rescheduled" {{ request('status') == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>

            <!-- Filter Keperluan -->
            <select name="purpose" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white focus:ring-2 focus:ring-brand-500">
                <option value="">Semua Keperluan</option>
                <option value="Bimbingan" {{ request('purpose') == 'Bimbingan' ? 'selected' : '' }}>Bimbingan</option>
                <option value="Tanda Tangan" {{ request('purpose') == 'Tanda Tangan' ? 'selected' : '' }}>Tanda Tangan</option>
                <option value="Lain-lain" {{ request('purpose') == 'Lain-lain' ? 'selected' : '' }}>Lain-lain</option>
            </select>

            <!-- Filter Date -->
            <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white focus:ring-2 focus:ring-brand-500">

            <!-- Search -->
            <div class="relative flex-grow max-w-xs">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / NIM / Kode..." class="w-full pl-8 pr-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
            </div>

            @if(request()->hasAny(['status', 'purpose', 'date', 'search']))
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-rose-600 hover:underline">Reset Filter</a>
            @endif
        </form>

        <!-- View Switcher -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
            <button @click="activeTab = 'table'" 
                    :class="activeTab === 'table' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900 font-semibold'" 
                    class="px-3 py-1.5 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                <i data-lucide="list" class="w-4 h-4"></i> List View
            </button>
            <button @click="activeTab = 'calendar'" 
                    :class="activeTab === 'calendar' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-900 font-semibold'" 
                    class="px-3 py-1.5 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                <i data-lucide="calendar" class="w-4 h-4"></i> Calendar View
            </button>
        </div>
    </div>

    <!-- TAB 1: LIST VIEW TABLE -->
    <div x-show="activeTab === 'table'" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Kode / Mahasiswa</th>
                        <th class="py-3.5 px-4">Keperluan</th>
                        <th class="py-3.5 px-4">Jadwal Bimbingan</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Quick Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($appointments as $apt)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Student & Booking Code -->
                        <td class="py-4 px-4">
                            <span class="font-mono font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded text-[11px] border border-brand-200">
                                {{ $apt->booking_code }}
                            </span>
                            <div class="font-bold text-slate-900 text-sm mt-1">{{ $apt->student_name }}</div>
                            <div class="text-slate-500 text-[11px]">NIM: {{ $apt->nim }} &bull; {{ $apt->department }}</div>
                            <div class="text-slate-400 text-[10px] mt-0.5">{{ $apt->student_email }}</div>
                        </td>

                        <!-- Purpose -->
                        <td class="py-4 px-4">
                            <span class="font-bold text-slate-800 block">{{ $apt->purpose }}</span>
                            <span class="text-xs text-slate-500 block font-medium">Dosen: {{ $apt->user?->name ?? 'Dosen Utama' }}</span>
                            @if($apt->notes)
                            <p class="text-slate-500 text-[11px] italic mt-0.5 line-clamp-2" title="{{ $apt->notes }}">"{{ $apt->notes }}"</p>
                            @endif
                        </td>

                        <!-- Date & Slot -->
                        <td class="py-4 px-4">
                            <div class="font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($apt->appointment_date)->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-brand-600 font-semibold text-[11px]">{{ $apt->time_slot }} WIB</div>

                            @if($apt->status === 'rescheduled')
                            <div class="mt-1.5 p-1.5 bg-indigo-50 border border-indigo-200 rounded text-[10px] text-indigo-900">
                                <span class="font-bold block">Proposed:</span>
                                {{ \Carbon\Carbon::parse($apt->proposed_date)->translatedFormat('d M Y') }} ({{ $apt->proposed_time_slot }})
                            </div>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-4 px-4">
                            @php
                                $statusBadge = match($apt->status) {
                                    'pending' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'text' => 'Pending'],
                                    'approved' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'text' => 'Disetujui'],
                                    'rescheduled' => ['bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'text' => 'Rescheduled'],
                                    'rejected' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'text' => 'Ditolak'],
                                    'completed' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'text' => 'Selesai'],
                                    'cancelled' => ['bg' => 'bg-slate-100 text-slate-600 border-slate-300', 'text' => 'Dibatalkan'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full font-bold text-[10px] border {{ $statusBadge['bg'] }}">
                                {{ $statusBadge['text'] }}
                            </span>
                        </td>

                        <!-- Quick Actions -->
                        <td class="py-4 px-4 text-right space-x-1">
                            @if($apt->status === 'pending' || $apt->status === 'rescheduled')
                                <form action="{{ route('admin.appointments.approve', $apt->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Setujui janji bimbingan ini?');" 
                                            class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] shadow-xs inline-flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i> Setujui
                                    </button>
                                </form>
                            @endif

                            @if($apt->status !== 'completed' && $apt->status !== 'rejected' && $apt->status !== 'cancelled')
                                <button type="button" @click="openRescheduleModal({{ $apt->id }}, '{{ $apt->booking_code }}', '{{ addslashes($apt->student_name) }}')"
                                        class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-[11px] shadow-xs inline-flex items-center gap-1">
                                    <i data-lucide="calendar-sync" class="w-3.5 h-3.5"></i> Reschedule
                                </button>
                            @endif

                            @if($apt->status === 'approved')
                                <form action="{{ route('admin.appointments.complete', $apt->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-[11px] shadow-xs inline-flex items-center gap-1">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Selesai
                                    </button>
                                </form>
                            @endif

                            @if($apt->status !== 'rejected' && $apt->status !== 'completed' && $apt->status !== 'cancelled')
                                <button type="button" @click="openRejectModal({{ $apt->id }}, '{{ $apt->booking_code }}', '{{ addslashes($apt->student_name) }}')"
                                        class="px-2.5 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg font-bold text-[11px] inline-flex items-center gap-1">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Tolak
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">
                            Tidak ada data janji bimbingan yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $appointments->links() }}
        </div>
    </div>

    <!-- TAB 2: CALENDAR VIEW -->
    <div x-show="activeTab === 'calendar'" x-cloak class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <i data-lucide="calendar-days" class="w-5 h-5 text-brand-600"></i> Kalender Ringkasan Bimbingan
        </h3>
        <p class="text-xs text-slate-500 mb-6">Berikut ringkasan jadwal pertemuan bimbingan yang telah terdaftar pada sistem.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $groupedEvents = $calendarEvents->groupBy('start');
            @endphp

            @forelse($groupedEvents as $date => $events)
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                <div class="font-bold text-xs text-slate-700 uppercase tracking-wider border-b border-slate-200 pb-2 flex items-center justify-between">
                    <span>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                    <span class="px-2 py-0.5 bg-brand-100 text-brand-700 rounded-full text-[10px] font-extrabold">{{ $events->count() }} Pertemuan</span>
                </div>

                <div class="space-y-2 pt-1">
                    @foreach($events as $event)
                    <div class="p-2.5 bg-white border border-slate-200 rounded-lg text-xs shadow-2xs">
                        <div class="font-bold text-slate-900">{{ $event['title'] }}</div>
                        <div class="text-[11px] text-slate-500">{{ $event['code'] }} &bull; {{ $event['purpose'] }}</div>
                        <span class="inline-block mt-1 font-bold text-[9px] uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                            {{ $event['status'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-slate-400">
                Belum ada data jadwal pada kalender.
            </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL 1: REJECT APPOINTMENT -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="rejectModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-rose-700 flex items-center gap-2">
                    <i data-lucide="x-circle" class="w-5 h-5"></i> Tolak Janji Bimbingan
                </h3>
                <button @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <p class="text-xs text-slate-600">
                Anda akan menolak janji bimbingan dari <strong x-text="selectedName"></strong> (Kode: <span class="font-mono" x-text="selectedCode"></span>). Email penolakan otomatis akan dikirim ke mahasiswa.
            </p>

            <form :action="`{{ url('admin/appointments') }}/${selectedId}/reject`" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="reject_reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="reason" id="reject_reason" required rows="3" x-model="rejectReason"
                              placeholder="Tuliskan alasan penolakan agar mahasiswa dapat memahami..."
                              class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: RESCHEDULE APPOINTMENT -->
    <div x-show="rescheduleModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="rescheduleModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-indigo-700 flex items-center gap-2">
                    <i data-lucide="calendar-sync" class="w-5 h-5"></i> Usulkan Perubahan Jadwal (Reschedule)
                </h3>
                <button @click="rescheduleModalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <p class="text-xs text-slate-600">
                Ajukan usulan tanggal & jam baru untuk <strong x-text="selectedName"></strong>. Mahasiswa akan menerima email berisi link tombol untuk menyetujui atau membatalkan.
            </p>

            <form :action="`{{ url('admin/appointments') }}/${selectedId}/reschedule`" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Proposed Tanggal Baru <span class="text-rose-500">*</span></label>
                        <input type="date" name="proposed_date" required min="{{ date('Y-m-d') }}" x-model="proposedDate"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Proposed Time Slot <span class="text-rose-500">*</span></label>
                        <select name="proposed_time_slot" required x-model="proposedSlot" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white">
                            <option value="09:00 - 10:00">09:00 - 10:00 WIB</option>
                            <option value="10:00 - 11:00">10:00 - 11:00 WIB</option>
                            <option value="13:00 - 14:00">13:00 - 14:00 WIB</option>
                            <option value="14:00 - 15:00">14:00 - 15:00 WIB</option>
                            <option value="15:00 - 16:00">15:00 - 16:00 WIB</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Perubahan Jadwal <span class="text-rose-500">*</span></label>
                    <textarea name="reason" required rows="3" x-model="rescheduleReason"
                              placeholder="Contoh: Ada rapat fakultas mendadak pada jam tersebut..."
                              class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="rescheduleModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md">Kirim Usulan Reschedule</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
