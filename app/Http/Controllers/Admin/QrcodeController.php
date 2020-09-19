<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QrcodeController extends Controller
{
    public function index(Request $request)
    {
        return view('qrcode.list',['id'=>Auth::id(),'url'=>config('admin.register').'?aid='.Auth::id()]);
    }
}