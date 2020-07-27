<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    protected $table = 'user_account';

    public static function getUserAccountInfo($userId){
        return UserAccount::where('user_id','=',$userId)->lockForUpdate()->first();
    }
}