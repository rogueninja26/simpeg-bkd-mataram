<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenEfile extends Model
{
    protected $table = 'dokumen_efile';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}