<?php


namespace App\Http\Controllers\Online;


use App\Http\Controllers\Controller;
use App\Models\Czrecord;
use Illuminate\Http\Request;

class OnCzController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        if (true==$request->has('pay_type')){
            $map['czrecord.pay_type']=(int)$request->input('pay_type');
        }
        $sql = Czrecord::query();
        $sql->leftJoin('user','user.user_id','=','czrecord.user_id')
            ->leftJoin('agent_users','agent_users.id','=','user.agent_id')
            ->select('czrecord.creatime','czrecord.pay_type','user.nickname','user.account','agent_users.username','agent_users.nickname as agentName','czrecord.score')
            ->where($map);
        if (true==$request->has('begin')){
            $begin = strtotime($request->input('begin'));
            if (true==$request->has('end')){
                $end = strtotime('+1day',strtotime($request->input('end')))-1;
            }else{
                $end = strtotime('+1day',time())-1;
            }
            $sql->whereBetween('czrecord.creatime',[$begin,$end]);
        }
        if (true==$request->has('account')){
            $sql->where('user.account','=',HttpFilter($request->input('account')));
        }
        $sql->orderBy('czrecord.creatime','desc');
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('onAgent.czrecord.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
}