<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

class QrcodeController extends Controller
{
    public function codeEdit($id)
    {
        return view('qrcode.qrocde',['id'=>$id]);
    }
}