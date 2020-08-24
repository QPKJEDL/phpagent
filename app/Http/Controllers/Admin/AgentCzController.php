<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if (true==$request->has('username'))
        {
            $map['agent_users.username']=HttpFilter($request->input('username'));
        }
        $sql = AgentBill::query();
        $sql->leftJoin('agent_users','agent_users.id','=','agent_billflow.agent_id')
            ->leftJoin('user','user.user_id','=','agent_billflow.user_id')
            ->select('agent_users.username','agent_users.nickname','user.account','user.nickname as userName','agent_billflow.money','agent_billflow.bet_before','agent_billflow.bet_after','agent_billflow.status','agent_billflow.type','agent_billflow.remark','agent_billflow.creatime');
        if (true==$request->has('begin') && true==$request->has('end'))
        {
            $begin = strtotime($request->input('begin'));
            $end = strtotime($request->input('end'));
            $sql->whereBetween('agent_billflow.creatime',[$begin,$end]);
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->where($map)->where('agent_users.id','=',Auth::id())->orWhere('agent_users.parent_id','=',Auth::id())->orderBy('creatime','desc')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$value)
        {
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('agentCz.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
}