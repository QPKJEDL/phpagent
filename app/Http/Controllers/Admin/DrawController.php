<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billflow;
use App\Models\Draw;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DrawController extends Controller
{
    public function index(Request $request){
        $map = array();
        if (true==$request->has('begin'))
        {
            $startDate = $request->input('begin');
        }
        else
        {
            $startDate = date('Y-m-d',time());
            $request->offsetSet('begin',date('Y-m-d',time()));
        }
        if (true==$request->has('end'))
        {
            $endDate = $request->input('end');
        }
        else
        {
            $endDate = date('Y-m-d',time());
            $request->offsetSet('end',date('Y-m-d',time()));
        }
        if (true==$request->has('account'))
        {
            $map['user.account']=HttpFilter($request->input('account'));
        }
        if (true==$request->has('user_type'))
        {
            $map['user.user_type']=(int)$request->input('user_type');
        }
        $dateArr = $this->getDateTimePeriodByBeginAndEnd($startDate,$endDate);
        //保存类型
        $status = array();
        if (true==$request->has('status'))
        {
            $status[]=(int)$request->input('status');
        }
        else
        {
            $status[] = 1;
            $status[]=3;
        }
        //获取本人包括本人以下的所有代理id
        $idArr = array();
        $idArr[] = Auth::id();
        $agentIdData = User::whereRaw('FIND_IN_SET('.Auth::id().',ancestors)',true)->select('id')->get();
        foreach ($agentIdData as $key=>$datum)
        {
            $idArr[]=$datum['id'];
        }
        //获取第一天的数据
        $bill = new Billflow();
        $bill->setTable('user_billflow_'.$dateArr[0]);
        $sql = $bill->leftJoin('user','user_billflow_'.$dateArr[0].'.user_id','=','user.user_id')
            ->select('user_billflow_'.$dateArr[0].'.*','user.account','user.nickname','user.agent_id')->whereIn('user.agent_id',$idArr)->where($map)->whereIn('user_billflow_'.$dateArr[0].'.status',$status);
        for ($i=1;$i<count($dateArr);$i++)
        {
            $b = new Billflow();
            $b->setTable('user_billflow_'.$dateArr[$i]);
            $d = $b->leftJoin('user','user_billflow_'.$dateArr[$i].'.user_id','=','user.user_id')
                ->select('user_billflow_'.$dateArr[$i].'.*','user.account','user.nickname','user.agent_id')->whereIn('user.agent_id',$idArr)->where($map)->whereIn('user_billflow_'.$dateArr[$i].'.status',$status);
            $sql->unionAll($d);
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = DB::table(DB::raw("({$sql->toSql()}) as a"))->mergeBindings($sql->getQuery())->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]->creatime=date('Y-m-d H:i:s',time());
        }
        return view('draw.list',['limit'=>$limit,'list'=>$data,'input'=>$request->all()]);
    }


    /**
     * 根据开始时间结束时间获取中间得时间段
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getDateTimePeriodByBeginAndEnd($startDate,$endDate){
        $arr = array();
        $start_date = date("Y-m-d",strtotime($startDate));
        $end_date = date("Y-m-d",strtotime($endDate));
        for ($i = strtotime($start_date); $i <= strtotime($end_date);$i += 86400){
            $arr[] = date('Ymd',$i);
        }
        return $arr;
    }

    /**
     * 获取全部代理
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAgentAllList(){
        return User::get();
    }

    /**
     * 根据代理id获取数据
     * @param $agentId
     * @param $data
     * @return mixed
     */
    public function getAgentInfoByAgentId($agentId,$data){
        foreach ($data as $key=>$value){
            if($agentId==$value['id']){
                return $data[$key];
                continue;
            }
        }
    }

    public function getDirectlyAgent($agentId){
        $agentList = $this->getAgentAllList();
        return $this->getRecursiveAgent($agentId,$agentList);
    }

    public function getRecursiveAgent($agentId,$agentList){
        $info = $this->getAgentInfoByAgentId($agentId,$agentList);
        if ($info['parent_id']==0){
            return $info;
        }else{
            return $this->getRecursiveAgent($info['parent_id'],$agentList);
        }
    }
}