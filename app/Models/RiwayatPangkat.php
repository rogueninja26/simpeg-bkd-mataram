<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RiwayatPangkat extends Model
{
    protected $table = 'riwayat_pangkat';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id',
        'golongan_id',
        'tmt_pangkat',
        'proyeksi_kp_berikutnya',
        'nomor_sk',
        'tgl_sk',
        'file_sk_path',
        'is_aktif',
    ];

    protected $casts = [
        'tgl_sk'                 => 'date',
        'tmt_pangkat'            => 'date',
        'proyeksi_kp_berikutnya' => 'date',
        'is_aktif'               => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function golongan()
    {
        return $this->belongsTo(MasterGolongan::class, 'golongan_id');
    }

   public function scopeMendekatiProyeksi($query, int $days = 90)
    {
        $limit = Carbon::today()->addDays($days);

        return $query->where('is_aktif', true)
                     ->whereNotNull('proyeksi_kp_berikutnya')
                     ->whereDate('proyeksi_kp_berikutnya', '<=', $limit);
    }
}