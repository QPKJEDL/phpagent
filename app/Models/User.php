<?php

namespace App\Models;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Models\Interfaces\AdminUsersInterface;
use App\Models\Traits\AdminUsersTrait;
class User extends Model implements AuthenticatableContract, CanResetPasswordContract, AdminUsersInterface
{
    use Authenticatable, CanResetPassword, AdminUsersTrait;
    protected $table = 'user';
    protected $fillable = ['username', 'email', 'mobile', 'password'];
    protected $hidden = ['password', 'remember_token','token'];
    protected $userInfo;

    public static function getUserInfo($account){
        $user =User::where('username','=',$account)->first();
        return $user;
    }
}
