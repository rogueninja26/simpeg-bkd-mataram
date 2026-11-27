<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanCutiIzin extends Model
{
    protected $table = 'pengajuan_cuti_izin';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id',
        'atasan_id',
        'jenis_cuti_izin',
        'tgl_mulai',
        'tgl_selesai',
        'jumlah_hari_kerja',
        'alasan',
        'alamat_selama_cuti',
        'lampiran_bukti_path',
        'status_approval',
        'catatan_penolakan',
        'nomor_surat',
        'file_surat_tte_path',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'jumlah_hari_kerja' => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function atasan()
    {
        return $this->belongsTo(Pegawai::class, 'atasan_id');
    }

    // Helper label untuk UI Tailwind
    public function getLabelJenisAttribute()
    {
        return str_replace('_', ' ', $this->jenis_cuti_izin);
    }
}