<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentBill;
use App\Models\Billflow;
use App\Models\HqUser;
use App\Models\Pay;
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
            $endDateTime = date('Y-m-d H:i:s',strtotime('+1day',strtotime($endDate)));
        }
        else
        {
            $endDate = date('Y-m-d',time());
            $endDateTime = date('Y-m-d H:i:s',strtotime('+1day',strtotime($endDate)));
            $request->offsetSet('end',date('Y-m-d',time()));
        }
        $dateArr = $this->getDateTimePeriodByBeginAndEnd($startDate,$endDateTime);
        $agentIdArr = User::query()->select('id')->whereRaw('id = ? or id in (select t.id from hq_agent_users t where FIND_IN_SET(?,ancestors))',[Auth::id(),Auth::id()])->get()->toArray();
        $userIdArr = HqUser::query()->select('user_id')->whereIn('agent_id',$agentIdArr)->get()->toArray();
        //获取第一天的数据
        $bill = new Billflow();
        $bill->setTable('user_billflow_'.$dateArr[0]);
        $sql = $bill->select('creatime',DB::raw('1 as user_type'),'user_id','nickname','agent_name','fir_name','score as money','bet_before',
            'bet_after','create_by','status','pay_type','business_id','business_name')->whereRaw('status in (1,3)')->whereIn('user_id',$userIdArr);
        for ($i=1;$i<count($dateArr);$i++)
        {
            $b = new Billflow();
            $b->setTable('user_billflow_'.$dateArr[$i]);
            $d= $b->select('creatime',DB::raw('1 as user_type'),'user_id','nickname','agent_name','fir_name','score as money','bet_before',
                'bet_after','create_by','status','pay_type','business_id','business_name')->whereRaw('status in (1,3)')->whereIn('user_id',$userIdArr);
            $sql->unionAll($d);
        }
        $agentBill = AgentBill::query()->select('creatime',DB::raw('2 as user_type'),'agent_id as user_id','agent_name as nickname','top_name as agent_name',
            'fir_name','money','bet_before','bet_after','create_by','status','type as pay_type',DB::raw('0 as business_id'),DB::raw('0 as business_name'))->whereIn('agent_id',$agentIdArr);
        $sql->unionAll($agentBill);
        if (true==$request->has('limit'))
        {
            $limit = $request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $dataSql = DB::table(DB::raw("({$sql->toSql()}) as a"))->mergeBindings($sql->getQuery())->where($map);
        $dataSql->whereRaw('creatime BETWEEN '.strtotime($startDate).' and '.(strtotime($endDateTime)-1).'');
        if (true==$request->has('create_by'))
        {
            if ($request->input('create_by')==0)
            {
                $dataSql->whereRaw('create_by != 0');
            }
        }
        if (true==$request->has('account'))
        {
            $arr = array();
            if (HqUser::where('account','=',$request->input('account'))->exists())
            {
                $info = HqUser::query()->select('user_id')->where("account",'=',$request->input('account'))->first();
                $arr[]=$info['user_id'];
            }
            if (User::where('username','=',$request->input('account'))->exists())
            {
                $info = User::query()->select('id as user_id')->where('username','=',$request->input('account'))->first();
                $arr[]=$info['user_id'];
            }
            $str = implode(',',$arr);
            $dataSql->whereRaw('user_id in ('.$str.')');
        }
        if (true==$request->has('userType'))
        {
            $userType = (int)$request->input('userType');
            $dataSql->whereRaw('user_type ='.$userType.'');
        }
        if (true==$request->has('business_name'))
        {
            $dataSql->whereRaw('business_id = '.$request->input('business_name').'');
        }
        if (true==$request->has('status'))
        {
            $status = (int)$request->input('status');
            $dataSql->whereRaw('status = '.$status.'');
        }
        $data =$dataSql->orderBy('creatime','desc')->paginate($limit);
        foreach ($data as $key=>$datum)
        {
            $data[$key]->creatime = date('Y-m-d H:i:s',$datum->creatime);
            if ($datum->user_type==1)
            {
                $data[$key]->user = $datum->user_id?HqUser::find($datum->user_id):[];
                $data[$key]->sj = $datum->user['agent_id']?User::find($datum->user['agent_id']):[];
                if ($data[$key]->sj['parent_id']==0)
                {
                    $data[$key]->zs = $datum->sj;
                }
                else
                {
                    $ancestors = explode(',',$datum->sj['ancestors']);
                    $data[$key]->zs = $ancestors[1]?User::find($ancestors[1]):[];
                }
            }
            else
            {
                $data[$key]->user = $datum->user_id?User::find($datum->user_id):[];
                if ($datum->user['parent_id']==0)
                {
                    $data[$key]->sj = $datum->user;
                    $data[$key]->zs = $datum->user;
                }
                else
                {
                    $data[$key]->sj = $datum->user['parent_id']?User::find($datum->user['parent_id']):[];
                    if ($datum->sj['parent_id']==0)
                    {
                        $data[$key]->zs = $datum->sj;
                    }
                    else
                    {
                        $ancestors = explode(',',$datum->sj['ancestors']);
                        $data[$key]->zs = $ancestors[1]?User::find($ancestors[1]):[];
                    }
                }
            }
            $data[$key]->creUser = $datum->create_by?User::find($datum->create_by):[];
        }
        return view('agentCz.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit,'business'=>Pay::getAllPayList(),'min'=>config('admin.minDate')]);
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
        $sql = AgentBill::query()->select('creatime',DB::raw('2 as user_type'),'agent_id as user_id','agent_name as nickname','top_name as agent_name',
            'fir_name','money','bet_before','bet_after','create_by','status','type as pay_type',DB::raw('0 as business_id'),DB::raw('0 as business_name'));
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->where($map)->where('agent_id','=',$id)->whereBetween('creatime',[$begin,$end])->orderBy('creatime','desc')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]->creatime = date('Y-m-d H:i:s',$datum->creatime);
            if ($datum->user_type==1)
            {
                $data[$key]->user = $datum->user_id?HqUser::find($datum->user_id):[];
                $data[$key]->sj = $datum->user['agent_id']?User::find($datum->user['agent_id']):[];
                if ($data[$key]->sj['parent_id']==0)
                {
                    $data[$key]->zs = $datum->sj;
                }
                else
                {
                    $ancestors = explode(',',$datum->sj['ancestors']);
                    $data[$key]->zs = $ancestors[1]?User::find($ancestors[1]):[];
                }
            }
            else
            {
                $data[$key]->user = $datum->user_id?User::find($datum->user_id):[];
                if ($datum->user['parent_id']==0)
                {
                    $data[$key]->sj = $datum->user;
                    $data[$key]->zs = $datum->user;
                }
                else
                {
                    $data[$key]->sj = $datum->user['parent_id']?User::find($datum->user['parent_id']):[];
                    if ($datum->sj['parent_id']==0)
                    {
                        $data[$key]->zs = $datum->sj;
                    }
                    else
                    {
                        $ancestors = explode(',',$datum->sj['ancestors']);
                        $data[$key]->zs = $ancestors[1]?User::find($ancestors[1]):[];
                    }
                }
            }
            $data[$key]->creUser = $datum->create_by?User::find($datum->create_by):[];
        }
        return view('agentCz.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit,'business'=>Pay::getAllPayList()]);
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