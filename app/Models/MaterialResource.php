<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'material_id',
        'path',
        'type',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
