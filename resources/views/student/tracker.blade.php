@extends('layouts.app')

@section('title', 'Cek Status Janji Bimbingan')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Cek Status Booking Bimbingan</h1>
            <p class="mt-2 text-sm text-slate-600">Masukkan <strong>NIM</strong> atau <strong>Kode Booking</strong> Anda untuk memantau status pengajuan bimbingan.</p>
        </div>

        <!-- Search Form -->
        <div class="bg-white p-6 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200 mb-8">
            <form action="{{ route('student.tracker') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" required
                           placeholder="Contoh NIM: 21010123 atau Kode Booking: BMB-20260722-XXXX"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm font-semibold transition-all">
                </div>
                <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    Cari Status
                </button>
            </form>
        </div>

        @if(session('status_updated'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('status_updated') }}</span>
        </div>
        @endif

        @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm font-semibold flex items-center gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
        @endif

        <!-- Results List -->
        @if($search)
            @if($appointments->count() > 0)
                <div class="space-y-6">
                    @foreach($appointments as $apt)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden transition-all hover:shadow-lg">
                        
                        <!-- Card Header & Status Badge -->
                        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
                            <div>
                                <span class="text-xs font-mono font-bold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-md border border-brand-200">
                                    {{ $apt->booking_code }}
                                </span>
                                <h3 class="text-lg font-bold text-slate-900 mt-2">{{ $apt->student_name }} ({{ $apt->nim }})</h3>
                                <p class="text-xs text-slate-500">{{ $apt->department }} &bull; {{ $apt->student_email }}</p>
                            </div>

                            <div>
                                @php
                                    $statusBadge = match($apt->status) {
                                        'pending' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'clock', 'text' => 'PENDING (Menunggu Persetujuan)'],
                                        'approved' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => 'check-circle-2', 'text' => 'DISETUJUI (Approved)'],
                                        'rescheduled' => ['bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'icon' => 'calendar-sync', 'text' => 'PERUBAHAN JADWAL (Rescheduled)'],
                                        'rejected' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'icon' => 'x-circle', 'text' => 'DITOLAK (Rejected)'],
                                        'completed' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'check-check', 'text' => 'SELESAI (Completed)'],
                                        'cancelled' => ['bg' => 'bg-slate-100 text-slate-600 border-slate-300', 'icon' => 'ban', 'text' => 'DIBATALKAN (Cancelled)'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusBadge['bg'] }}">
                                    <i data-lucide="{{ $statusBadge['icon'] }}" class="w-4 h-4"></i> {{ $statusBadge['text'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body Details -->
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-xs font-semibold text-slate-500 uppercase block">Tanggal & Slot Waktu</span>
                                    <span class="text-sm font-bold text-slate-900 block mt-1">
                                        {{ \Carbon\Carbon::parse($apt->appointment_date)->translatedFormat('l, d F Y') }}
                                    </span>
                                    <span class="text-xs font-semibold text-brand-600 block mt-0.5">Jam {{ $apt->time_slot }} WIB</span>
                                </div>

                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                                    <span class="text-xs font-semibold text-slate-500 uppercase block">Keperluan & Dosen</span>
                                    <span class="text-sm font-bold text-slate-900 block mt-1">{{ $apt->purpose }}</span>
                                    <span class="text-xs text-slate-600 block mt-0.5">Dosen: {{ $apt->user?->name ?? 'Dosen Pembimbing Utama' }}</span>
                                </div>
                            </div>

                            @if($apt->notes)
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                                <span class="font-bold text-slate-700 block mb-1">Catatan Tambahan:</span>
                                <p class="text-slate-600 italic">"{{ $apt->notes }}"</p>
                            </div>
                            @endif

                            <!-- Reschedule Special Banner -->
                            @if($apt->status === 'rescheduled')
                            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-900 flex items-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-indigo-600"></i> Usulan Jadwal Baru Dari Dosen
                                    </h4>
                                    <span class="text-xs font-bold text-indigo-700">Tindakan Diperlukan</span>
                                </div>
                                <p class="text-xs text-indigo-800">
                                    Alasan: <em>"{{ $apt->reschedule_reason ?? 'Penyesuaian jadwal dosen' }}"</em>
                                </p>
                                <div class="p-3 bg-white rounded-lg border border-indigo-200 flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] font-semibold text-slate-500 block">JADWAL USULAN BARU:</span>
                                        <span class="text-sm font-extrabold text-indigo-950">
                                            {{ \Carbon\Carbon::parse($apt->proposed_date)->translatedFormat('l, d F Y') }} ({{ $apt->proposed_time_slot }} WIB)
                                        </span>
                                    </div>
                                    <a href="{{ route('student.reschedule.show', ['token' => $apt->token]) }}" 
                                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition-all flex items-center gap-1.5">
                                        Respon Usulan Jadwal <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if($apt->status === 'rejected' && $apt->reschedule_reason)
                            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">
                                <span class="font-bold block mb-1">Alasan Penolakan Dosen:</span>
                                <p class="italic">"{{ $apt->reschedule_reason }}"</p>
                            </div>
                            @endif


                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-500">
                    <i data-lucide="search-x" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                    <h3 class="text-base font-bold text-slate-800">Data Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-500 mt-1">Tidak ada data janji bimbingan yang cocok dengan pencarian "<strong>{{ $search }}</strong>".</p>
                </div>
            @endif
        @endif

    </div>
</div>
@endsection
