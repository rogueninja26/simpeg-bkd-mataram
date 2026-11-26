<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'username_login',
        'password',
        'is_active',
        'last_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
    ];

    // Relasi ke tabel roles
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relasi ke tabel pegawai
    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'user_id');
    }

    // Helper untuk mengecek nama role dengan mudah
    public function hasRole($roleName)
    {
        return $this->role && $this->role->nama_role === $roleName;
    }
}