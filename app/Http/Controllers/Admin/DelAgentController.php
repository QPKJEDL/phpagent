<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DelAgentController extends Controller
{
    public function index(Request $request)
    {
        $map =array();
        $map['del_flag']=1;
        $sql=User::query();
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->where($map)->whereRaw('FIND_IN_SET('.Auth::id().',ancestors)',true)->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]['fee']=json_decode($datum['fee'],true);
        }
        return view('delAgent.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
}