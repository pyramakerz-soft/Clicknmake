<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialSchool extends Model
{
    use HasFactory;

    protected $table = 'material_school';
    protected $guarded = [];
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
