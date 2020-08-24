<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desk;
use App\Models\HqUser;
use App\Models\LiveReward;
use App\Models\Maintain;
use App\Models\Order;
use App\Models\UserDayEnd;
use App\Models\UserRebate;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 会员日结表
 * Class UserDayEndController
 * @package App\Http\Controllers\Admin
 */
class UserDayEndController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $sql = UserRebate::query();
        $sql->leftJoin('user','user.user_id','=','user_rebate.user_id')
            ->leftJoin('user_account','user_account.user_id','=','user_rebate.user_id')
            ->select('user_rebate.id');
        if (true==$request->has('userType'))
        {
            $map['user_rebate.userType']=(int)$request->input('userType');
        }
        if (true==$request->has('account'))
        {
            //根据逗号分割字符串转成数组
            $accountArr = explode(',',HttpFilter($request->input('account')));
            $sql->whereIn('user.account',$accountArr);
        }
        else
        {
            $sql->where('user_rebate.id','=',0);
        }
        if (true==$request->has('begin'))
        {
            $begin = strtotime($request->input('begin'));
            if (true==$request->has('end'))
            {
                $end = strtotime('+1day',strtotime($request->input('end')))-1;
            }
            else
            {
                $end = strtotime('+1day',$begin)-1;
                $request->offsetSet('end',date('Y-m-d',$end));
            }
            $sql->where($map)->whereBetween('user_rebate.creatime',[$begin,$end])->groupBy('user_rebate.user_id','user_rebate.creatime');
        }
        //对应分页插件初始化每页显示条数
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $dataSql = UserRebate::whereIn('user_rebate.id',$sql->get());
        $data = $dataSql->leftJoin('user','user.user_id','=','user_rebate.user_id')
            ->leftJoin('user_account','user_account.user_id','=','user_rebate.user_id')
            ->select('user_rebate.user_id','user.nickname','user.account','user_account.balance',DB::raw('SUM(betNum) as betNum'),
                DB::raw('SUM(washMoney) as washMoney'),DB::raw('SUM(betMoney) as betMoney'),DB::raw('SUM(getMoney) as getMoney'),DB::raw('SUM(feeMoney) as feeMoney'),'user_rebate.userType')->groupBy('user_rebate.user_id')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]['reward']=LiveReward::getSumMoney($datum['user_id'],$begin,$end);
        }
        return view('userDay.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
    public function getUserDayEndByAgentId($id,$begin,$end,Request $request)
    {
        $userInfo =(int)$id?HqUser::find((int)$id):[];
        $agentInfo = $userInfo['agent_id']?User::find($userInfo['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限进行查看','status'=>0];
        }
        $request->offsetSet('begin',$begin);
        $request->offsetSet('end',$end);
        if (true==$request->has('pageNum'))
        {
            $curr = (int)$request->input('pageNum');
        }else{
            $curr=1;
        }
        $dateArr = $this->getDateTimePeriodByBeginAndEnd($begin,$end);
        $sql='';
        for ($i=0;$i<count($dateArr);$i++){
            //效验数据表是否存在
            if (Schema::hasTable('order_'.$dateArr[$i])){
                if ($sql==""){
                    $sql = "select * from hq_order_".$dateArr[$i];
                }else{
                    $sql = $sql.' union all select * from hq_order_'.$dateArr[$i];
                }
            }
        }
        if ($sql!="" || $sql!=null){
            if (true==$request->has('account')){
                $dataSql = 'select t1.user_id,sum(t1.get_money) as getMoney,count(t1.user_id) as `count`,u.nickname,u.account,ua.balance from ( select * from ('.$sql.') t where t.creatime between '.strtotime($begin).' and '.strtotime($end).' ) t1 
        left join hq_user u on t1.user_id = u.user_id
        left join hq_user_account ua on ua.user_id = u.user_id
        where u.account = "'.HttpFilter($request->input('account')).'" and u.agent_id='.(int)$id.'
        group by t1.user_id
        LIMIT '.(($curr - 1) * 10).',10
        ';
                //查询分页总页数
                $dataPages = 'select t1.user_id,sum(t1.get_money) as getMoney,count(t1.user_id) as `count`,u.nickname,u.account,ua.balance from ( select * from ('.$sql.') t where t.creatime between '.strtotime($begin).' and '.strtotime($end).' ) t1 
        left join hq_user u on t1.user_id = u.user_id
        left join hq_user_account ua on ua.user_id = u.user_id
        where u.account = "'.HttpFilter($request->input('account')).'" and u.agent_id='.(int)$id.'
        group by t1.user_id';
            }else{
                $dataSql = 'select t1.user_id,sum(t1.get_money) as getMoney,count(t1.user_id) as `count`,u.nickname,u.account,ua.balance from ( select * from ('.$sql.') t where t.creatime between '.strtotime($begin).' and '.strtotime($end).' ) t1 
        left join hq_user u on t1.user_id = u.user_id
        left join hq_user_account ua on ua.user_id = u.user_id
        where u.agent_id = '.(int)$id.'
        group by t1.user_id
        LIMIT '.(($curr - 1) * 10).',10
        ';
                $dataPages = 'select t1.user_id,sum(t1.get_money) as getMoney,count(t1.user_id) as `count`,u.nickname,u.account,ua.balance from ( select * from ('.$sql.') t where t.creatime between '.strtotime($begin).' and '.strtotime($end).' ) t1 
        left join hq_user u on t1.user_id = u.user_id
        left join hq_user_account ua on ua.user_id = u.user_id
        where u.agent_id = '.(int)$id.'
        group by t1.user_id';
            }
            $pages = Db::select($dataPages);
            $data = DB::select($dataSql);
            foreach ($data as $key=>&$value){
                $userDataSql = "";
                for ($i=0;$i<count($dateArr);$i++){
                    if (Schema::hasTable('order_'.$dateArr[$i])){
                        if ($userDataSql==""){
                            $userDataSql = "select user_id,bet_money,game_type,status,creatime from hq_order_".$dateArr[$i];
                        }else{
                            $userDataSql = $userDataSql.' union all select user_id,bet_money,game_type,status,creatime from hq_order_'.$dateArr[$i];
                        }
                    }
                }
                $userData = DB::select('select * from (select * from ('.$userDataSql.') t where t.creatime between '.strtotime($begin).' and '.strtotime($end).') t1 where t1.user_id=?',[$value->user_id]);
                $data[$key]->betMoney=$this->getSumBetMoney($userData);
                $data[$key]->code = $this->getWashCode($userData);
                $data[$key]->reward = LiveReward::getSumMoney($value->user_id,strtotime($begin),strtotime($end));//count($data)/10==0?count($data)/10:count($data)/10 +1]
            }
            return view('userDay.list',['list'=>$data,'min'=>config('admin.min_date'),'input'=>$request->all(),'curr'=>$curr,'pages'=>ceil(count($pages)/10)]);
        }else{
            $data= array();
            return view('userDay.list',['list'=>$data,'min'=>config('admin.min_date'),'input'=>$request->all(),'curr'=>$curr,'pages'=>1]);
        }
    }
    /**
     * 获取总下注金额
     * @param $data
     * @return float|int|mixed
     */
    public function getSumBetMoney($data){
        $money = 0;
        foreach ($data as $key=>$value){
            if ($value->game_type==1){//百家乐
                $bjl = json_decode($value->bet_money,true);
                $money = $money + array_sum($bjl);
            }else if ($value->game_type==2){
                $lh = json_decode($value->bet_money,true);
                $money = $money + array_sum($lh);
            }else if($value->game_type==3){
                $nn = json_decode($value->bet_money,true);
                if (!empty($nn['x1_Super_Double'])){
                    $money = $money + $nn['x1_Super_Double'] * 10;
                }
                if (!empty($nn['x2_Super_Double'])){
                    $money = $money + $nn['x2_Super_Double'] * 10;
                }
                if (!empty($nn['x3_Super_Double'])){
                    $money = $money + $nn['x3_Super_Double'] * 10;
                }
                if (!empty($nn['x1_double'])){
                    $money = $money + $nn['x1_double'] * 3;
                }
                if (!empty($nn['x2_double'])){
                    $money = $money + $nn['x2_double'] * 3;
                }
                if (!empty($nn['x3_double'])){
                    $money = $money + $nn['x3_double'] * 3;
                }
                if (!empty($nn['x1_equal'])){
                    $money = $money + $nn['x1_equal'];
                }
                if (!empty($nn['x2_equal'])){
                    $money = $money + $nn['x2_equal'];
                }
                if (!empty($nn['x3_equal'])){
                    $money = $money + $nn['x3_equal'];
                }
            }else if ($value->game_type==4){
                $sg = json_decode($value->bet_money,true);
                //{"x1_Super_Double":10000,"x1_double":10000,"x1_equal":10000,"x2_Super_Double":10000,"x2_double":10000,"x2_equal":10000,"x3_Super_Double":10000,"x3_double":10000,"x3_equal":10000,"x4_Super_Double":10000,"x4_double":10000,"x4_equal":10000,"x5_Super_Double":10000,"x5_double":10000,"x5_equal":10000,"x6_Super_Double":10000,"x6_double":10000,"x6_equal":10000}
                if (!empty($sg['x1_Super_Double'])){
                    $money = $money + $sg['x1_Super_Double'] * 10;
                }
                if (!empty($sg['x2_Super_Double'])){
                    $money = $money + $sg['x2_Super_Double'] * 10;
                }
                if (!empty($sg['x3_Super_Double'])){
                    $money = $money + $sg['x3_Super_Double'] * 10;
                }
                if (!empty($sg['x4_Super_Double'])){
                    $money = $money + $sg['x4_Super_Double'] * 10;
                }
                if (!empty($sg['x5_Super_Double'])){
                    $money = $money + $sg['x5_Super_Double'] * 10;
                }
                if (!empty($sg['x6_Super_Double'])){
                    $money = $money + $sg['x6_Super_Double'] * 10;
                }
                if (!empty($sg['x1_double'])){
                    $money = $money + $sg['x1_double'] * 3;
                }
                if (!empty($sg['x2_double'])){
                    $money = $money + $sg['x2_double'] * 3;
                }
                if (!empty($sg['x3_double'])){
                    $money = $money + $sg['x3_double'] * 3;
                }
                if (!empty($sg['x4_double'])){
                    $money = $money + $sg['x4_double'] * 3;
                }
                if (!empty($sg['x5_double'])){
                    $money = $money + $sg['x5_double'] * 3;
                }
                if (!empty($sg['x6_double'])){
                    $money = $money + $sg['x6_double'] * 3;
                }
                if (!empty($sg['x1_equal'])){
                    $money = $money + $sg['x1_equal'];
                }
                if (!empty($sg['x2_equal'])){
                    $money = $money + $sg['x2_equal'];
                }
                if (!empty($sg['x3_equal'])){
                    $money = $money + $sg['x3_equal'];
                }
                if (!empty($sg['x4_equal'])){
                    $money = $money + $sg['x4_equal'];
                }
                if (!empty($sg['x5_equal'])){
                    $money = $money + $sg['x5_equal'];
                }
                if (!empty($sg['x6_equal'])){
                    $money = $money + $sg['x6_equal'];
                }
            }else if($value->game_type==5){
                $a89 = json_decode($value->bet_money,true);
                if (!empty($a89['ShunMen_Super_Double'])){
                    $money = $money + $a89['ShunMen_Super_Double'] * 10;
                }
                if (!empty($a89['TianMen_Super_Double'])){
                    $money = $money + $a89['TianMen_Super_Double'] * 10;
                }
                if (!empty($a89['FanMen_Super_Double'])){
                    $money = $money + $a89['FanMen_Super_Double'] * 10;
                }
                if (!empty($a89['ShunMen_equal'])){
                    $money = $money + $a89['ShunMen_equal'];
                }
                if (!empty($a89['TianMen_equal'])){
                    $money = $money + $a89['TianMen_equal'];
                }
                if (!empty($a89['FanMen_equal'])){
                    $money = $money + $a89['FanMen_equal'];
                }
            }
        }
        return $money;
    }
    /**
     * 获取有效下注金额
     * @param $data
     * @return float|int|mixed
     */
    public function getWashCode($data){
        $money = 0;
        foreach ($data as $key=>$value){
            if ($value->status==1){
                if ($value->game_type==1){//百家乐
                    $bjl = json_decode($value->bet_money,true);
                    $money = $money + array_sum($bjl);
                }else if ($value->game_type==2){
                    $lh = json_decode($value->bet_money,true);
                    $money = $money + array_sum($lh);
                }else if($value->game_type==3){
                    $nn = json_decode($value->bet_money,true);
                    if (!empty($nn['x1_Super_Double'])){
                        $money = $money + $nn['x1_Super_Double'] * 10;
                    }
                    if (!empty($nn['x2_Super_Double'])){
                        $money = $money + $nn['x2_Super_Double'] * 10;
                    }
                    if (!empty($nn['x3_Super_Double'])){
                        $money = $money + $nn['x3_Super_Double'] * 10;
                    }
                    if (!empty($nn['x1_double'])){
                        $money = $money + $nn['x1_double'] * 3;
                    }
                    if (!empty($nn['x2_double'])){
                        $money = $money + $nn['x2_double'] * 3;
                    }
                    if (!empty($nn['x3_double'])){
                        $money = $money + $nn['x3_double'] * 3;
                    }
                    if (!empty($nn['x1_equal'])){
                        $money = $money + $nn['x1_equal'];
                    }
                    if (!empty($nn['x2_equal'])){
                        $money = $money + $nn['x2_equal'];
                    }
                    if (!empty($nn['x3_equal'])){
                        $money = $money + $nn['x3_equal'];
                    }
                }else if ($value->game_type==4){
                    $sg = json_decode($value->bet_money,true);
                    //{"x1_Super_Double":10000,"x1_double":10000,"x1_equal":10000,"x2_Super_Double":10000,"x2_double":10000,"x2_equal":10000,"x3_Super_Double":10000,"x3_double":10000,"x3_equal":10000,"x4_Super_Double":10000,"x4_double":10000,"x4_equal":10000,"x5_Super_Double":10000,"x5_double":10000,"x5_equal":10000,"x6_Super_Double":10000,"x6_double":10000,"x6_equal":10000}
                    if (!empty($sg['x1_Super_Double'])){
                        $money = $money + $sg['x1_Super_Double'] * 10;
                    }
                    if (!empty($sg['x2_Super_Double'])){
                        $money = $money + $sg['x2_Super_Double'] * 10;
                    }
                    if (!empty($sg['x3_Super_Double'])){
                        $money = $money + $sg['x3_Super_Double'] * 10;
                    }
                    if (!empty($sg['x4_Super_Double'])){
                        $money = $money + $sg['x4_Super_Double'] * 10;
                    }
                    if (!empty($sg['x5_Super_Double'])){
                        $money = $money + $sg['x5_Super_Double'] * 10;
                    }
                    if (!empty($sg['x6_Super_Double'])){
                        $money = $money + $sg['x6_Super_Double'] * 10;
                    }
                    if (!empty($sg['x1_double'])){
                        $money = $money + $sg['x1_double'] * 3;
                    }
                    if (!empty($sg['x2_double'])){
                        $money = $money + $sg['x2_double'] * 3;
                    }
                    if (!empty($sg['x3_double'])){
                        $money = $money + $sg['x3_double'] * 3;
                    }
                    if (!empty($sg['x4_double'])){
                        $money = $money + $sg['x4_double'] * 3;
                    }
                    if (!empty($sg['x5_double'])){
                        $money = $money + $sg['x5_double'] * 3;
                    }
                    if (!empty($sg['x6_double'])){
                        $money = $money + $sg['x6_double'] * 3;
                    }
                    if (!empty($sg['x1_equal'])){
                        $money = $money + $sg['x1_equal'];
                    }
                    if (!empty($sg['x2_equal'])){
                        $money = $money + $sg['x2_equal'];
                    }
                    if (!empty($sg['x3_equal'])){
                        $money = $money + $sg['x3_equal'];
                    }
                    if (!empty($sg['x4_equal'])){
                        $money = $money + $sg['x4_equal'];
                    }
                    if (!empty($sg['x5_equal'])){
                        $money = $money + $sg['x5_equal'];
                    }
                    if (!empty($sg['x6_equal'])){
                        $money = $money + $sg['x6_equal'];
                    }
                }else if($value->game_type==5){
                    $a89 = json_decode($value->bet_money,true);
                    if (!empty($a89['ShunMen_Super_Double'])){
                        $money = $money + $a89['ShunMen_Super_Double'] * 10;
                    }
                    if (!empty($a89['TianMen_Super_Double'])){
                        $money = $money + $a89['TianMen_Super_Double'] * 10;
                    }
                    if (!empty($a89['FanMen_Super_Double'])){
                        $money = $money + $a89['FanMen_Super_Double'] * 10;
                    }
                    if (!empty($a89['ShunMen_equal'])){
                        $money = $money + $a89['ShunMen_equal'];
                    }
                    if (!empty($a89['TianMen_equal'])){
                        $money = $money + $a89['TianMen_equal'];
                    }
                    if (!empty($a89['FanMen_equal'])){
                        $money = $money + $a89['FanMen_equal'];
                    }
                }
            }
        }
        return $money;
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
     * 获取昨天的开始时间
     * @return false|int
     */
    public function getYesterdayBeginTime(){
        return strtotime(date("Y-m-d",strtotime("-1 day")));
    }

    /**
     * 根据昨天的开始时间获取到结束时间
     * @param $time 昨天的开始时间
     * @return float|int
     */
    public function getYesterdayEndTime($time){
        return $time+24 * 60 * 60-1;
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