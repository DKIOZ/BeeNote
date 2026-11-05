<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RucheEmplacement extends Model
{
    use SoftDeletes;

    protected $fillable = ['date_deplacement', 'notes'];

    protected function casts(): array
    {
        return ['date_deplacement' => 'date:Y-m-d'];
    }

    public function ruche()
    {
        return $this->belongsTo(Ruche::class);
    }

    public function depuis()
    {
        return $this->belongsTo(Rucher::class, 'depuis_rucher_id')->withTrashed();
    }

    public function vers()
    {
        return $this->belongsTo(Rucher::class, 'vers_rucher_id')->withTrashed();
    }
}
