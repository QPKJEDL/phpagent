<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * 已删代理
 * Class RemoveAgentController
 * @package App\Http\Controllers\Admin
 */
class RemoveAgentController extends Controller
{
    public function index(Request $request){
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
        foreach ($data as $key=>$value){
            $data[$key]['fee']=json_decode($value['fee'],true);
        }
        return view('removeAgent.list',['list'=>$data,'input'=>$request->all()]);
    }
}