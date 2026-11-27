<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RiwayatKontrakKerja extends Model
{
    protected $table = 'riwayat_kontrak_kerja';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id',
        'jenis_kontrak',
        'golongan_pppk_id',
        'nomor_sk_spk',
        'tgl_penetapan',
        'tgl_mulai_kontrak',
        'tgl_akhir_kontrak',
        'gaji_atau_honor_pokok',
        'status_evaluasi',
        'file_kontrak_path',
        'is_aktif',
    ];

    protected $casts = [
        'tgl_penetapan'         => 'date',
        'tgl_mulai_kontrak'     => 'date',
        'tgl_akhir_kontrak'     => 'date',
        'gaji_atau_honor_pokok' => 'decimal:2',
        'is_aktif'              => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function golonganPppk()
    {
        return $this->belongsTo(MasterGolongan::class, 'golongan_pppk_id');
    }

    public function scopeAkanHabis($query, int $days = 90)
    {
        $limit = Carbon::today()->addDays($days);

        return $query->where('is_aktif', true)
                     ->whereIn('status_evaluasi', ['Berjalan', 'Direkomendasikan_Perpanjang'])
                     ->whereNotNull('tgl_akhir_kontrak')
                     ->whereDate('tgl_akhir_kontrak', '<=', $limit);
    }
}