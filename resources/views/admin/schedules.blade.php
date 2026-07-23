@extends('layouts.admin')

@section('title', 'Pengaturan Jam Kerja & Kalender Libur')
@section('header_title', 'Konfigurasi Jam Bimbingan & Kalender Libur')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('schedulesManager', () => ({
        activeTab: 'setting',
        currentMonth: '{{ date('Y-m') }}',
        monthName: '',
        startDayOffset: 0,
        days: [],
        masterSlots: [],
        loadingCalendar: true,
        syncingHolidays: false,

        // Modal state for date override
        modalOpen: false,
        selectedDate: '',
        formattedSelectedDate: '',
        isAvailable: true,
        reason: '',
        unavailableSlots: [],
        unavailableStart: '',
        unavailableEnd: '',
        unavailableRanges: [],
        dayAppointments: [],
        hasOverride: false,

        // Reschedule & Reject modal state for guest list
        rejectModalOpen: false,
        rescheduleModalOpen: false,
        selectedId: null,
        selectedCode: '',
        selectedName: '',
        rejectReason: '',
        proposedDate: '{{ date('Y-m-d') }}',
        proposedSlot: '09:00 - 10:00',
        rescheduleReason: '',

        // Reschedule availability preview state
        rescheduleSlots: [],
        loadingRescheduleSlots: false,
        rescheduleDateBlocked: false,
        rescheduleBlockedReason: '',

        // AJAX loading state
        actionLoadingId: null,

        fetchRescheduleSlots() {
            if (!this.proposedDate) return;
            this.loadingRescheduleSlots = true;
            this.rescheduleDateBlocked = false;
            this.rescheduleSlots = [];

            fetch(`{{ route('student.available-slots', [], false) }}?date=${this.proposedDate}`)
                .then(res => res.json())
                .then(data => {
                    this.loadingRescheduleSlots = false;
                    if (data.is_blocked) {
                        this.rescheduleDateBlocked = true;
                        this.rescheduleBlockedReason = data.reason;
                    } else {
                        this.rescheduleSlots = data.slots;
                        if (data.slots.length > 0 && (!this.proposedSlot || !data.slots.some(s => s.time_slot === this.proposedSlot))) {
                            let avail = data.slots.find(s => s.is_available);
                            if (avail) this.proposedSlot = avail.time_slot;
                            else if (data.slots[0]) this.proposedSlot = data.slots[0].time_slot;
                        }
                    }
                })
                .catch(err => {
                    this.loadingRescheduleSlots = false;
                });
        },

        openRejectModal(id, code, name) {
            this.selectedId = id;
            this.selectedCode = code;
            this.selectedName = name;
            this.rejectReason = '';
            this.rejectModalOpen = true;
        },

        openRescheduleModal(id, code, name, currentDate) {
            this.selectedId = id;
            this.selectedCode = code;
            this.selectedName = name;
            this.rescheduleReason = '';
            this.proposedDate = currentDate || '{{ date('Y-m-d') }}';
            this.rescheduleModalOpen = true;
            this.fetchRescheduleSlots();
        },

        async approveAppointment(id) {
            this.actionLoadingId = id;
            let csrf = '{{ csrf_token() }}';
            try {
                let res = await fetch(`{{ url('/admin/appointments', [], false) }}/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                this.actionLoadingId = null;
                let el = document.getElementById('status-badge-' + id);
                if (el) {
                    el.className = 'inline-flex items-center px-2.5 py-1 rounded-full font-bold text-[10px] border bg-emerald-50 text-emerald-700 border-emerald-200';
                    el.innerText = 'Disetujui';
                }
                let actionsEl = document.getElementById('action-buttons-' + id);
                if (actionsEl) {
                    actionsEl.innerHTML = `<button type="button" @click="completeAppointment(${id})" class="px-2.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-[11px]">Selesai</button>`;
                }
            } catch(e) {
                this.actionLoadingId = null;
                alert('Gagal memproses permohonan.');
            }
        },

        async completeAppointment(id) {
            this.actionLoadingId = id;
            let csrf = '{{ csrf_token() }}';
            try {
                let res = await fetch(`{{ url('/admin/appointments', [], false) }}/${id}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                this.actionLoadingId = null;
                let el = document.getElementById('status-badge-' + id);
                if (el) {
                    el.className = 'inline-flex items-center px-2.5 py-1 rounded-full font-bold text-[10px] border bg-blue-50 text-blue-700 border-blue-200';
                    el.innerText = 'Selesai';
                }
                let actionsEl = document.getElementById('action-buttons-' + id);
                if (actionsEl) actionsEl.innerHTML = '';
            } catch(e) {
                this.actionLoadingId = null;
                alert('Gagal memproses permohonan.');
            }
        },

        async submitReject() {
            if (!this.selectedId) return;
            let id = this.selectedId;
            this.actionLoadingId = id;
            let csrf = '{{ csrf_token() }}';
            let formData = new FormData();
            formData.append('reason', this.rejectReason);

            try {
                let res = await fetch(`{{ url('/admin/appointments', [], false) }}/${id}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                this.rejectModalOpen = false;
                this.actionLoadingId = null;
                let el = document.getElementById('status-badge-' + id);
                if (el) {
                    el.className = 'inline-flex items-center px-2.5 py-1 rounded-full font-bold text-[10px] border bg-rose-50 text-rose-700 border-rose-200';
                    el.innerText = 'Ditolak';
                }
                let actionsEl = document.getElementById('action-buttons-' + id);
                if (actionsEl) actionsEl.innerHTML = '';
            } catch(e) {
                this.actionLoadingId = null;
                alert('Gagal menolak janji.');
            }
        },

        async submitReschedule() {
            if (!this.selectedId) return;
            let id = this.selectedId;
            this.actionLoadingId = id;
            let csrf = '{{ csrf_token() }}';
            let formData = new FormData();
            formData.append('proposed_date', this.proposedDate);
            formData.append('proposed_time_slot', this.proposedSlot);
            formData.append('reason', this.rescheduleReason);

            try {
                let res = await fetch(`{{ url('/admin/appointments', [], false) }}/${id}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                this.rescheduleModalOpen = false;
                this.actionLoadingId = null;
                let el = document.getElementById('status-badge-' + id);
                if (el) {
                    el.className = 'inline-flex items-center px-2.5 py-1 rounded-full font-bold text-[10px] border bg-indigo-50 text-indigo-700 border-indigo-200';
                    el.innerText = 'Rescheduled';
                }
                let dateEl = document.getElementById('date-slot-' + id);
                if (dateEl) {
                    dateEl.innerHTML = `<div class="font-bold text-slate-900">${this.proposedDate}</div><div class="text-indigo-600 font-semibold text-[11px]">${this.proposedSlot} WIB</div>`;
                }
            } catch(e) {
                this.actionLoadingId = null;
                alert('Gagal mengirim usulan reschedule.');
            }
        },

        fetchMonth(month) {
            this.loadingCalendar = true;
            this.currentMonth = month;
            fetch(`{{ route('admin.calendar.month-data', [], false) }}?month=${month}`)
                .then(res => res.json())
                .then(data => {
                    this.monthName = data.month_name;
                    this.startDayOffset = data.start_day_of_week - 1;
                    this.days = data.days;
                    this.masterSlots = data.master_slots;
                    this.loadingCalendar = false;
                })
                .catch(err => {
                    console.error('Failed fetching calendar:', err);
                    this.loadingCalendar = false;
                });
        },

        prevMonth() {
            let parts = this.currentMonth.split('-');
            let d = new Date(parts[0], parts[1] - 2, 1);
            let m = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            this.fetchMonth(m);
        },

        nextMonth() {
            let parts = this.currentMonth.split('-');
            let d = new Date(parts[0], parts[1], 1);
            let m = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            this.fetchMonth(m);
        },

        todayMonth() {
            let now = new Date();
            let m = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
            this.fetchMonth(m);
        },

        syncHolidays() {
            let year = this.currentMonth.split('-')[0];
            if (!confirm(`Sinkronkan tanggal merah & libur nasional Indonesia tahun ${year} dari Open API?`)) return;
            this.syncingHolidays = true;
            let csrf = '{{ csrf_token() }}';

            fetch('{{ route('admin.sync-holidays', [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ year: year })
            })
            .then(res => res.json())
            .then(data => {
                this.syncingHolidays = false;
                alert(data.message);
                this.fetchMonth(this.currentMonth);
            })
            .catch(err => {
                this.syncingHolidays = false;
                alert('Gagal menghubungkan ke Open API Tanggal Merah.');
            });
        },

        openDayModal(dayObj) {
            this.selectedDate = dayObj.date;
            let d = new Date(dayObj.date + 'T00:00:00');
            let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            this.formattedSelectedDate = d.toLocaleDateString('id-ID', options);
            
            this.isAvailable = dayObj.is_available;
            this.reason = dayObj.reason || '';
            this.unavailableSlots = Array.isArray(dayObj.unavailable_slots) ? [...dayObj.unavailable_slots] : [];
            this.unavailableStart = dayObj.unavailable_start || '';
            this.unavailableEnd = dayObj.unavailable_end || '';
            this.unavailableRanges = Array.isArray(dayObj.unavailable_ranges) ? JSON.parse(JSON.stringify(dayObj.unavailable_ranges)) : [];
            this.dayAppointments = Array.isArray(dayObj.appointments) ? [...dayObj.appointments] : [];
            this.hasOverride = dayObj.has_override;
            
            this.modalOpen = true;
        },

        saveDayOverride() {
            if (this.unavailableStart && this.unavailableEnd && this.unavailableStart >= this.unavailableEnd) {
                alert('Jam mulai tidak boleh lebih lambat atau sama dengan jam selesai!');
                return;
            }

            for (let i = 0; i < this.unavailableRanges.length; i++) {
                let r = this.unavailableRanges[i];
                if (r.start && r.end) {
                    if (r.start >= r.end) {
                        alert(`Jam mulai (${r.start}) tidak boleh lebih lambat atau sama dengan jam selesai (${r.end}) pada baris ke-${i + 1}!`);
                        return;
                    }
                } else {
                    alert(`Harap isi lengkap jam mulai dan jam selesai pada baris ke-${i + 1}!`);
                    return;
                }
            }

            let csrf = '{{ csrf_token() }}';
            fetch('{{ route('admin.date-override.save', [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    date: this.selectedDate,
                    is_available: this.isAvailable ? 1 : 0,
                    reason: this.reason,
                    unavailable_slots: this.unavailableSlots,
                    unavailable_start: this.unavailableStart || null,
                    unavailable_end: this.unavailableEnd || null,
                    unavailable_ranges: this.unavailableRanges
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success === false) {
                    alert(data.message);
                    return;
                }
                this.modalOpen = false;
                this.fetchMonth(this.currentMonth);
            });
        },

        resetDayOverride() {
            if (!confirm('Kembalikan pengaturan tanggal ini ke status default?')) return;
            let csrf = '{{ csrf_token() }}';
            fetch('{{ route('admin.date-override.delete', [], false) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    date: this.selectedDate
                })
            })
            .then(res => res.json())
            .then(data => {
                this.modalOpen = false;
                this.fetchMonth(this.currentMonth);
            });
        }
    }));
});
</script>

<div class="space-y-8" x-data="schedulesManager" x-init="fetchMonth(currentMonth)">

    <!-- TAB NAVIGATION BAR -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button @click="activeTab = 'setting'"
                    :class="activeTab === 'setting' ? 'bg-brand-600 text-white shadow-md font-bold' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all">
                <i data-lucide="settings" class="w-4 h-4"></i> Setting Kalender & Slot Jam
            </button>
            <button @click="activeTab = 'daftar_tamu'"
                    :class="activeTab === 'daftar_tamu' ? 'bg-brand-600 text-white shadow-md font-bold' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all">
                <i data-lucide="users" class="w-4 h-4"></i> Daftar Tamu Terdaftar
            </button>
            <button @click="activeTab = 'pengumuman'"
                    :class="activeTab === 'pengumuman' ? 'bg-brand-600 text-white shadow-md font-bold' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 font-semibold'"
                    class="px-4 py-2.5 rounded-xl text-xs flex items-center gap-2 transition-all">
                <i data-lucide="megaphone" class="w-4 h-4"></i> Pengumuman Portal Publik
            </button>
        </div>

        <template x-if="activeTab === 'setting'">
            <button @click="syncHolidays()" :disabled="syncingHolidays"
                    class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-2xs transition-all flex items-center gap-1.5 disabled:opacity-50">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="syncingHolidays ? 'animate-spin' : ''"></i>
                <span x-text="syncingHolidays ? 'Menyingkronkan...' : '🇮🇩 Sync Tanggal Merah (API)'"></span>
            </button>
        </template>
    </div>


    <!-- TAB 1: SETTING KALENDER & SLOT JAM -->
    <div x-show="activeTab === 'setting'" class="space-y-8 animate-in fade-in duration-150">
        
        <!-- SECTION 1: INTERACTIVE MONTHLY CALENDAR TILES -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            
            <!-- Header Controls -->
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-brand-600"></i> Kalender Setting Hari Libur & Jam Off (Tile View)
                    </h2>
                    <p class="text-xs text-slate-500">Klik ubin tanggal untuk mengatur Libur / Buka serta menonaktifkan jam tertentu (misal: Rapat/Dinas).</p>
                </div>

                <!-- Month Navigation Buttons -->
                <div class="flex items-center gap-2">
                    <button @click="prevMonth()" class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition-all shadow-2xs">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <span class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-900 shadow-2xs min-w-[140px] text-center" x-text="monthName || 'Memuat...'"></span>
                    <button @click="nextMonth()" class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition-all shadow-2xs">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                    <button @click="todayMonth()" class="px-3 py-2 rounded-xl border border-brand-200 bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition-all shadow-2xs">
                        Hari Ini
                    </button>
                </div>
            </div>

            <!-- Legend Bar -->
            <div class="px-6 py-3 bg-slate-100/60 border-b border-slate-200/80 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-600">
                <span class="font-bold text-slate-800">Keterangan Status:</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Available / Buka</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Libur / Closed</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> ⚠️ Ada Jam Off</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> 🔵 Ada Tamu Terdaftar</span>
            </div>

            <!-- Calendar Grid Container -->
            <div class="p-6">
                <div x-show="loadingCalendar" class="py-16 text-center text-slate-400">
                    <div class="inline-block animate-spin w-6 h-6 border-2 border-brand-600 border-t-transparent rounded-full mb-2"></div>
                    <p class="text-xs font-semibold">Memuat kalender tile per bulan...</p>
                </div>

                <div x-show="!loadingCalendar" class="space-y-2">
                    <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">
                        <div class="p-2 bg-slate-100/80 rounded-lg">Senin</div>
                        <div class="p-2 bg-slate-100/80 rounded-lg">Selasa</div>
                        <div class="p-2 bg-slate-100/80 rounded-lg">Rabu</div>
                        <div class="p-2 bg-slate-100/80 rounded-lg">Kamis</div>
                        <div class="p-2 bg-slate-100/80 rounded-lg">Jumat</div>
                        <div class="p-2 bg-rose-50 text-rose-700 rounded-lg">Sabtu</div>
                        <div class="p-2 bg-rose-50 text-rose-700 rounded-lg">Minggu</div>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        <template x-for="i in startDayOffset" :key="'blank-' + i">
                            <div class="min-h-[96px] bg-slate-50/40 rounded-xl border border-dashed border-slate-200"></div>
                        </template>

                        <template x-for="dayObj in days" :key="dayObj.date">
                            <div @click="openDayModal(dayObj)" 
                                 class="min-h-[100px] p-2.5 rounded-2xl border-2 transition-all cursor-pointer flex flex-col justify-between relative group hover:shadow-md"
                                 :class="{
                                     'border-emerald-300 bg-emerald-50/20 hover:border-emerald-500': dayObj.is_available && dayObj.unavailable_slots.length === 0 && !dayObj.unavailable_start && dayObj.unavailable_ranges.length === 0,
                                     'border-amber-300 bg-amber-50/20 hover:border-amber-500': dayObj.is_available && (dayObj.unavailable_slots.length > 0 || dayObj.unavailable_start || dayObj.unavailable_ranges.length > 0),
                                     'border-rose-200 bg-rose-50/30 hover:border-rose-400': !dayObj.is_available,
                                     'ring-2 ring-brand-500 ring-offset-2': dayObj.is_today
                                 }">
                                
                                <div class="flex items-start justify-between gap-1">
                                    <span class="text-sm font-extrabold" 
                                          :class="dayObj.is_today ? 'text-brand-600 bg-brand-100 px-2 py-0.5 rounded-lg' : (dayObj.is_weekend ? 'text-rose-600' : 'text-slate-800')" 
                                          x-text="dayObj.day">
                                    </span>

                                    <div class="flex items-center gap-1">
                                        <template x-if="dayObj.has_override">
                                            <span title="Diedit Manual (Override)" class="w-2 h-2 rounded-full bg-brand-600"></span>
                                        </template>

                                        <template x-if="dayObj.booked_count > 0">
                                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-extrabold flex items-center justify-center shadow-xs flex-shrink-0" title="Jumlah mahasiswa mendaftar" x-text="dayObj.booked_count">
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-2 space-y-1">
                                    <template x-if="dayObj.is_available && dayObj.unavailable_slots.length === 0 && !dayObj.unavailable_start && dayObj.unavailable_ranges.length === 0">
                                        <span class="inline-block w-full text-center px-1.5 py-1 rounded-lg bg-emerald-100/80 text-emerald-800 text-[10px] font-extrabold truncate">
                                            Available / Buka
                                        </span>
                                    </template>

                                    <template x-if="dayObj.is_available && (dayObj.unavailable_slots.length > 0 || dayObj.unavailable_start || dayObj.unavailable_ranges.length > 0)">
                                        <span class="inline-block w-full text-center px-1.5 py-1 rounded-lg bg-amber-100 text-amber-900 text-[10px] font-extrabold truncate">
                                            ⚠️ Off (<span x-text="dayObj.unavailable_ranges.length > 0 ? `${dayObj.unavailable_ranges.length} sesi` : (dayObj.unavailable_start ? '1 sesi' : `${dayObj.unavailable_slots.length} jam`)"></span>)
                                        </span>
                                    </template>

                                    <template x-if="!dayObj.is_available">
                                        <span class="inline-block w-full text-center px-1.5 py-1 rounded-lg bg-rose-100 text-rose-800 text-[10px] font-extrabold truncate" :title="dayObj.reason || 'Libur'">
                                            Libur / Closed
                                        </span>
                                    </template>

                                    <template x-if="dayObj.reason">
                                        <p class="text-[10px] text-slate-500 italic truncate font-medium mt-0.5" x-text="dayObj.reason"></p>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: JAM KERJA DEFAULT & SLOT BIMBINGAN MINGGUAN -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="clock" class="w-5 h-5 text-brand-600"></i> Slot Waktu & Kuota Default Bimbingan Mingguan
                    </h2>
                    <p class="text-xs text-slate-500">Tentukan jam layanan default harian (misal: 08:00 - 15:00) yang berlaku setiap minggunya.</p>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Add New Schedule Form -->
                <form action="{{ route('admin.schedules.store') }}" method="POST" class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Kerja</label>
                            <select name="day_of_week" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white">
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                                <option value="0">Minggu</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Slot Waktu (Jam)</label>
                            <input type="text" name="time_slot" required placeholder="Contoh: 08:00 - 09:00" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Maksimal Kuota (Mhs)</label>
                            <input type="number" name="quota" required min="1" value="3" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold">
                        </div>

                        <div>
                            <button type="submit" class="w-full py-2 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5">
                                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Slot Waktu
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Table of Schedules -->
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Hari</th>
                                <th class="py-3 px-4">Slot Waktu</th>
                                <th class="py-3 px-4">Kuota Mahasiswa</th>
                                <th class="py-3 px-4">Status Aktif</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($schedules as $sched)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $dayNames[$sched->day_of_week] ?? $sched->day_of_week }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-brand-700">
                                    {{ $sched->time_slot }} WIB
                                </td>

                                <form action="{{ route('admin.schedules.update', $sched->id) }}" method="POST">
                                    @csrf
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="quota" value="{{ $sched->quota }}" min="1" class="w-16 px-2 py-1 border border-slate-300 rounded text-xs font-bold text-center">
                                            <span class="text-slate-500">mhs</span>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <select name="is_active" class="px-2 py-1 border border-slate-300 rounded text-xs font-semibold bg-white">
                                            <option value="1" {{ $sched->is_active ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ !$sched->is_active ? 'selected' : '' }}>Non-Aktif</option>
                                        </select>
                                    </td>

                                    <td class="py-3.5 px-4 text-right space-x-1">
                                        <button type="submit" title="Simpan Perubahan" class="px-2.5 py-1 bg-slate-900 text-white rounded text-[11px] font-bold">
                                            Simpan
                                        </button>
                                </form>

                                        <form action="{{ route('admin.schedules.delete', $sched->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Hapus slot ini?');" title="Hapus Slot" class="px-2 py-1 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded text-[11px] font-bold">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada slot waktu default yang dikonfigurasi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- TAB 2: DAFTAR TAMU TERDAFTAR -->
    <div x-show="activeTab === 'daftar_tamu'" class="space-y-6 animate-in fade-in duration-150">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs mb-4 space-y-4 md:space-y-0 md:flex md:items-center md:justify-between">
            <form action="{{ route('admin.schedules.index') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow">
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rescheduled" {{ request('status') == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                </select>

                <select name="purpose" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white">
                    <option value="">Semua Keperluan</option>
                    <option value="Bimbingan" {{ request('purpose') == 'Bimbingan' ? 'selected' : '' }}>Bimbingan</option>
                    <option value="Tanda Tangan" {{ request('purpose') == 'Tanda Tangan' ? 'selected' : '' }}>Tanda Tangan</option>
                    <option value="Lain-lain" {{ request('purpose') == 'Lain-lain' ? 'selected' : '' }}>Lain-lain</option>
                </select>

                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white">

                <div class="relative flex-grow max-w-xs">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / NIM / Kode..." class="w-full pl-8 pr-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
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
                            <td class="py-4 px-4">
                                <span class="font-mono font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded text-[11px] border border-brand-200">
                                    {{ $apt->booking_code }}
                                </span>
                                <div class="font-bold text-slate-900 text-sm mt-1">{{ $apt->student_name }}</div>
                                <div class="text-slate-500 text-[11px]">NIM: {{ $apt->nim }} &bull; {{ $apt->department }}</div>
                                <div class="text-slate-400 text-[10px] mt-0.5">{{ $apt->student_email }}</div>
                            </td>

                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-800 block">{{ $apt->purpose }}</span>
                                <span class="text-xs text-slate-500 block font-medium">Dosen: {{ $apt->user?->name ?? 'Dosen Utama' }}</span>
                                @if($apt->notes)
                                <p class="text-slate-500 text-[11px] italic mt-0.5 line-clamp-2" title="{{ $apt->notes }}">"{{ $apt->notes }}"</p>
                                @endif
                            </td>

                            <td class="py-4 px-4">
                                <div id="date-slot-{{ $apt->id }}">
                                    <div class="font-bold text-slate-900">
                                        {{ \Carbon\Carbon::parse($apt->appointment_date)->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-brand-600 font-semibold text-[11px]">{{ $apt->time_slot }} WIB</div>
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                @php
                                    $st = match($apt->status) {
                                        'pending' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'txt' => 'Pending'],
                                        'approved' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'txt' => 'Disetujui'],
                                        'rescheduled' => ['bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'txt' => 'Rescheduled'],
                                        'rejected' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'txt' => 'Ditolak'],
                                        'completed' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'txt' => 'Selesai'],
                                        default => ['bg' => 'bg-slate-100 text-slate-600 border-slate-300', 'txt' => ucfirst($apt->status)],
                                    };
                                @endphp
                                <span id="status-badge-{{ $apt->id }}" class="inline-flex items-center px-2.5 py-1 rounded-full font-bold text-[10px] border {{ $st['bg'] }}">
                                    {{ $st['txt'] }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div id="action-buttons-{{ $apt->id }}" class="flex items-center justify-end gap-1">
                                    <template x-if="actionLoadingId === {{ $apt->id }}">
                                        <span class="inline-flex items-center gap-1.5 text-slate-500 text-xs font-semibold bg-slate-100 px-3 py-1 rounded-lg">
                                            <svg class="animate-spin w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Memproses...
                                        </span>
                                    </template>

                                    <template x-if="actionLoadingId !== {{ $apt->id }}">
                                        <div class="inline-flex items-center gap-1">
                                            @if($apt->status === 'pending' || $apt->status === 'rescheduled')
                                                <button type="button" @click="approveAppointment({{ $apt->id }})" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] transition-all">Setujui</button>
                                                <button type="button" @click="openRescheduleModal({{ $apt->id }}, '{{ $apt->booking_code }}', '{{ addslashes($apt->student_name) }}', '{{ $apt->appointment_date }}')" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-[11px] transition-all">Reschedule</button>
                                                <button type="button" @click="openRejectModal({{ $apt->id }}, '{{ $apt->booking_code }}', '{{ addslashes($apt->student_name) }}')" class="px-2.5 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg font-bold text-[11px] transition-all">Tolak</button>
                                            @elseif($apt->status === 'approved')
                                                <button type="button" @click="completeAppointment({{ $apt->id }})" class="px-2.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-[11px] transition-all">Selesai</button>
                                            @endif
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">Tidak ada data janji bimbingan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>


    <!-- TAB 3: PENGATURAN PENGUMUMAN PORTAL PUBLIK -->
    <div x-show="activeTab === 'pengumuman'" class="space-y-6 animate-in fade-in duration-150 max-w-3xl">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="megaphone" class="w-5 h-5 text-brand-600"></i> Pengaturan Banner Pengumuman Registrasi
                </h2>
                <p class="text-xs text-slate-500">Tampilkan pesan atau instruksi penting kepada mahasiswa di halaman formulir pendaftaran bimbingan.</p>
            </div>

            <form action="{{ route('admin.announcement.update') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Status Tampilan Pengumuman</label>
                    <div class="grid grid-cols-2 gap-4 max-w-md">
                        <label class="p-3.5 rounded-xl border-2 cursor-pointer font-bold text-xs flex items-center justify-center gap-2 transition-all bg-emerald-50/40 border-emerald-400 text-emerald-900">
                            <input type="radio" name="is_active" value="1" {{ ($announcement->is_active ?? false) ? 'checked' : '' }} class="text-brand-600">
                            <span>🟢 Aktifkan Pengumuman</span>
                        </label>
                        <label class="p-3.5 rounded-xl border-2 cursor-pointer font-bold text-xs flex items-center justify-center gap-2 transition-all bg-slate-50 border-slate-200 text-slate-600">
                            <input type="radio" name="is_active" value="0" {{ !($announcement->is_active ?? false) ? 'checked' : '' }} class="text-brand-600">
                            <span>Sembunyikan</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="announcement_message" class="block text-xs font-bold text-slate-700 uppercase mb-1">Isi Pesan Pengumuman</label>
                    <textarea id="announcement_message" name="message" rows="4" 
                              placeholder="Contoh: Harap membawa draf fisik proposal skripsi 1 jam sebelum jam bimbingan..."
                              class="w-full p-3.5 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-brand-500">{{ old('message', $announcement->message ?? '') }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL OVERRIDE TANGGAL & JAM UN-AVAILABLE -->
    <div x-show="modalOpen" x-cloak 
         class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 flex items-center justify-center min-h-screen">
        
        <div @click.outside="modalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden flex flex-col max-h-[85vh] my-auto relative z-10">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between flex-shrink-0">
                <div>
                    <span class="text-[11px] font-extrabold text-brand-600 uppercase tracking-wider block">Setting Tanggal Khusus</span>
                    <h3 class="text-sm font-extrabold text-slate-900" x-text="formattedSelectedDate"></h3>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-200/60 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body Container -->
            <div class="p-6 space-y-6 overflow-y-auto flex-grow">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Status Layanan Bimbingan / Bertamu</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="isAvailable = true"
                                class="p-3 rounded-xl border-2 font-bold text-xs flex items-center justify-center gap-2 transition-all"
                                :class="isAvailable ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-sm' : 'border-slate-200 bg-white text-slate-500'">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Available / Buka
                        </button>
                        <button type="button" @click="isAvailable = false"
                                class="p-3 rounded-xl border-2 font-bold text-xs flex items-center justify-center gap-2 transition-all"
                                :class="!isAvailable ? 'border-rose-500 bg-rose-50 text-rose-800 shadow-sm' : 'border-slate-200 bg-white text-slate-500'">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span> Libur / Closed
                        </button>
                    </div>
                </div>

                <div>
                    <label for="modal_reason" class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Alasan Override</label>
                    <input type="text" id="modal_reason" x-model="reason" 
                           placeholder="Contoh: Rapat Senat / Tugas Dinas / Sesi Bertamu Khusus"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                </div>

                <div x-show="isAvailable" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Daftar Jam Unavailable / Jam Off</label>
                        <button type="button" @click="unavailableRanges.push({ start: '', end: '' })" 
                                class="px-2.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Jam Off
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500">Tentukan satu atau beberapa rentang jam ketika Anda berhalangan (misal: Rapat jam 08:30-09:15 dan Dinas jam 13:00-14:00). Semua slot bimbingan default mahasiswa yang beririsan otomatis diblokir.</p>

                    <div class="space-y-3 max-h-52 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-xl">
                        <template x-for="(range, index) in unavailableRanges" :key="index">
                            <div class="flex items-end gap-2 p-2 bg-white rounded-lg border border-slate-200 shadow-2xs">
                                <div class="grid grid-cols-2 gap-2 flex-grow">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Jam Mulai</label>
                                        <input type="time" x-model="range.start" required
                                               class="w-full px-2 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-brand-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Jam Selesai</label>
                                        <input type="time" x-model="range.end" required
                                               class="w-full px-2 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-brand-500">
                                    </div>
                                </div>
                                <button type="button" @click="unavailableRanges.splice(index, 1)" 
                                        class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition-colors" title="Hapus rentang ini">
                                    Hapus
                                </button>
                            </div>
                        </template>

                        <template x-if="unavailableRanges.length === 0">
                            <p class="text-center text-[11px] text-slate-400 py-4">Belum ada jam off khusus yang ditambahkan. Seluruh jam default harian aktif.</p>
                        </template>
                    </div>
                </div>

                <!-- SECTION: LIST MAHASISWA MENDAFTAR TANGGAL INI -->
                <div class="border-t border-slate-200 pt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase flex items-center gap-1.5">
                            <i data-lucide="users" class="w-4 h-4 text-brand-600"></i>
                            <span>Daftar Mahasiswa Mendaftar</span>
                            <span class="px-2 py-0.5 rounded-full bg-brand-100 text-brand-700 text-[11px] font-extrabold" x-text="dayAppointments.length + ' Mahasiswa'"></span>
                        </label>
                    </div>

                    <template x-if="dayAppointments.length > 0">
                        <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1" x-data="{ expandedAptId: null }">
                            <template x-for="apt in dayAppointments" :key="apt.id">
                                <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/80 hover:bg-white hover:border-brand-300 transition-all space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="overflow-hidden flex-grow">
                                            <div class="flex items-center gap-2">
                                                <span class="font-extrabold text-slate-900 text-xs truncate" x-text="apt.student_name"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 font-medium truncate mt-0.5">
                                                <span class="font-bold text-slate-800" x-text="apt.purpose"></span> &bull; <span class="text-brand-600 font-semibold" x-text="apt.time_slot + ' WIB'"></span>
                                            </p>
                                        </div>

                                        <!-- Tombol Lingkaran Huruf i Kecil yang Terlihat Jelas -->
                                        <button type="button" 
                                                @click="expandedAptId = (expandedAptId === apt.id ? null : apt.id)"
                                                @mouseenter="expandedAptId = apt.id"
                                                class="w-7 h-7 rounded-full bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center shadow-xs transition-all flex-shrink-0"
                                                title="Klik / Hover untuk melihat data mahasiswa lengkap">
                                            <span class="font-serif italic font-extrabold text-sm leading-none">i</span>
                                        </button>
                                    </div>

                                    <!-- Kartu Detail Informasi Mahasiswa (Inline Expandable - Bebas dari Isu Terpotong/Clipping) -->
                                    <div x-show="expandedAptId === apt.id" x-cloak 
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-98"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="p-3.5 bg-slate-900 text-white rounded-xl text-xs space-y-2 border border-slate-800 shadow-md">
                                        <div class="flex items-center justify-between border-b border-slate-700 pb-1.5">
                                            <span class="font-mono text-brand-400 font-bold text-[11px]" x-text="apt.booking_code"></span>
                                            <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 uppercase font-bold text-slate-300" x-text="apt.status"></span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                                            <div><span class="text-slate-400">NIM:</span> <strong class="text-white block font-semibold" x-text="apt.nim"></strong></div>
                                            <div><span class="text-slate-400">Program Studi:</span> <strong class="text-white block font-semibold" x-text="apt.department"></strong></div>
                                            <div class="col-span-2"><span class="text-slate-400">Email Mahasiswa:</span> <strong class="text-white block font-semibold" x-text="apt.student_email"></strong></div>
                                            <div class="col-span-2"><span class="text-slate-400">Waktu Bimbingan:</span> <strong class="text-brand-300 block font-semibold" x-text="apt.time_slot + ' WIB'"></strong></div>
                                        </div>
                                        <template x-if="apt.notes">
                                            <div class="bg-slate-800 p-2.5 rounded-lg text-[11px] text-slate-300 italic border border-slate-700">
                                                "<span x-text="apt.notes"></span>"
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="dayAppointments.length === 0">
                        <p class="text-center text-[11px] text-slate-400 py-3 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                            Belum ada mahasiswa yang mendaftar pada tanggal ini.
                        </p>
                    </template>
                </div>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3 flex-shrink-0">
                <template x-if="hasOverride">
                    <button type="button" @click="resetDayOverride()" class="px-4 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-xl text-xs font-bold transition-all">
                        Reset Ke Default
                    </button>
                </template>
                <template x-if="!hasOverride">
                    <div></div>
                </template>

                <div class="flex items-center gap-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-100 rounded-xl text-xs font-bold transition-all">
                        Batal
                    </button>
                    <button type="button" @click="saveDayOverride()" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Tanggal
                    </button>
                </div>
            </div>

        </div>
    </div>


    <!-- MODAL REJECT -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 flex items-center justify-center min-h-screen">
        <div @click.outside="rejectModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4 my-auto">
            <h3 class="text-base font-bold text-slate-900">Tolak Janji Bimbingan</h3>
            <p class="text-xs text-slate-500">Berikan alasan penolakan untuk mahasiswa <strong x-text="selectedName"></strong> (<span x-text="selectedCode"></span>).</p>
            
            <form @submit.prevent="submitReject()" class="space-y-4">
                <textarea x-model="rejectReason" required rows="3" placeholder="Alasan penolakan..." class="w-full p-3 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs">Tolak Janji</button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL RESCHEDULE (LENGKAP DENGAN PREVIEW KUOTA & AVAILABILITY LIVE) -->
    <div x-show="rescheduleModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 flex items-center justify-center min-h-screen">
        <div @click.outside="rescheduleModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4 my-auto">
            <h3 class="text-base font-bold text-slate-900">Usulkan Jadwal Ulang (Reschedule)</h3>
            <p class="text-xs text-slate-500">Ajukan tanggal & jam pengganti untuk <strong x-text="selectedName"></strong> (<span x-text="selectedCode"></span>).</p>
            
            <form @submit.prevent="submitReschedule()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Usulan Baru</label>
                    <input type="date" x-model="proposedDate" @change="fetchRescheduleSlots()" min="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Live Availability Preview & Slot Selector -->
                <div class="space-y-2 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <label class="block text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Pilihan Slot Jam Available</span>
                        <span x-show="loadingRescheduleSlots" class="text-[10px] text-brand-600 animate-pulse font-normal">Memuat ketersediaan...</span>
                    </label>

                    <template x-if="rescheduleDateBlocked">
                        <div class="p-2.5 rounded-lg bg-rose-100 text-rose-800 text-[11px] font-bold">
                            ⚠️ Tanggal ini Tutup / Libur (<span x-text="rescheduleBlockedReason"></span>)
                        </div>
                    </template>

                    <template x-if="!rescheduleDateBlocked && rescheduleSlots.length > 0">
                        <select x-model="proposedSlot" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold bg-white focus:ring-2 focus:ring-indigo-500">
                            <template x-for="slot in rescheduleSlots" :key="slot.time_slot">
                                <option :value="slot.time_slot" :disabled="!slot.is_available"
                                        x-text="`${slot.time_slot} WIB — ${slot.is_available ? `Sisa ${slot.remaining} Kuota` : (slot.disabled_reason || 'Kuota Penuh')}`">
                                </option>
                            </template>
                        </select>
                    </template>

                    <template x-if="!rescheduleDateBlocked && rescheduleSlots.length === 0 && !loadingRescheduleSlots">
                        <div class="space-y-1">
                            <input type="text" x-model="proposedSlot" required placeholder="Contoh: 10:00 - 11:00" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold">
                            <p class="text-[10px] text-slate-400">Ketik slot jam manual jika tidak ada opsi dropdown.</p>
                        </div>
                    </template>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Perubahan Jadwal</label>
                    <textarea x-model="rescheduleReason" required rows="2" placeholder="Catatan untuk mahasiswa..." class="w-full p-3 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="rescheduleModalOpen = false" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold hover:bg-slate-100">Batal</button>
                    <button type="submit" :disabled="rescheduleDateBlocked" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-xs">Kirim Usulan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
