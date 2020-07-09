<?php


namespace App\Http\Controllers\Online;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * 线上代理 已删代理
 * Class OnDelAgentController
 * @package App\Http\Controllers\Online
 */
class OnDelAgentController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $map['del_flag'] = 1;
        if (true==$request->has('username')){
            $map['username']=$request->input('username');
        }
        $sql = User::query()->where($map);
        if(true==$request->has('nickname')){
            $sql->where('nickname','like','%'.$request->input('nickname').'%');
        }
        $data = $sql->paginate(10)->appends($request->all());
        return view('onAgent.delAgent.list',['list'=>$data,'input'=>$request->all()]);
    }
}