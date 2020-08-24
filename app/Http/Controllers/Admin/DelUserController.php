<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Czrecord;
use App\Models\HqUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelUserController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $map['user.del_flag']=1;
        $childrenDate = User::whereRaw('FIND_IN_SET('.Auth::id().',ancestors)',true)->select('id')->get();
        $agentIdMap = array();
        $agentIdMap[]=Auth::id();
        foreach ($childrenDate as $key=>$value)
        {
            $agentIdMap[] = $value['id'];
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        //$data = HqUser::query()->whereIn('agent_id',$agentIdMap)->where(['del_flag'=>1])->paginate($limit)->appends($request->all());
        $sql = HqUser::query();
        $sql->leftJoin('user_account','user_account.user_id','=','user.user_id')
            ->select('user.*','user_account.balance')->whereIn('user.agent_id',$agentIdMap);
        if (true==$request->has('nickname'))
        {
            $sql->where('user.nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        $data = $sql->where($map)->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]['fee']=json_decode($datum['fee'],true);
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['creatime'] = date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('delUser.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
    /**
     * 获取用户最近充值记录
     */
    public function getUserCzCord($userId)
    {
        $data = Czrecord::where('user_id',$userId)->orderBy('creatime','desc')->first();
        return $data;
    }
}