<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 代理提现查询
 * Class agentDrawController
 * @package App\Http\Controllers\Admin
 */
class AgentDrawController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $map['agent_billflow.status']=2;
        if (true==$request->has('username'))
        {
            $map['agent_users.username']=HttpFilter($request->input('username'));
        }
        $sql = AgentBill::query();
        $sql->leftJoin('agent_users','agent_users.id','=','agent_billflow.agent_id')
            ->leftJoin('user','user.user_id','=','agent_billflow.user_id')
            ->select('agent_users.username','agent_users.nickname','user.account','user.nickname as userName','agent_billflow.money','agent_billflow.bet_before','agent_billflow.bet_after','agent_billflow.status','agent_billflow.remark','agent_billflow.creatime');
        if (true==$request->has('begin') && true==$request->has('end'))
        {
            $begin = strtotime($request->input('begin'));
            $end = strtotime($request->input('end'));
            $sql->whereBetween('agent_billflow.creatime',[$begin,$end]);
        }
        $data = $sql->where($map)->where('agent_users.id','=',Auth::id())->orWhere('agent_users.parent_id','=',Auth::id())->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value)
        {
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('agentDraw.list',['list'=>$data,'input'=>$request->all(),'min'=>config('admin.min_date')]);
    }
}