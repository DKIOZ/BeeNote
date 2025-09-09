<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    /** @use HasFactory<\Database\Factories\PhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'chemin',
        'titre',
        'description',
        'taille_fichier',
        'mime_type',
        'ordre',
    ];

    public function photoable()
    {
        return $this->morphTo();
    }
}
