<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservationHeader extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function questions()
    {
        return $this->hasMany(ObservationQuestion::class, 'observation_header_id');
    }

    public function template()
    {
        return $this->belongsTo(ObservationTemplate::class);
    }
}
