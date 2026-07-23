@extends('layouts.admin')

@section('title', 'Konfigurasi SMTP Email')
@section('header_title', 'Pengaturan Server SMTP & Mailer Engine')

@section('content')
<div x-data="{ testModalOpen: false }" class="space-y-8">
    
    <!-- SECTION 1: FORM CONFIGURATION -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="mail" class="w-5 h-5 text-brand-600"></i> Pengaturan SMTP Email Server
                </h2>
                <p class="text-xs text-slate-500">Konfigurasi akun pengirim email untuk notifikasi otomatis bimbingan.</p>
            </div>
            
            <button @click="testModalOpen = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                <i data-lucide="send" class="w-4 h-4"></i> Kirim Test Email
            </button>
        </div>

        <form action="{{ route('admin.smtp.update') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Row 1: Host & Port -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Host <span class="text-rose-500">*</span></label>
                    <input type="text" name="host" required value="{{ old('host', $setting->host ?? 'smtp.gmail.com') }}"
                           placeholder="smtp.gmail.com / mail.domain.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Port <span class="text-rose-500">*</span></label>
                    <input type="number" name="port" required value="{{ old('port', $setting->port ?? 587) }}"
                           placeholder="587 / 465 / 25"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 text-sm font-semibold">
                </div>
            </div>

            <!-- Row 2: Username & Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Username / Email Akun</label>
                    <input type="text" name="username" value="{{ old('username', $setting->username ?? '') }}"
                           placeholder="user@gmail.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Password (Masked)</label>
                    <input type="password" name="password" placeholder="•••••••• (Biarkan kosong jika tidak diubah)"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 text-sm font-semibold">
                </div>
            </div>

            <!-- Row 3: Encryption & Status Active -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Enkripsi Pengiriman <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-4 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="radio" name="encryption" value="tls" {{ old('encryption', $setting->encryption ?? 'tls') == 'tls' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span>TLS (Recommended 587)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="radio" name="encryption" value="ssl" {{ old('encryption', $setting->encryption ?? '') == 'ssl' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span>SSL (Port 465)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="radio" name="encryption" value="none" {{ old('encryption', $setting->encryption ?? '') == 'none' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span>None</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pengirim Status Active</label>
                    <select name="is_active" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 text-sm font-semibold bg-white">
                        <option value="1" {{ old('is_active', $setting->is_active ?? true) ? 'selected' : '' }}>Aktif (Email dikirim otomatis)</option>
                        <option value="0" {{ !old('is_active', $setting->is_active ?? true) ? 'selected' : '' }}>Non-Aktif (Matikan Email)</option>
                    </select>
                </div>
            </div>

            <!-- Row 4: From Email & From Name -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">From Email (Pengirim) <span class="text-rose-500">*</span></label>
                    <input type="email" name="from_email" required value="{{ old('from_email', $setting->from_email ?? 'no-reply@portal.ac.id') }}"
                           placeholder="no-reply@portal.ac.id"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">From Name (Nama Pengirim) <span class="text-rose-500">*</span></label>
                    <input type="text" name="from_name" required value="{{ old('from_name', $setting->from_name ?? 'Portal Bimbingan Akademik') }}"
                           placeholder="Portal Bimbingan Akademik"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 text-sm font-semibold">
                </div>
            </div>

            <!-- Save Button -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Konfigurasi SMTP
                </button>
            </div>
        </form>
    </div>

    <!-- SECTION 2: AUDIT TRAIL LOGS EMAIL -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-indigo-600"></i> Audit Log Riwayat Pengiriman Email (20 Terakhir)
            </h2>
            <p class="text-xs text-slate-500">Memantau status berhasil/gagal pengiriman email ke mahasiswa.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">Penerima</th>
                        <th class="py-3.5 px-4">Subjek Email</th>
                        <th class="py-3.5 px-4">Status Delivery</th>
                        <th class="py-3.5 px-4">Pesan Error / Detail</th>
                        <th class="py-3.5 px-4">Waktu Dikirim</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($emailLogs as $log)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $log->recipient }}
                            @if($log->appointment)
                            <div class="text-[10px] text-brand-600 font-mono">Kode: {{ $log->appointment->booking_code }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium">
                            {{ $log->subject }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($log->status === 'sent')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    ✓ SENT
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    ✕ FAILED
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 italic max-w-xs truncate" title="{{ $log->error_message }}">
                            {{ $log->error_message ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            {{ $log->created_at->translatedFormat('d M Y H:i') }} WIB
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat email log.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TEST EMAIL -->
    <div x-show="testModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="testModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                    <i data-lucide="send" class="w-5 h-5 text-emerald-600"></i> Kirim Test Email SMTP
                </h3>
                <button @click="testModalOpen = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <p class="text-xs text-slate-600">
                Masukkan alamat email tujuan untuk menguji koneksi SMTP server dan format pengiriman email.
            </p>

            <form action="{{ route('admin.smtp.test') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Tujuan Tes <span class="text-rose-500">*</span></label>
                    <input type="email" name="test_email" required value="{{ Auth::user()->email }}" placeholder="tujuan@domain.com"
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="testModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md">Kirim Email Sekarang</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
