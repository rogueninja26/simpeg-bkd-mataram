<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(MasterUnitKerja::class, 'unit_kerja_aktif_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(MasterJabatan::class, 'jabatan_aktif_id');
    }

    public function polaKerja()
    {
        return $this->belongsTo(MasterPolaKerja::class, 'pola_kerja_id');
    }

    public function dokumenEfile()
    {
        return $this->hasMany(DokumenEfile::class, 'pegawai_id');
    }

    // === TAMBAHAN RELASI TAHAP 2 (KONTRAK, KGB & KP) ===

    // === RELASI TAHAP 2 (KONTRAK, KGB & KP) ===

    public function kontrakKerja()
    {
        return $this->hasMany(RiwayatKontrakKerja::class, 'pegawai_id')
                    ->orderBy('tgl_mulai_kontrak', 'desc');
    }

    public function kontrakAktif()
    {
        return $this->hasOne(RiwayatKontrakKerja::class, 'pegawai_id')
                    ->where('is_aktif', true)
                    ->latest('tgl_akhir_kontrak');
    }

    public function riwayatKgb()
    {
        return $this->hasMany(RiwayatKgb::class, 'pegawai_id')
                    ->orderBy('tmt_kgb', 'desc');
    }

    public function kgbTerakhir()
    {
        return $this->hasOne(RiwayatKgb::class, 'pegawai_id')
                    ->where('is_aktif', true);
    }

    public function riwayatPangkat()
    {
        return $this->hasMany(RiwayatPangkat::class, 'pegawai_id')
                    ->orderBy('tmt_pangkat', 'desc');
    }

    public function pangkatTerakhir()
    {
        return $this->hasOne(RiwayatPangkat::class, 'pegawai_id')
                    ->where('is_aktif', true);
    }
}