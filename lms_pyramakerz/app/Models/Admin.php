<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected $guarded = [];
    protected $guard_name = 'web';
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function hasRole($roles)
    {
        return true; 
    }

    public function hasPermissionTo($permission, $guardName = null)
    {
        return true; 
    }
}
