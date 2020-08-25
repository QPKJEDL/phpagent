<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    public function userInfo()
    {
        if (empty(Auth::user()))
        {
            return ['msg'=>'没有权限','status'=>0];
        }
        return view('users.userinfo',['userinfo'=>Auth::user()]);
    }
}