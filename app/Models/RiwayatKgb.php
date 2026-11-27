<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RiwayatKgb extends Model
{
    protected $table = 'riwayat_kgb';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id',
        'gaji_pokok_lama',
        'gaji_pokok_baru',
        'mkg_tahun',
        'mkg_bulan',
        'tmt_kgb',
        'tmt_kgb_berikutnya',
        'nomor_sk',
        'tgl_sk',
        'file_sk_path',
        'is_aktif',
    ];

    protected $casts = [
        'tgl_sk'             => 'date',
        'tmt_kgb'            => 'date',
        'tmt_kgb_berikutnya' => 'date',
        'gaji_pokok_lama'    => 'decimal:2',
        'gaji_pokok_baru'    => 'decimal:2',
        'is_aktif'           => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function scopeJatuhTempo($query, int $days = 60)
    {
        $limit = Carbon::today()->addDays($days);

        return $query->where('is_aktif', true)
                     ->whereNotNull('tmt_kgb_berikutnya')
                     ->whereDate('tmt_kgb_berikutnya', '<=', $limit);
    }
}