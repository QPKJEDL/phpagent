<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HqUser extends Model
{
    protected $table = 'user';
    protected $hidden = ['password'];
    protected $primaryKey = 'user_id';
    public $timestamps = false;
}