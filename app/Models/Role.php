<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = ['nama'];

    /** Sebutan peran pada antarmuka, mis. "Petugas TU". */
    public function getSebutanAttribute(): string
    {
        return User::PERAN[$this->nama] ?? Str::headline($this->nama);
    }
}
