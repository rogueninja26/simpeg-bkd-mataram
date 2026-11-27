<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $casts = [
        'data_lama_json' => 'array',
        'data_baru_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}