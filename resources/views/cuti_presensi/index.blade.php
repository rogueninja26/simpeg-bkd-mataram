@extends('layouts.app')

@section('title', 'Cuti, Izin & Presensi - SIMPEG BKD Kota Mataram')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Cuti, Izin & Rekap Presensi</h1>
            <p class="text-sm text-slate-500">Pengajuan cuti mandiri pegawai, persetujuan pimpinan, dan sinkronisasi presensi bulanan BKD Kota Mataram.</p>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Navigasi Tab -->
    <div class="border-b border-slate-200 mb-6">
        <nav class="-mb-px flex space-x-6" id="tabNav">
            <button type="button" onclick="switchTab('tab-cuti')" id="btn-tab-cuti"
                class="tab-btn border-b-2 border-blue-600 py-3 px-1 text-sm font-semibold text-blue-600">
                Pengajuan & Approval Cuti/Izin
            </button>
            <button type="button" onclick="switchTab('tab-presensi')" id="btn-tab-presensi"
                class="tab-btn border-b-2 border-transparent py-3 px-1 text-sm font-medium text-slate-500 hover:text-slate-700">
                Rekap Presensi Bulanan
            </button>
        </nav>
    </div>

    <!-- TAB 1: PENGAJUAN & APPROVAL CUTI/IZIN -->
    <div id="tab-cuti" class="tab-content grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Pengajuan Mandiri -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 h-fit">
            <h2 class="text-base font-bold text-slate-800 mb-4 border-b pb-2">Form Pengajuan Mandiri</h2>
            <form action="{{ route('cuti-presensi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Cuti / Izin</label>
                    <select name="jenis_cuti_izin" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Cuti_Tahunan">Cuti Tahunan</option>
                        <option value="Cuti_Sakit">Cuti Sakit</option>
                        <option value="Cuti_Melahirkan">Cuti Melahirkan</option>
                        <option value="Cuti_Alasan_Penting">Cuti Alasan Penting</option>
                        <option value="Cuti_Besar">Cuti Besar</option>
                        <option value="Izin_Kontrak">Izin Kontrak (PPPK Paruh / Non-ASN)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Atasan Langsung (Pejabat Penilai)</label>
                    <select name="atasan_id" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Atasan Langsung --</option>
                        @foreach($daftarAtasan as $atasan)
                            <option value="{{ $atasan->id }}">
                                {{ $atasan->nama_lengkap }} ({{ $atasan->jabatan->nama_jabatan ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" required class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Cuti / Izin</label>
                    <textarea name="alasan" rows="3" required placeholder="Tuliskan keperluan cuti/izin..."
                        class="w-full rounded-lg border-slate-300 text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Selama Cuti</label>
                    <input type="text" name="alamat_selama_cuti" placeholder="Contoh: Jl. Pejanggik, Mataram"
                        class="w-full rounded-lg border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Bukti (Surat Dokter/Pendukung)</label>
                    <input type="file" name="lampiran_bukti" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition">
                    Kirim Pengajuan
                </button>
            </form>
        </div>

        <!-- Daftar Pengajuan & Panel Approval -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-base font-bold text-slate-800 mb-4 border-b pb-2">Riwayat & Antrean Persetujuan Cuti</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2.5 text-left font-semibold text-slate-600">Pegawai</th>
                            <th class="px-3 py-2.5 text-left font-semibold text-slate-600">Jenis & Tanggal</th>
                            <th class="px-3 py-2.5 text-center font-semibold text-slate-600">Status</th>
                            <th class="px-3 py-2.5 text-right font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftarCuti as $item)
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="font-semibold text-slate-800">{{ $item->pegawai->nama_lengkap }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->pegawai->nomor_induk }}</div>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="font-medium text-blue-700">{{ $item->label_jenis }}</span>
                                    <div class="text-xs text-slate-600">
                                        {{ $item->tgl_mulai->format('d/m/Y') }} - {{ $item->tgl_selesai->format('d/m/Y') }}
                                        ({{ $item->jumlah_hari_kerja }} hari kerja)
                                    </div>
                                    <div class="text-xs text-slate-400 italic mt-0.5">"{{ \Illuminate\Support\Str::limit($item->alasan, 45) }}"</div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @php
                                        $badge = [
                                            'Diajukan' => 'bg-amber-100 text-amber-800',
                                            'Disetujui_Atasan' => 'bg-sky-100 text-sky-800',
                                            'Disetujui_Kaban' => 'bg-emerald-100 text-emerald-800',
                                            'Ditolak' => 'bg-rose-100 text-rose-800',
                                        ][$item->status_approval] ?? 'bg-slate-100 text-slate-800';
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ str_replace('_', ' ', $item->status_approval) }}
                                    </span>
                                    @if($item->nomor_surat)
                                        <div class="text-[11px] text-slate-500 mt-1">No: {{ $item->nomor_surat }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right">
                                    @if(in_array($roleName, ['Superadmin', 'Admin_Kepegawaian', 'Operator', 'Pimpinan', 'Kaban']) && !in_array($item->status_approval, ['Disetujui_Kaban', 'Ditolak']))
                                        <button type="button"
                                            onclick="openModalApproval({{ $item->id }}, '{{ $item->pegawai->nama_lengkap }}', '{{ $item->status_approval }}')"
                                            class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-3 py-1.5 rounded-md font-medium">
                                            Proses
                                        </button>
                                    @elseif($item->file_surat_tte_path)
                                        <a href="{{ asset('storage/' . $item->file_surat_tte_path) }}" target="_blank"
                                            class="text-xs text-blue-600 hover:underline font-medium">
                                            Unduh SK Cuti
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-slate-400">Belum ada data pengajuan cuti/izin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $daftarCuti->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: REKAP PRESENSI BULANAN -->
    <div id="tab-presensi" class="tab-content hidden bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5 border-b pb-4">
            <div>
                <h2 class="text-base font-bold text-slate-800">Rekapitulasi Kehadiran Bulanan</h2>
                <p class="text-xs text-slate-500">Data otomatis terhubung dengan cuti berstatus <strong>Disetujui Kaban</strong> sebagai dasar perhitungan TPP/Penghasilan.</p>
            </div>
            <form method="GET" action="{{ route('cuti-presensi.index') }}" class="flex items-center gap-2">
                <select name="bulan" class="rounded-lg border-slate-300 text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <input type="number" name="tahun" value="{{ $tahun }}" class="w-24 rounded-lg border-slate-300 text-sm">
                <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2.5 text-left font-semibold text-slate-600">Nama Pegawai</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-slate-600">Wajib Kerja</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-slate-600">Hadir</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-slate-600">Cuti / Izin</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-slate-600">Dinas Luar</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-slate-600">Terlambat (Mnt)</th>
                        <th class="px-3 py-2.5 text-center font-semibold text-slate-600">Alpa</th>
                        <th class="px-3 py-2.5 text-right font-semibold text-slate-600">Disiplin (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekapPresensi as $rekap)
                        <tr>
                            <td class="px-3 py-3">
                                <div class="font-semibold text-slate-800">{{ $rekap->pegawai->nama_lengkap }}</div>
                                <div class="text-xs text-slate-500">{{ $rekap->pegawai->unitKerja->nama_unit ?? '-' }}</div>
                            </td>
                            <td class="px-3 py-3 text-center">{{ $rekap->wajib_hari_kerja }}</td>
                            <td class="px-3 py-3 text-center font-medium text-emerald-700">{{ $rekap->jumlah_hadir }}</td>
                            <td class="px-3 py-3 text-center font-semibold text-blue-700">{{ $rekap->jumlah_cuti_izin }}</td>
                            <td class="px-3 py-3 text-center">{{ $rekap->jumlah_dinas_luar }}</td>
                            <td class="px-3 py-3 text-center text-amber-600">{{ $rekap->total_terlambat_menit }}</td>
                            <td class="px-3 py-3 text-center text-rose-600 font-semibold">{{ $rekap->tanpa_keterangan }}</td>
                            <td class="px-3 py-3 text-right font-bold text-slate-800">{{ number_format($rekap->persentase_disiplin, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-400">Belum ada rekap presensi untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Approval -->
<div id="modalApproval" class="fixed inset-0 z-50 hidden bg-slate-900/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-800 mb-1">Persetujuan Cuti / Izin</h3>
        <p id="modalPegawaiNama" class="text-xs text-slate-500 mb-4"></p>

        <form id="formApproval" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keputusan Persetujuan</label>
                <select name="status_approval" id="selectStatusApproval" required onchange="toggleAlasanTolak()"
                    class="w-full rounded-lg border-slate-300 text-sm">
                    <option value="Disetujui_Atasan">Setujui Tahap 1 (Disetujui Atasan)</option>
                    <option value="Disetujui_Kaban">Setujui Final (Disetujui Kaban & Sinkron Presensi)</option>
                    <option value="Ditolak">Tolak Pengajuan</option>
                </select>
            </div>

            <div id="boxNomorSurat">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat Cuti (Opsional / Final)</label>
                <input type="text" name="nomor_surat" placeholder="Contoh: 850/124/BKD/2026"
                    class="w-full rounded-lg border-slate-300 text-sm">
            </div>

            <div id="boxSuratTte">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Surat Cuti TTE (PDF)</label>
                <input type="file" name="file_surat_tte" accept=".pdf" class="w-full text-xs text-slate-500">
            </div>

            <div id="boxCatatanTolak" class="hidden">
                <label class="block text-xs font-semibold text-rose-700 mb-1">Catatan Penolakan (Wajib diisi jika ditolak)</label>
                <textarea name="catatan_penolakan" rows="2" class="w-full rounded-lg border-rose-300 text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModalApproval()" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    Simpan Keputusan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById(tabId).classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-600', 'text-blue-600', 'font-semibold');
            btn.classList.add('border-transparent', 'text-slate-500', 'font-medium');
        });
        const activeBtn = document.getElementById('btn-' + tabId);
        activeBtn.classList.add('border-blue-600', 'text-blue-600', 'font-semibold');
        activeBtn.classList.remove('border-transparent', 'text-slate-500', 'font-medium');
    }

    function openModalApproval(id, namaPegawai, currentStatus) {
        document.getElementById('modalPegawaiNama').innerText = 'Pegawai: ' + namaPegawai + ' (Status saat ini: ' + currentStatus.replace('_', ' ') + ')';
        document.getElementById('formApproval').action = '/cuti-presensi/approval/' + id;
        document.getElementById('modalApproval').classList.remove('hidden');
    }

    function closeModalApproval() {
        document.getElementById('modalApproval').classList.add('hidden');
    }

    function toggleAlasanTolak() {
        const val = document.getElementById('selectStatusApproval').value;
        document.getElementById('boxCatatanTolak').classList.toggle('hidden', val !== 'Ditolak');
        document.getElementById('boxNomorSurat').classList.toggle('hidden', val === 'Ditolak');
        document.getElementById('boxSuratTte').classList.toggle('hidden', val === 'Ditolak');
    }
</script>
@endsection