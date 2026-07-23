@extends('layouts.app')

@section('title', 'Respon Perubahan Jadwal Bimbingan')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full mb-3 uppercase tracking-wider">
                <i data-lucide="calendar-sync" class="w-3.5 h-3.5"></i> Perubahan Jadwal Dosen
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Persetujuan Perubahan Jadwal Bimbingan</h1>
            <p class="text-xs text-slate-600 mt-1">Kode Booking: <strong class="font-mono text-brand-700">{{ $appointment->booking_code }}</strong></p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold">{{ $appointment->student_name }}</h2>
                    <p class="text-xs text-slate-400">{{ $appointment->nim }} &bull; {{ $appointment->department }}</p>
                </div>
                <span class="text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 px-3 py-1 rounded-full">
                    Rescheduled
                </span>
            </div>

            <div class="p-6 space-y-6">
                <!-- Reschedule Reason Box -->
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-xs text-amber-900">
                    <span class="font-bold block text-amber-950 mb-1">Catatan / Alasan Dosen:</span>
                    <p class="italic">"{{ $appointment->reschedule_reason ?? 'Penyesuaian jadwal pertemuan dosen.' }}"</p>
                </div>

                <!-- Comparison Cards -->
                <div>
                    <span class="text-xs font-bold uppercase text-slate-500 tracking-wider block mb-3">Perbandingan Waktu Pertemuan:</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Old Schedule -->
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl relative opacity-75">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider mb-1">JADWAL LAMA</span>
                            <span class="text-sm font-bold text-slate-600 line-through block">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('l, d F Y') }}
                            </span>
                            <span class="text-xs text-slate-500 font-semibold block mt-0.5 line-through">
                                {{ $appointment->time_slot }} WIB
                            </span>
                        </div>

                        <!-- New Proposed Schedule -->
                        <div class="p-4 bg-emerald-50 border-2 border-emerald-500/40 rounded-xl relative">
                            <span class="text-[10px] font-bold uppercase text-emerald-700 block tracking-wider mb-1">USULAN JADWAL BARU</span>
                            <span class="text-sm font-extrabold text-emerald-950 block">
                                {{ \Carbon\Carbon::parse($appointment->proposed_date)->translatedFormat('l, d F Y') }}
                            </span>
                            <span class="text-xs text-emerald-800 font-bold block mt-0.5">
                                {{ $appointment->proposed_time_slot }} WIB
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <form action="{{ route('student.reschedule.action', ['token' => $appointment->token]) }}" method="POST" class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-3">
                    @csrf
                    
                    <button type="submit" name="action" value="accept" 
                            class="flex-1 py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Terima Jadwal Baru
                    </button>

                    <button type="submit" name="action" value="cancel" 
                            onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan bimbingan ini?');"
                            class="flex-1 py-3.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i> Batalkan Pengajuan
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
