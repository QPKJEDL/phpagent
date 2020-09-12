<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentBill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 代理充值
 * Class AgentCzController
 * @package App\Http\Controllers\Admin
 */
class AgentCzController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $sql = AgentBill::query();
        $sql->leftJoin('agent_users','agent_users.id','=','agent_billflow.agent_id')
            ->leftJoin('user','user.user_id','=','agent_billflow.user_id')
            ->select('agent_users.username','user.account','agent_billflow.money','agent_billflow.user_id','agent_billflow.agent_name','agent_billflow.user_name','agent_billflow.bet_before','agent_billflow.bet_after','agent_billflow.status','agent_billflow.type','agent_billflow.remark','agent_billflow.creatime');
        if (true==$request->has('begin'))
        {
            $begin = strtotime($request->input('begin'))+config('admin.beginTime');
        }else{
            $begin = strtotime(date('Y-m-d',time())) + config('admin.beginTime');
            $request->offsetSet('begin',date('Y-m-d',time()));
        }
        if (true==$request->has('end'))
        {
            $end = strtotime('+1day',strtotime($request->input('end')))+config('admin.beginTime');
        }
        else
        {
            $end = strtotime('+1day',strtotime(date('Y-m-d',time())))+config('admin.beginTime');
        }
        $sql->whereBetween('agent_billflow.creatime',[$begin,$end]);
        if (true==$request->has('username'))
        {
            $agent = User::query()->where('username','=',HttpFilter($request->input('username')))->first();
            $sql->where('agent_billflow.agent_id','=',$agent['id']);
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->where($map)->where('agent_users.id','=',Auth::id())->orWhere('agent_users.parent_id','=',Auth::id())->orderBy('agent_billflow.creatime','desc')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$value)
        {
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('agentCz.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }

    public function getRecordById($id,Request $request)
    {
        $map = array();
        $agentInfo = (int)$id?User::find((int)$id):[];
        $request->offsetSet('username',$agentInfo['username']);
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'您没有权限','status'=>0];
        }
        if (true==$request->has('begin'))
        {
            $begin = strtotime($request->input('begin'))+config('admin.beginTime');
        }else{
            $begin = strtotime(date('Y-m-d',time())) + config('admin.beginTime');
            $request->offsetSet('begin',date('Y-m-d',time()));
        }
        if (true==$request->has('end'))
        {
            $end = strtotime('+1day',strtotime($request->input('end')))+config('admin.beginTime');
        }
        else
        {
            $end = strtotime('+1day',strtotime(date('Y-m-d',time())))+config('admin.beginTime');
        }
        $sql = AgentBill::query();
        $sql->leftJoin('agent_users','agent_users.id','=','agent_billflow.agent_id')
            ->leftJoin('user','user.user_id','=','agent_billflow.user_id')
            ->select('agent_users.username','agent_users.nickname','user.account','user.nickname as userName','agent_billflow.money','agent_billflow.bet_before','agent_billflow.bet_after','agent_billflow.status','agent_billflow.type','agent_billflow.remark','agent_billflow.creatime');
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->where($map)->where('agent_billflow.agent_id','=',$id)->whereBetween('agent_billflow.creatime',[$begin,$end])->orderBy('agent_billflow.creatime','desc')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$value)
        {
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('agentCz.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
    public function whetherAffiliatedAgent($ancestors)
    {
        $userId = Auth::id();
        foreach ($ancestors as $key=>$value)
        {
            if ($userId==$value){
                return true;
                break;
            }
        }
        return false;
    }
}