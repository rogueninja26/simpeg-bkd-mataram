@extends('layouts.app')

@section('title', 'Dashboard & Bezetting SDM - SIMPEG BKD Kota Mataram')

@section('content')
<div class="flex flex-col w-full pb-space-2xl space-y-space-lg">
    
    <!-- Header Banner -->
    <div class="w-full bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-space-md">
        <div class="space-y-1">
            <div class="flex items-center gap-space-sm">
                <span class="px-2 py-0.5 rounded-full bg-primary-container text-on-primary-container font-label-sm text-[11px] uppercase tracking-wider font-semibold">Ringkasan Eksekutif</span>
                <span class="flex items-center gap-1 font-label-sm text-secondary font-semibold text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    Data Real-time PostgreSQL
                </span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">Dashboard &amp; Bezetting SDM BKD</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Pemetaan komposisi kekuatan pegawai, sebaran golongan, serta status kelengkapan e-File aparatur Badan Keuangan Daerah Kota Mataram.
            </p>
        </div>

        @if(in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian', 'Pimpinan']))
        <div class="flex items-center gap-space-sm">
            <a href="{{ route('pegawai.index') }}" class="flex items-center gap-space-xs px-space-md py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-lg text-label-lg transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">badge</span>
                <span>Kelola Data Pegawai</span>
            </a>
        </div>
        @endif
    </div>

    <!-- 4 Kartu KPI Utama (Komposisi Status Kepegawaian) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">
        <!-- Total Pegawai -->
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm flex flex-col justify-between space-y-space-sm border-l-4 border-primary">
            <div class="flex items-center justify-between">
                <span class="font-label-sm text-on-surface-variant uppercase">Total Aparatur Aktif</span>
                <div class="w-9 h-9 rounded-lg bg-primary-fixed text-on-primary-fixed flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                </div>
            </div>
            <div>
                <div class="font-headline-xl text-headline-xl text-primary font-tabular-data">{{ $stats['total'] ?? 0 }}</div>
                <span class="font-body-sm text-[12px] text-on-surface-variant">Seluruh status kepegawaian BKD</span>
            </div>
        </div>

        <!-- PNS / CPNS -->
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm flex flex-col justify-between space-y-space-sm border-l-4 border-secondary">
            <div class="flex items-center justify-between">
                <span class="font-label-sm text-on-surface-variant uppercase">PNS &amp; CPNS</span>
                <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">verified_user</span>
                </div>
            </div>
            <div>
                <div class="font-headline-xl text-headline-xl text-secondary font-tabular-data">{{ $stats['pns'] ?? 0 }}</div>
                <span class="font-body-sm text-[12px] text-on-surface-variant">Aparatur Sipil Negara Tetap</span>
            </div>
        </div>

        <!-- PPPK (Penuh & Paruh Waktu) -->
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm flex flex-col justify-between space-y-space-sm border-l-4 border-surface-tint">
            <div class="flex items-center justify-between">
                <span class="font-label-sm text-on-surface-variant uppercase">PPPK (Penuh &amp; Paruh)</span>
                <div class="w-9 h-9 rounded-lg bg-surface-container-highest text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">assignment_ind</span>
                </div>
            </div>
            <div>
                <div class="font-headline-xl text-headline-xl text-primary font-tabular-data">{{ $stats['pppk'] ?? 0 }}</div>
                <span class="font-body-sm text-[12px] text-on-surface-variant">Pegawai Pemerintah Perjanjian Kerja</span>
            </div>
        </div>

        <!-- Tenaga Kontrak / Non-ASN -->
        <div class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm flex flex-col justify-between space-y-space-sm border-l-4 border-on-tertiary-container">
            <div class="flex items-center justify-between">
                <span class="font-label-sm text-on-surface-variant uppercase">Tenaga Kontrak / PJLP</span>
                <div class="w-9 h-9 rounded-lg bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">engineering</span>
                </div>
            </div>
            <div>
                <div class="font-headline-xl text-headline-xl text-tertiary font-tabular-data">{{ $stats['kontrak'] ?? 0 }}</div>
                <span class="font-body-sm text-[12px] text-on-surface-variant">Tenaga Pendukung Operasional</span>
            </div>
        </div>
    </div>

    <!-- Tabel Matriks Bezetting Per Bidang / Unit Kerja -->
    <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm space-y-space-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-headline-sm text-headline-sm text-primary">Matriks Bezetting SDM per Bidang / Subbagian</h3>
                <p class="font-body-sm text-on-surface-variant">Perbandingan kebutuhan formasi (ABK) dengan kondisi riil pegawai saat ini.</p>
            </div>
            <span class="px-3 py-1 rounded-lg bg-surface-container-low font-label-sm text-primary">Tahun Anggaran {{ date('Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-label-sm uppercase">
                        <th class="py-3 px-4 rounded-tl-lg">Unit Kerja / Bidang</th>
                        <th class="py-3 px-4 text-center">PNS</th>
                        <th class="py-3 px-4 text-center">PPPK</th>
                        <th class="py-3 px-4 text-center">Non-ASN</th>
                        <th class="py-3 px-4 text-center">Total Riil</th>
                        <th class="py-3 px-4 text-center rounded-tr-lg">Status Formasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container text-body-md">
                    @forelse($bezettingUnit ?? [] as $unit)
                    <tr class="hover:bg-surface-container-low/50 transition-colors">
                        <td class="py-3 px-4 font-semibold text-primary">{{ $unit['nama'] }}</td>
                        <td class="py-3 px-4 text-center font-tabular-data">{{ $unit['pns'] }}</td>
                        <td class="py-3 px-4 text-center font-tabular-data">{{ $unit['pppk'] }}</td>
                        <td class="py-3 px-4 text-center font-tabular-data">{{ $unit['kontrak'] }}</td>
                        <td class="py-3 px-4 text-center font-tabular-data font-bold text-primary">{{ $unit['total'] }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px]">
                                Terpenuhi
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-on-surface-variant font-body-sm">
                            Belum ada data agregasi unit kerja. Pastikan tabel pegawai sudah terisi dari proses seeding.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection