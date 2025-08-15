<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rucher extends Model
{
    /** @use HasFactory<\Database\Factories\RucherFactory> */
    use HasFactory;

    protected $fillable = [
        'nom',           // ✅ L'utilisateur peut le définir
        'localisation',  // ✅ L'utilisateur peut le définir  
        'latitude',      // ✅ L'utilisateur peut le définir
        'longitude',     // ✅ L'utilisateur peut le définir
        'description',   // ✅ L'utilisateur peut le définir
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ruches() {
        return $this->hasMany(Ruche::class);
    }
}
