<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapPresensiBulanan extends Model
{
    protected $table = 'rekap_presensi_bulanan';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id',
        'bulan',
        'tahun',
        'wajib_hari_kerja',
        'jumlah_hadir',
        'jumlah_cuti_izin',
        'jumlah_dinas_luar',
        'total_terlambat_menit',
        'tanpa_keterangan',
        'persentase_disiplin',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'wajib_hari_kerja' => 'integer',
        'jumlah_hadir' => 'integer',
        'jumlah_cuti_izin' => 'integer',
        'jumlah_dinas_luar' => 'integer',
        'total_terlambat_menit' => 'integer',
        'tanpa_keterangan' => 'integer',
        'persentase_disiplin' => 'decimal:2',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}