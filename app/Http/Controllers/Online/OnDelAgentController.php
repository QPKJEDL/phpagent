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
            $map['username']=$request->input('username');
        }
        $sql = User::query()->where($map);
        $sql->where('ancestors','like','%'.Auth::id().'%');
        if(true==$request->has('nickname')){
            $sql->where('nickname','like','%'.$request->input('nickname').'%');
        }
        //$sql = 'select * from  hq_agent_users where parent_id = '.Auth::id().' or parent_id IN (select t1.* from hq_agent_users t1 where FIND_IN_SET('.Auth::id().',ancestors))';
        $data = $sql->paginate(10)->appends($request->all());
        //$data = DB::table(DB::raw($sql))->paginate(10)->appends($request->all());
        return view('onAgent.delAgent.list',['list'=>$data,'input'=>$request->all()]);
    }
}