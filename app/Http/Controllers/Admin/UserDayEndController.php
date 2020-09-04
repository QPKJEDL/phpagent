<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HqUser;
use App\Models\LiveReward;
use App\Models\Order;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserRebate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                $request->offsetSet('end',date('Y-m-d',time()));
            }
            $sql->where($map)->whereBetween('user_rebate.creatime',[$begin,$end])->groupBy('user_rebate.user_id','user_rebate.creatime');
        }
        else{
            $request->offsetSet('begin',date('Y-m-d',time()));
            if (false==$request->has('end'))
            {
                $request->offsetSet('end',date('Y-m-d',time()));
            }
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
        $bool = $this->checkIsToDay($request->input('begin'),$request->input('end'));
        if ($bool)
        {
            if (true==$request->has('account'))
            {
                $account = explode(',',$request->input('account'));
                $userIdArr = array();
                foreach ($account as $key)
                {
                    $userIdArr[] = HqUser::where('account','=',$key)->first()['user_id'];
                }
                $tableName = date('Ymd',time());
                $order = new Order();
                $order->setTable('order_'.$tableName);
                $orderData = $order->select('user_id',DB::raw('SUM(1) as count'),DB::raw('SUM(get_money) as get_money'))->whereIn('user_id',$userIdArr)->groupBy('user_id')->get()->toArray();
                foreach ($orderData as $key=>$datum)
                {
                    $user = $datum['user_id']?HqUser::find($datum['user_id']):[];
                    $agentD = $user['agent_id']?User::find($user['agent_id']):[];
                    $ancestors = explode(',',$agentD['ancestors']);
                    $ancestors[] = $agentD['id'];
                    if (!$this->whetherAffiliatedAgent($ancestors)){
                        array_splice($orderData,$key,1);
                        continue;
                    }
                    $userBalance = UserAccount::where('user_id','=',$datum['user_id'])->first();
                    //用户
                    $orderData[$key]['nickname']=$user['nickname'];
                    $orderData[$key]['account']=$user['account'];
                    $orderData[$key]['balance']=$userBalance['balance'];
                    $orderData[$key]['user_type']=$user['user_type'];
                    $orderDataByUserId = $order->select('bet_money','status','game_type')->where('user_id','=',$datum['user_id'])->get();
                    //总下注金额
                    $orderData[$key]['sumMoney']=$this->getSumBetMoney($orderDataByUserId);
                    //有效下注金额
                    $orderData[$key]['money']=$this->getSumMoney($orderDataByUserId);
                    //打赏金额
                    $money = LiveReward::where('user_id','=',$datum['user_id'])->sum('money');
                    $orderData[$key]['reward']=$money;
                }
            }else{
                $orderData=array();
            }
        }else{
            $orderData=array();
        }
        foreach ($data as $key=>$datum)
        {
            $data[$key]['reward']=LiveReward::getSumMoney($datum['user_id'],$begin,$end);
        }
        foreach ($orderData as $key=>$datum)
        {
            $arr = $this->updateDate($datum['user_id'],$data);
            if ($arr['code']==0)
            {
                $a = array();
                $a['user_id']=$datum['user_id'];
                $a['nickname']=$datum['nickname'];
                $a['account']=$datum['account'];
                $a['balance']=$datum['balance'];
                $a['betNum']=$datum['count'];
                $a['washMoney']=$datum['sumMoney'];
                $a['betMoney']=$datum['money'];
                $a['feeMoney']=0;
                $a['userType']=$datum['user_type'];
                $a['getMoney']=$datum['get_money'];
                $a['reward']=$datum['reward'];
                $data[]=$a;
            }else{
                $index = $arr['index'];
                $data[$index]['betNum']=$data[$index]['betNum'] + $datum['count'];
                $data[$index]['washMoney']=$data[$index]['washMoney']+$datum['sumMoney'];
                $data[$index]['betMoney']=$data[$index]['betMoney']+$datum['money'];
                $data[$index]['getMoney']=$data[$index]['getMoney']+$datum['get_money'];
                $data[$index]['reward']=$data[$index]['reward']+$datum['reward'];
            }
        }
        return view('userDay.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
    public function getUserDayEndByAgentId($id,$begin,$end,Request $request)
    {
        if (true==$request->has('begin'))
        {
            $begin = $request->input('begin');
        }else{
            $request->offsetSet('begin',$begin);
        }
        if (true==$request->has('end'))
        {
            $end = $request->input('end');
        }
        else
        {
            $request->offsetSet('end',$end);
        }
        $bool = $this->checkIsToDay($request->input('begin'),$request->input('end'));
        if ($bool)
        {
            $arr = array();
            $arr['u.agent_id']=$id;
            $order = new Order();
            $order->setTable('order_'.date('Ymd',time()).' as order');
            $sql = $order->leftJoin('user as u','u.user_id','=','order.user_id')
                ->leftJoin('user_account as ua','ua.user_id','=','u.user_id')
                ->select('u.agent_id','u.user_type','u.user_id','u.nickname','u.account','ua.balance',DB::raw('SUM(1) as betNum'),DB::raw('SUM(get_money) as getMoney'));
            if (true==$request->has('account'))
            {
                $arr['u.account']=$request->input('account');
            }
            $orderData = $sql->where($arr)->groupBy('order.user_id')->get()->toArray();
            foreach ($orderData as $key=>&$datum)
            {
                $datum['washMoney']=0;
                $datum['betMoney']=0;
                $oData = $order->where('order.user_id','=',$datum['user_id'])->get()->toArray();
                foreach ($oData as $k=>$v)
                {
                    $betMoney = json_decode($v['bet_money'],true);
                    if ($v['game_type']==1 || $v['game_type']==2)
                    {
                        $datum['washMoney']=$datum['washMoney'] + array_sum($betMoney);
                        if ($v['status']==1)
                        {
                            $datum['betMoney']=$datum['betMoney'] + array_sum($betMoney);
                        }
                    }elseif ($v['game_type']==3)
                    {
                        $datum['washMoney']=$datum['washMoney']+$this->getNiuNiuBetMoney($betMoney);
                        if ($v['status']==1)
                        {
                            $datum['betMoney']=$datum['betMoney'] + $this->getNiuNiuBetMoney($betMoney);
                        }
                    }elseif ($v['game_type']==4)
                    {
                        $datum['washMoney']=$datum['washMoney']+$this->getSanGongBetMoney($betMoney);
                        if ($v['status']==1)
                        {
                            $datum['betMoney']=$datum['betMoney'] + $this->getSanGongBetMoney($betMoney);
                        }
                    }elseif ($v['game_type']==5)
                    {
                        $datum['washMoney']=$datum['washMoney']+$this->getA89BetMoney($betMoney);
                        if ($v['status']==1)
                        {
                            $datum['betMoney']=$datum['betMoney'] + $this->getA89BetMoney($betMoney);
                        }
                    }
                }
            }
        }else{
            $orderData=array();
        }
        //对应分页插件初始化分页参数
        if (true==$request->has('limit'))
        {
            $limit = $request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $beginTime = strtotime($begin);
        $endTime = strtotime('+1day',strtotime($end))-1;
        $map = array();
        $map['user_rebate.agent_id']=$id;
        $sql = UserRebate::query();
        $sql->leftJoin('user','user.user_id','=','user_rebate.user_id')
            ->leftJoin('user_account','user_account.user_id','=','user_rebate.user_id')
            ->select('user_rebate.id')->where($map)->whereBetween('user_rebate.creatime',[$beginTime,$endTime])->groupBy('user_rebate.agent_id','user_rebate.creatime');
        $dataSql = UserRebate::whereIn('user_rebate.id',$sql->get());
        $data = $dataSql->leftJoin('user','user.user_id','=','user_rebate.user_id')
            ->leftJoin('user_account','user_account.user_id','=','user_rebate.user_id')
            ->select('user_rebate.user_id','user.nickname','user.account','user_account.balance',DB::raw('SUM(betNum) as betNum'),
                DB::raw('SUM(washMoney) as washMoney'),DB::raw('SUM(betMoney) as betMoney'),DB::raw('SUM(getMoney) as getMoney'),DB::raw('SUM(feeMoney) as feeMoney'),'user_rebate.userType')->groupBy('user_rebate.user_id')->get()->toArray();
        if (count($data)==0)
        {
            foreach ($orderData as $key=>$v)
            {
                $info = array();
                $info['user_id']=$v['user_id'];
                $info['nickname']=$v['nickname'];
                $info['account']=$v['account'];
                $info['balance']=$v['balance'];
                $info['betNum']=$v['betNum'];
                $info['washMoney']=$v['washMoney'];
                $info['betMoney']=$v['betMoney'];
                $info['getMoney']=$v['getMoney'];
                $info['feeMoney']=0;
                $info['userType']=$v['user_type'];
                $data[]=$info;
            }
        }
        else
        {
            foreach ($orderData as $key=>$datum)
            {
                $arr = $this->updateDate($datum['user_id'],$data);
                if ($arr['code']==1)
                {
                    $index= $arr['index'];
                    $data[$index]['betNum']=$data[$index]['betNum'] + $datum['betNum'];
                    $data[$index]['washMoney']=$data[$index]['washMoney']+$datum['washMoney'];
                    $data[$index]['betMoney']=$data[$index]['betMoney']+$datum['betMoney'];
                    $data[$index]['getMoney']=$data[$index]['getMoney']+$datum['getMoney'];
                }
            }
        }
        foreach ($data as $key=>$datum)
        {
            $data[$key]['reward']=LiveReward::getSumMoney($datum['user_id'],$begin,$end);
        }
        if (true==$request->has('excel'))
        {
            $head = array('台类型','名称','账号','当前金额','下注次数','下注金额','总洗码','派彩所赢','抽水','码佣总额','打赏金额');
            $excel = array();
            foreach ($data as $key=>$datum)
            {
                $a = array();
                $a['desk_name']='全部';
                $a['nickname']=$datum['nickname'];
                $a['account']=$datum['account'];
                $a['balance']=number_format($datum['balance']/100,2);
                $a['betNum']=$datum['betNum'];
                $a['washMoney']=number_format($datum['washMoney']/100,2);
                $a['betMoney']=number_format($datum['betMoney']/100,2);
                $a['getMoney']=number_format($datum['getMoney']/100,2);
                $a['feeMoney']=number_format($datum['feeMoney']/100,2);
                if ($datum['userType']==1)
                {
                    $a['my']=number_format($datum['betMoney']/100 * 0.009,2);
                }else
                {
                    $a['my']='-';
                }
                $a['reward']=number_format($datum['reward']/100,2);
                $excel[] = $a;
            }
            try {
                exportExcel($head, $excel, date('Y-m-d',time()).'会员日结', '', true);
            } catch (\PHPExcel_Reader_Exception $e) {
            } catch (\PHPExcel_Exception $e) {
            }
        }
        return view('userDay.list',['list'=>$data,'min'=>config('admin.min_date'),'input'=>$request->all()]);
    }

    /**
     * 计算a89下注金额
     * @param $data
     * @return float|int
     */
    public function getA89BetMoney($data)
    {
        $money = 0;
        if (!empty($data['ShunMen_equal']))
        {
            $money = $money + $data['ShunMen_equal'];
        }
        if (!empty($data['ShunMen_Super_Double']))
        {
            $money = $money + $data['ShunMen_Super_Double'] * 10;
        }
        if (!empty($data['TianMen_equal']))
        {
            $money = $money + $data['TianMen_equal'];
        }
        if (!empty($data['TianMen_Super_Double']))
        {
            $money = $money + $data['TianMen_Super_Double'] * 10;
        }
        if (!empty($data['FanMen_equal']))
        {
            $money = $money + $data['FanMen_equal'];
        }
        if (!empty($data['FanMen_Super_Double']))
        {
            $money = $money + $data['FanMen_Super_Double'] * 10;
        }
        return $money;
    }

    /**
     * 根据userId效验数组中是否存在该数据
     * @param $userId
     * @param $data
     * @return array
     */
    public function updateDate($userId,$data)
    {
        $arr = array();
        $arr['code']=0;
        if (count($data)!=0)
        {
            foreach ($data as $key=>$datum)
            {
                if ($datum['user_id']==$userId)
                {
                    $arr['code']=1;
                    $arr['index']=$key;
                    break;
                }
            }
        }
        return $arr;
    }
    /**
     * 计算三公下注金额
     * @param $data
     * @return float|int
     */
    public function getSanGongBetMoney($data)
    {
        $money = 0;
        if (!empty($data['x1_equal']))
        {
            $money = $money + $data['x1_equal'];
        }
        if (!empty($data['x1_double']))
        {
            $money = $money + $data['x1_double'] * 3;
        }
        if (!empty($data['x1_Super_Double']))
        {
            $money = $money + $data['x1_Super_Double'] * 10;
        }
        if (!empty($data['x2_equal']))
        {
            $money = $money + $data['x2_equal'];
        }
        if (!empty($data['x2_double']))
        {
            $money = $money + $data['x2_double'] * 3;
        }
        if (!empty($data['x2_Super_Double']))
        {
            $money = $money + $data['x2_Super_Double'] * 10;
        }
        if (!empty($data['x3_equal']))
        {
            $money = $money + $data['x3_equal'];
        }
        if (!empty($data['x3_double']))
        {
            $money = $money + $data['x3_double'] * 3;
        }
        if (!empty($data['x3_Super_Double']))
        {
            $money = $money + $data['x3_Super_Double'] * 10;
        }
        if (!empty($data['x4_equal']))
        {
            $money = $money + $data['x4_equal'];
        }
        if (!empty($data['x4_double']))
        {
            $money = $money + $data['x4_double'] * 3;
        }
        if (!empty($data['x4_Super_Double']))
        {
            $money = $money + $data['x4_Super_Double'] * 10;
        }
        if (!empty($data['x5_equal']))
        {
            $money = $money + $data['x5_equal'];
        }
        if (!empty($data['x5_double']))
        {
            $money = $money + $data['x5_double'] *3;
        }
        if (!empty($data['x5_Super_Double']))
        {
            $money = $money + $data['x5_Super_Double'] * 10;
        }
        if (!empty($data['x6_equal']))
        {
            $money = $money + $data['x6_equal'];
        }
        if (!empty($data['x6_double']))
        {
            $money = $money + $data['x6_double'] *3;
        }
        if (!empty($data['x6_Super_Double']))
        {
            $money = $money + $data['x6_Super_Double'] * 10;
        }
        return $money;
    }
    /**
     * 效验查询是否存在今天
     * @param $startDate
     * @param $endDate
     * @return bool
     */
    public function checkIsToDay($startDate,$endDate)
    {
        $bool = false;
        $data = $this->getDateTimePeriodByBeginAndEnd($startDate,$endDate);
        foreach ($data as $key)
        {
            if ($key==date('Ymd',time()))
            {
                $bool = true;
                break;
            }
        }
        return $bool;
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
     * 获取有效下注
     * @param $data
     * @return float|int|void
     */
    public function getSumMoney($data)
    {
        $money = 0;
        foreach ($data as $key=>$datum)
        {
            if ($datum['status']!=1)
            {
                continue;
            }
            $betMoney = json_decode($datum['bet_money'],true);
            if ($datum['game_type']==1 || $datum['game_type']==2){
                $money = $money + array_sum($betMoney);
            }
            else if ($datum['game_type']==3)
            {
                $money = $money + $this->getNiuNiuBetMoney($betMoney);
            }
            else if ($data['game_type']==4)
            {
                $money = $money + $this->getSanGongBetMoney($betMoney);
            }
            else if ($data['game_type']==5)
            {
                $money = $money + $this->getA89BetMoney($betMoney);
            }
        }
        return $money;
    }
    /**
     * 计算牛牛下注金额
     * @param $data
     * @return float|int
     */
    public function getNiuNiuBetMoney($data)
    {
        $money = 0;
        if (!empty($data['x1_equal']))
        {
            $money = $money + $data['x1_equal'];
        }
        if (!empty($data['x1_double']))
        {
            $money = $money + $data['x1_double'] * 3;
        }
        if (!empty($data['x1_Super_Double']))
        {
            $money = $money + $data['x1_Super_Double'] * 10;
        }
        if (!empty($data['x2_equal']))
        {
            $money = $money + $data['x2_equal'];
        }
        if (!empty($data['x2_double']))
        {
            $money = $money + $data['x2_double'] * 3;
        }
        if (!empty($data['x2_Super_Double']))
        {
            $money = $money + $data['x2_Super_Double'] * 10;
        }
        if (!empty($data['x3_equal']))
        {
            $money = $money + $data['x3_equal'];
        }
        if (!empty($data['x3_double']))
        {
            $money = $money + $data['x3_double'] * 3;
        }
        if (!empty($data['x3_Super_Double']))
        {
            $money = $money + $data['x3_Super_Double'] * 10;
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