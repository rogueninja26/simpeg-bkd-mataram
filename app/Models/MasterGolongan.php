<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterGolongan extends Model
{
    protected $table = 'master_golongan';
    protected $guarded = ['id'];
    public $timestamps = false;
}