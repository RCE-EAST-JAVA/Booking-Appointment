@extends('layouts.app')

@section('title', 'Form Pendaftaran Bimbingan Akademik')

@section('content')
<div class="py-12 bg-gradient-to-b from-brand-50/50 to-slate-50 min-h-screen" 
     x-data="{
         selectedDate: '{{ old('appointment_date', date('Y-m-d')) }}',
         selectedSlot: '{{ old('time_slot') }}',
         selectedLecturer: '{{ old('user_id', $lecturers->first()?->id) }}',
         slots: [],
         loadingSlots: false,
         isBlocked: false,
         blockedReason: '',

         fetchSlots() {
             if (!this.selectedDate) return;
             this.loadingSlots = true;
             this.isBlocked = false;
             this.slots = [];

             let url = `{{ route('student.available-slots', [], false) }}?date=${this.selectedDate}`;
             if (this.selectedLecturer) {
                 url += `&user_id=${this.selectedLecturer}`;
             }

             fetch(url, {
                 headers: {
                     'Accept': 'application/json'
                 }
             })
                 .then(res => res.json())
                 .then(data => {
                     this.loadingSlots = false;
                     if (data.is_blocked) {
                         this.isBlocked = true;
                         this.blockedReason = data.reason;
                     } else {
                         this.slots = data.slots;
                     }
                 })
                 .catch(err => {
                     this.loadingSlots = false;
                     console.error('Error fetching slots:', err);
                 });
         }
     }"
     x-init="fetchSlots()">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Hero Title Banner -->
        <div class="text-center mb-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Formulir Janji Bimbingan Akademik</h2>
        </div>

        @if(isset($announcement) && $announcement && $announcement->is_active && !empty($announcement->message))
        <!-- Admin Announcement Banner -->
        <div class="mb-8 p-4 sm:p-5 rounded-2xl bg-amber-50 border-2 border-amber-300/80 shadow-sm flex items-start gap-4 animate-in fade-in zoom-in duration-200">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-md">
                <i data-lucide="megaphone" class="w-5 h-5"></i>
            </div>
            <div class="flex-grow">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-amber-900 mb-0.5">Pengumuman Penting dari Dosen / Admin:</h4>
                <p class="text-sm font-semibold text-amber-950 leading-relaxed">{{ $announcement->message }}</p>
            </div>
        </div>
        @endif

        <!-- Success Modal Alert if Booking Code Generated -->
        @if(session('success_booking'))
        <div class="mb-8 p-6 bg-emerald-50 border-2 border-emerald-500/30 rounded-2xl shadow-xl">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-lg shadow-emerald-500/30">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </div>
                <div class="flex-grow">
                    <h3 class="text-xl font-bold text-emerald-900">Pendaftaran Berhasil Dikirim!</h3>
                    <p class="text-sm text-emerald-700 mt-1">
                        Terima kasih, <strong>{{ session('success_booking')['name'] }}</strong>. Pengajuan janji bimbingan Anda telah tersimpan dengan status <span class="font-bold underline">PENDING</span>.
                    </p>
                    
                    <div class="mt-4 p-4 bg-white border border-emerald-200 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Kode Booking Anda</span>
                            <span class="text-2xl font-extrabold text-brand-700 tracking-widest font-mono">{{ session('success_booking')['code'] }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('student.tracker', ['search' => session('success_booking')['code']]) }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-lg shadow-md transition-all flex items-center gap-2">
                                <i data-lucide="search" class="w-4 h-4"></i> Cek Status Booking
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-8 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Registration Form Card -->
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-brand-600"></i> Informasi Mahasiswa & Janji Temu
                </h2>
                <p class="text-xs text-slate-500">Isi data identitas secara valid agar email konfirmasi dapat terkirim.</p>
            </div>

            <form action="{{ route('student.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                @csrf

                <!-- Row 1: Nama & NIM -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="student_name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="student_name" id="student_name" required value="{{ old('student_name') }}"
                               placeholder="Contoh: Ahmad Rizky" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all">
                        @error('student_name')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="nim" class="block text-sm font-semibold text-slate-700 mb-1">NIM / Nomor Induk Mahasiswa <span class="text-rose-500">*</span></label>
                        <input type="text" name="nim" id="nim" required value="{{ old('nim') }}"
                               placeholder="Contoh: 21010123" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all">
                        @error('nim')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Row 2: Email & Jurusan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="student_email" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Email Aktif <span class="text-rose-500">*</span></label>
                        <input type="email" name="student_email" id="student_email" required value="{{ old('student_email') }}"
                               placeholder="student@univ.ac.id" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all">
                        <span class="text-[11px] text-slate-500 mt-1 block">Surat konfirmasi dan pemberitahuan akan dikirim ke email ini.</span>
                        @error('student_email')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-semibold text-slate-700 mb-1">Program Studi / Jurusan <span class="text-rose-500">*</span></label>
                        <input type="text" name="department" id="department" required value="{{ old('department') }}"
                               placeholder="Contoh: Teknik Informatika / S1" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all">
                        @error('department')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Row 3: Dosen Pembimbing & Keperluan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="user_id" class="block text-sm font-semibold text-slate-700 mb-1">Pilih Dosen Tujuan <span class="text-rose-500">*</span></label>
                        <select name="user_id" id="user_id" required x-model="selectedLecturer" @change="fetchSlots()" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all bg-white">
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->id }}">{{ $lecturer->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="purpose" class="block text-sm font-semibold text-slate-700 mb-1">Keperluan Bimbingan <span class="text-rose-500">*</span></label>
                        <select name="purpose" id="purpose" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all bg-white">
                            <option value="Bimbingan" {{ old('purpose') == 'Bimbingan' ? 'selected' : '' }}>Bimbingan Skripsi / Tesis / Tugas Akhir</option>
                            <option value="Tanda Tangan" {{ old('purpose') == 'Tanda Tangan' ? 'selected' : '' }}>Pengesahan / Tanda Tangan Dokumen</option>
                            <option value="Lain-lain" {{ old('purpose') == 'Lain-lain' ? 'selected' : '' }}>Konsultasi Akademik / Lain-lain</option>
                        </select>
                        @error('purpose')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Notes Textarea -->
                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1">Keterangan Tambahan / Catatan Bimbingan</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Tuliskan topik atau bab yang ingin didiskusikan..."
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm transition-all">{{ old('notes') }}</textarea>
                </div>

                <hr class="border-slate-200 my-6">

                <!-- Section: Picker Tanggal & Time Slot -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="calendar" class="w-5 h-5 text-brand-600"></i> Pilih Tanggal & Waktu Bimbingan
                            </h3>
                            <p class="text-xs text-slate-500">Kuota waktu disesuaikan dengan jam kerja aktif dosen.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Date Input -->
                        <div class="md:col-span-1">
                            <label for="appointment_date" class="block text-xs font-semibold text-slate-600 uppercase mb-1">Pilih Tanggal</label>
                            <input type="date" name="appointment_date" id="appointment_date" required 
                                   min="{{ date('Y-m-d') }}"
                                   x-model="selectedDate"
                                   @change="fetchSlots()"
                                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm font-semibold transition-all">
                        </div>

                        <!-- Slots Grid -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Pilih Slot Waktu Yang Tersedia</label>
                            
                            <!-- Loading Spinner -->
                            <div x-show="loadingSlots" class="p-6 text-center text-slate-500 bg-slate-50 rounded-xl border border-slate-200">
                                <div class="inline-block animate-spin w-5 h-5 border-2 border-brand-600 border-t-transparent rounded-full mb-2"></div>
                                <p class="text-xs font-semibold">Memeriksa kuota slot waktu...</p>
                            </div>

                            <!-- Blocked Warning -->
                            <div x-show="!loadingSlots && isBlocked" x-cloak class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-semibold flex items-center gap-2">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0"></i>
                                <div>
                                    <p class="font-bold">Tanggal Ini Libur / Tidak Melayani Bimbingan</p>
                                    <p class="text-amber-700 font-normal" x-text="blockedReason"></p>
                                </div>
                            </div>

                            <!-- Slots Radio Picker -->
                            <div x-show="!loadingSlots && !isBlocked && slots.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="slot in slots" :key="slot.time_slot">
                                    <label class="relative flex items-center justify-between p-3.5 rounded-xl border-2 cursor-pointer transition-all"
                                           :class="{
                                               'border-brand-600 bg-brand-50/50 shadow-sm': selectedSlot === slot.time_slot && slot.is_available,
                                               'border-slate-200 hover:border-slate-300 bg-white': selectedSlot !== slot.time_slot && slot.is_available,
                                               'border-slate-100 bg-slate-100/70 opacity-60 cursor-not-allowed': !slot.is_available
                                           }">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="time_slot" :value="slot.time_slot" x-model="selectedSlot" :disabled="!slot.is_available" class="text-brand-600 focus:ring-brand-500">
                                            <div>
                                                <span class="block text-xs font-bold text-slate-900" x-text="slot.time_slot + ' WIB'"></span>
                                                <span class="text-[11px] font-semibold"
                                                      :class="slot.is_available ? 'text-emerald-600' : 'text-rose-500'"
                                                      x-text="slot.is_available ? `Sisa Kuota: ${slot.remaining}` : (slot.disabled_reason || 'Kuota Penuh')">
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- No Slots Available -->
                            <div x-show="!loadingSlots && !isBlocked && slots.length === 0" x-cloak class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs text-center font-medium">
                                Tidak ada jadwal bimbingan aktif pada hari ini. Silakan pilih hari lain (Senin - Jumat).
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 text-white font-bold text-base rounded-xl shadow-lg shadow-brand-500/25 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-5 h-5"></i> Ajukan Janji
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
