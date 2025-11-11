<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservationTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function headers()
    {
        return $this->hasMany(ObservationHeader::class);
    }
    public function observations()
    {
        return $this->hasMany(Observation::class);
    }
}
