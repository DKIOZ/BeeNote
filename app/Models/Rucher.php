<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rucher extends Model
{
    /** @use HasFactory<\Database\Factories\RucherFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'localisation',
        'latitude',
        'longitude',
        'description',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function ruches()
    {
        return $this->hasMany(Ruche::class);
    }
}
