@extends('layouts.admin')

@section('title', 'Pengaturan Profil & Password')
@section('header_title', 'Pengaturan Profil Akun & Keamanan')

@section('content')
<div class="max-w-4xl space-y-8">
    
    <!-- Profile Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-brand-600"></i> Informasi Profil Pengguna
                </h2>
                <p class="text-xs text-slate-500">Perbarui nama tampilan dan username akun login Anda.</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-base">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        </div>

        <form action="{{ route('admin.profile.update') }}" method="POST" class="p-6 space-y-5">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                           placeholder="Contoh: Dr. Honest Dody Molasy"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    @error('name')<p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase mb-1">Username Login</label>
                    <input type="text" name="username" id="username" required value="{{ old('username', $user->email) }}"
                           placeholder="Masukkan username baru"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    <span class="text-[11px] text-slate-500 mt-1 block">Username ini digunakan saat login ke dalam portal admin.</span>
                    @error('username')<p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    <!-- Password Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="key-round" class="w-5 h-5 text-amber-600"></i> Ubah Kata Sandi (Password)
            </h2>
            <p class="text-xs text-slate-500">Gunakan kombinasi kata sandi yang kuat untuk menjaga keamanan akun Anda.</p>
        </div>

        <form action="{{ route('admin.profile.password') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" required
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    @error('current_password')<p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Baru</label>
                        <input type="password" name="new_password" id="new_password" required
                               placeholder="Minimal 6 karakter"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                        @error('new_password')<p class="mt-1 text-xs text-rose-500 font-semibold">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 uppercase mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                               placeholder="Ulangi password baru"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i> Perbarui Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
