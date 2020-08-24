<?php


namespace App\Http\Controllers\Online;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            $map['username']=HttpFilter($request->input('username'));
        }
        $sql = User::query()->where($map);
        $sql->where('ancestors','like','%'.Auth::id().'%');
        if(true==$request->has('nickname')){
            $sql->where('nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->paginate($limit)->appends($request->all());
        return view('onAgent.delAgent.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
}