<?php


namespace App\Http\Controllers\Online;


use App\Http\Controllers\Controller;
use App\Models\GameRecord;
use App\Models\HqUser;
use App\Models\LiveReward;
use App\Models\Maintain;
use App\Models\Order;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserRebate;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OnAgentDayController extends Controller
{
    public function index(Request $request){
        $request->offsetSet('type',1);
        $map = array();
        $idArr = array();
        $idArr[]=Auth::id();
        $agentIdData = User::whereRaw('FIND_IN_SET('.Auth::id().',ancestors)',true)->select('id')->get();
        foreach ($agentIdData as $key=>$datum)
        {
            $idArr[]=$datum['id'];
        }

        $sql = UserRebate::query();
        $sql->leftJoin('agent_users','agent_users.id','=','user_rebate.agent_id')
            ->select('user_rebate.agent_id','agent_users.nickname','agent_users.username','agent_users.fee','agent_users.userType','agent_users.proportion','agent_users.pump',
                DB::raw('SUM(washMoney) as washMoney'),DB::raw('SUM(getMoney) as getMoney'),DB::raw('SUM(betMoney) as betMoney'),DB::raw('SUM(feeMoney) as feeMoney'));
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
        }
        else
        {
            $begin = strtotime(date('Y-m-d',time()));
            $end = strtotime('+1day',$begin)-1;
            $request->offsetSet('begin',date('Y-m-d',$begin));
            $request->offsetSet('end',date('Y-m-d',$end));
        }
        if (true==$request->has('userType'))
        {
            $map['agent_users.userType']=(int)$request->input('userType');
        }
        if (false==$request->has('account'))
        {
            $request->offsetSet('account','');
        }
        $data = $sql->where($map)->whereIn('user_rebate.agent_id',$idArr)->whereBetween('user_rebate.creatime',[$begin,$end])->groupBy('user_rebate.agent_id')->get()->toArray();
        $bool = $this->checkIsToDay($request->input('begin'),$request->input('end'));
        if ($bool)
        {
            $order = new Order();
            $order->setTable('order_'.date('Ymd',time()));
            $orderData = $order->get()->toArray();
            if (count($data)!=0)
            {
                foreach ($data as $key=>$datum)
                {
                    foreach ($orderData as $k=>$v)
                    {
                        $user = $v['user_id']?HqUser::find($v['user_id']):[];
                        $agent = $user['agent_id']?User::find($user['agent_id']):[];
                        $ancestors = explode(',',$agent['ancestors']);
                        $ancestors[] = $agent['id'];
                        if ($this->whetherAffiliatedAgent($ancestors))
                        {
                            $betMoney = json_decode($v['bet_money'],true);
                            if($v['game_type']==1 || $v['game_type']==2)
                            {
                                $data[$key]['washMoney'] = $datum['washMoney'] +array_sum($betMoney);
                                if ($v['status']==1)
                                {
                                    $data[$key]['betMoney']= $datum['betMoney'] + array_sum($betMoney);
                                }
                            }else if ($v['game_type']==3){
                                $data[$key]['washMoney'] = $datum['washMoney'] + $this->getNiuNiuBetMoney($betMoney);
                                if ($v['status']==1)
                                {
                                    $data[$key]['betMoney']=$datum['betMoney'] + $this->getNiuNiuBetMoney($betMoney);
                                }
                            }else if($v['game_type']==4){
                                $data[$key]['washMoney']=$datum['washMoney'] + $this->getSanGongBetMoney($betMoney);
                                if ($v['status']==1)
                                {
                                    $data[$key]['betMoney'] = $datum['betMoney'] + $this->getSanGongBetMoney($betMoney);
                                }
                            }else if($v['game_type']==5){
                                $data[$key]['washMoney']=$datum['washMoney'] + $this->getA89BetMoney($betMoney);
                                if ($v['status']==1)
                                {
                                    $data[$key]['betMoney'] = $datum['betMoney'] + $this->getA89BetMoney($betMoney);
                                }
                            }
                            $data[$key]['getMoney'] = $datum['getMoney'] + $v['get_money'];
                        }
                        else{
                            continue;
                        }
                    }
                }
            }
            else
            {
                foreach ($orderData as $k=>$v){
                    $user = $v['user_id']?HqUser::find($v['user_id']):[];
                    $agentD = $user['agent_id']?User::find($user['agent_id']):[];
                    $ancestorsData = explode(',',$agentD['ancestors']);
                    $ancestorsData[] = $agentD['id'];
                    if (!$this->whetherAffiliatedAgent($ancestorsData)){
                        continue;
                    }
                    if ($user['agent_id']!=0)
                    {
                        if (User::where('id','=',$user['agent_id'])->exists()){
                            $agent = $user['agent_id']?User::find($user['agent_id']):[];
                            $ancestors = explode(',',$agent['ancestors']);
                            $ancestors[] = $agent['id'];
                            $agentInfo = $ancestors[1]?User::find($ancestors[1]):[];
                            $arr = $this->checkAgentIdIsExist($agentInfo['id'],$data);
                            if ($arr['exist']==1)
                            {
                                $a['agent_id']=$agentInfo['id'];
                                $a['nickname']=$agentInfo['nickname'];
                                $a['username']=$agentInfo['username'];
                                $a['userType']=$agentInfo['userType'];
                                if ($agentInfo['userType']==1){
                                    $a['fee']=$agentInfo['fee'];
                                }else{
                                    $a['pump']=$agentInfo['pump'];
                                }
                                $a['proportion']=$agentInfo['proportion'];
                                $a['feeMoney']=0;
                                $a['reward']=0;
                                $betMoney = json_decode($v['bet_money'],true);
                                if($v['game_type']==1 || $v['game_type']==2)
                                {
                                    $a['washMoney'] = array_sum($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $a['betMoney']= array_sum($betMoney);
                                    }
                                }else if ($v['game_type']==3){
                                    $a['washMoney'] = $this->getNiuNiuBetMoney($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $a['betMoney']=$this->getNiuNiuBetMoney($betMoney);
                                    }
                                }else if($v['game_type']==4){
                                    $a['washMoney']=$this->getSanGongBetMoney($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $a['betMoney'] =$this->getSanGongBetMoney($betMoney);
                                    }
                                }else if($v['game_type']==5){
                                    $a['washMoney']=$this->getA89BetMoney($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $a['betMoney'] =$this->getA89BetMoney($betMoney);
                                    }
                                }
                                $a['getMoney'] =$v['get_money'];
                                $data[] = $a;
                            }
                            else
                            {
                                $data[$arr['index']]['getMoney'] = $data[$arr['index']]['getMoney'] + $v['get_money'];
                                $betMoney = json_decode($v['bet_money'],true);
                                if($v['game_type']==1 || $v['game_type']==2)
                                {
                                    $data[$arr['index']]['washMoney'] = $data[$arr['index']]['washMoney']+ array_sum($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $data[$arr['index']]['betMoney']= $data[$arr['index']]['betMoney'] + array_sum($betMoney);
                                    }
                                }else if ($v['game_type']==3){
                                    $data[$arr['index']]['washMoney'] = $data[$arr['index']]['washMoney'] + $this->getNiuNiuBetMoney($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $data[$arr['index']]['betMoney']=$data[$arr['index']]['betMoney']+ $this->getNiuNiuBetMoney($betMoney);
                                    }
                                }else if($v['game_type']==4){
                                    $data[$arr['index']]['washMoney']=$data[$arr['index']]['washMoney']+ $this->getSanGongBetMoney($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $data[$arr['index']]['betMoney'] = $data[$arr['index']]['betMoney']+$this->getSanGongBetMoney($betMoney);
                                    }
                                }else if($v['game_type']==5){
                                    $data[$arr['index']]['washMoney']=$data[$arr['index']]['washMoney']+ $this->getA89BetMoney($betMoney);
                                    if ($v['status']==1)
                                    {
                                        $data[$arr['index']]['betMoney'] =$data[$arr['index']]['betMoney']+$this->getA89BetMoney($betMoney);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($data as $key=>&$datum)
        {
            if ($datum['userType']==1)
            {
                $datum['fee']=json_decode($datum['fee'],true);
                //洗码费
                $datum['code']=$datum['betMoney']*0.009;
                //占股收益
                $datum['zg']=-($datum['getMoney'] + $datum['code']) * ($datum['proportion']/100);
                //总收益
                if ($datum['zg']>0)
                {
                    $datum['sy'] = $datum['zg'] + $datum['feeMoney'] + $datum['code'];
                }
                else
                {
                    $datum['sy']=$datum['zg'] + $datum['feeMoney'] + $datum['code'];
                }


                //总收益
                if ($datum['getMoney']>0)
                {
                    $datum['gs']= -$datum['getMoney'] - $datum['sy'];
                }
                else
                {
                    $datum['gs']= abs($datum['getMoney']) - $datum['sy'];
                }
            }
            else
            {
                $data[$key]['sy']=$datum['feeMoney'];
                $data[$key]['gs']=-($datum['getMoney'] + $datum['sy']);
            }
            //打赏金额
            //获取当前代理下的会员
            $userData = HqUser::where('agent_id','=',$datum['agent_id'])->select('user_id')->get();
            $money = LiveReward::query()->whereIn('user_id',$userData)->sum('money');
            $datum['reward']=$money;
        }
        if (true==$request->has('excel'))
        {
            $excel = array();
            foreach ($data as $key=>&$datum)
            {
                $a = array();
                $a['desk_type']='全部';
                $a['name']=$datum['nickname'];
                $a['username']=$datum['username'];
                $a['washMoney']=number_format($datum['washMoney']/100,2);
                if ($datum['getMoney']>0)
                {
                    $a['getMoney']=number_format(-$datum['getMoney']/100,2);
                }
                else{
                    $a['getMoney']=number_format($datum['getMoney']/100,2);
                }
                $a['betMoney']=number_format($datum['betMoney']/100,2);
                if ($datum['userType']==1){
                    $a['feeMoney']=number_format($datum['feeMoney']/100,2);
                }else{
                    $a['feeMoney']='-';
                }
                $a['reward']=number_format($datum['reward']/100,2);
                if ($datum['userType']==1)
                {
                    $a['fee']=$datum['fee']['baccarat'].'/'.$datum['fee']['dragonTiger'].'/'.$datum['fee']['niuniu'].'/'.$datum['fee']['sangong'].'/'.$datum['fee']['A89'];
                }else
                {
                    $a['fee']='-';
                }

                if ($datum['userType']==1)
                {
                    $a['code']=number_format($datum['code']/100,2);
                    $a['pump']='-';
                    $a['puSy']='-';
                    $a['proportion']=$datum['proportion'].'%';
                    $a['zg'] = number_format($datum['zg']/100,2);
                }else
                {
                    $a['code']='-';
                    $a['pump']=$datum['pump'].'%';
                    $a['puSy']=number_format($datum['feeMoney']/100,2);
                    $a['proportion']='-';
                    $a['zg']=0.00;
                }
                $a['sy'] = number_format($datum['sy']/100,2);
                $a['gs']=number_format($datum['gs']/100,2);
                $excel[] = $a;
            }
            $head = array('台类型','名称','账号','总押码','总赢','总洗码','总抽水','打赏金额','百/龙/牛/三/A','洗码费','抽水比例','抽水收益','占股','占股收益','总收益','公司收益');
            try {
                exportExcel($head, $excel, date('Y-m-d H:i:s',time()).'代理日结', '', true);
            } catch (\PHPExcel_Reader_Exception $e) {
            } catch (\PHPExcel_Exception $e) {
            }
        }
        return view('onAgent.agentDay.list',['list'=>$data,'input'=>$request->all(),'min'=>config('admin.min_date')]);
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
     * 下级代理日结
     * @param $id
     * @param $begin
     * @param $end
     * @param Request $request
     * @return Factory|Application|View
     */
    public function getIndexByParentId($id,$begin,$end,Request $request){
        $agentInfo = (int)$id?User::find((int)$id):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'您没有权限','status'=>0];
        }
        $map = array();
        $map['agent_users.parent_id']=(int)$id;
        $sql = UserRebate::query();
        $sql->leftJoin('agent_users','agent_users.id','=','user_rebate.agent_id')
            ->select('user_rebate.agent_id','agent_users.nickname','agent_users.username','agent_users.fee','agent_users.userType','agent_users.proportion','agent_users.pump',
                DB::raw('SUM(washMoney) as washMoney'),DB::raw('SUM(getMoney) as getMoney'),DB::raw('SUM(betMoney) as betMoney'),DB::raw('SUM(feeMoney) as feeMoney'));
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
            }
        }
        else
        {
            $begin = strtotime(date('Y-m-d',time()));
            $end = strtotime('+1day',$begin)-1;
            $request->offsetSet('begin',date('Y-m-d',$begin));
            $request->offsetSet('end',date('Y-m-d',$end));
        }
        $data = $sql->where($map)->whereBetween('user_rebate.creatime',[$begin,$end])->groupBy('user_rebate.agent_id')->get()->toArray();
        $bool = $this->checkIsToDay($request->input('begin'),$request->input('end'));
        if ($bool)
        {
            $order = new Order();
            $order->setTable('order_'.date('Ymd',time()).' as order');
            $orderData = $this->getAncestorsByAgentId($id,$order);
            if(count($data)==0)
            {
                foreach ($orderData as $key=>$datum)
                {
                    if ($datum['count']==1)
                    {
                        $a = array();
                        $a['agent_id']=$datum['id'];
                        $a['nickname']=$datum['nickname'];
                        $a['username']=$datum['username'];
                        $a['fee']=$datum['fee'];
                        $a['userType']=$datum['userType'];
                        $a['proportion']=$datum['proportion'];
                        $a['pump']=$datum['pump'];
                        $a['washMoney'] = $datum['sumMoney'];
                        $a['getMoney']=$datum['getMoney'];
                        $a['betMoney']=$datum['betMoney'];
                        $a['feeMoney']=0;
                        $data[] = $a;
                    }
                }
            }
            else
            {
                foreach ($orderData as $key=>$datum)
                {
                    $arr = $this->checkAgentIdIsExist($datum['id'],$data);
                    if ($arr['exist']==0)
                    {
                        $index = $arr['index'];
                        $data[$index]['washMoney']=$data[$index]['washMoney'] + $datum['sumMoney'];
                        $data[$index]['getMoney']=$data[$index]['getMoney'] + $datum['getMoney'];
                        $data[$index]['betMoney']=$data[$index]['betMoney'] + $datum['betMoney'];
                    }
                }
            }
        }
        foreach ($data as $key=>&$datum)
        {
            if ($datum['userType']==1)
            {
                //洗码费
                $datum['code']=$datum['betMoney']*0.009;
                $data[$key]['fee']=json_decode($datum['fee'],true);
                $datum['zg']=-($datum['getMoney'] + $datum['code']) * ($datum['proportion']/100);
                //总收益
                if ($datum['zg']>0)
                {
                    $datum['sy'] = $datum['zg'] + $datum['feeMoney'] + $datum['code'];
                }
                else
                {
                    $datum['sy']=$datum['zg'] + $datum['feeMoney'] + $datum['code'];
                }
                //总收益
                if ($datum['getMoney']>0)
                {
                    $datum['gs']= -$datum['getMoney'] - $datum['sy'];
                }
                else
                {
                    $datum['gs']= abs($datum['getMoney']) - $datum['sy'];
                }
            }
            else
            {
                $data[$key]['sy']=$datum['feeMoney'];
                $data[$key]['gs']=-($datum['getMoney'] + $datum['sy']);
            }
            //打赏金额
            //获取当前代理下的会员
            $userData = HqUser::where('agent_id','=',$datum['agent_id'])->select('user_id')->get();
            $money = LiveReward::query()->whereIn('user_id',$userData)->sum('money');
            $data[$key]['reward']=$money;
        }
        if (true==$request->has('excel'))
        {
            $excel = array();
            foreach ($data as $key=>&$datum)
            {
                $a = array();
                $a['desk_type']='全部';
                $a['name']=$datum['nickname'];
                $a['username']=$datum['username'];
                $a['washMoney']=number_format($datum['washMoney']/100,2);
                if ($datum['getMoney']>0)
                {
                    $a['getMoney']=number_format(-$datum['getMoney']/100,2);
                }
                else{
                    $a['getMoney']=number_format($datum['getMoney']/100,2);
                }
                $a['betMoney']=number_format($datum['betMoney']/100,2);
                if ($datum['userType']==1){
                    $a['feeMoney']=number_format($datum['feeMoney']/100,2);
                }else{
                    $a['feeMoney']='-';
                }
                $a['reward']=number_format($datum['reward']/100,2);
                if ($datum['userType']==1)
                {
                    $a['fee']=$datum['fee']['baccarat'].'/'.$datum['fee']['dragonTiger'].'/'.$datum['fee']['niuniu'].'/'.$datum['fee']['sangong'].'/'.$datum['fee']['A89'];
                }else
                {
                    $a['fee']='-';
                }

                if ($datum['userType']==1)
                {
                    $a['code']=number_format($datum['code']/100,2);
                    $a['pump']='-';
                    $a['puSy']='-';
                    $a['proportion']=$datum['proportion'].'%';
                    $a['zg'] = number_format($datum['zg']/100,2);
                }else
                {
                    $a['code']='-';
                    $a['pump']=$datum['pump'].'%';
                    $a['puSy']=number_format($datum['feeMoney']/100,2);
                    $a['proportion']='-';
                    $a['zg']=0.00;
                }
                $a['sy'] = number_format($datum['sy']/100,2);
                $a['gs']=number_format($datum['gs']/100,2);
                $excel[] = $a;
            }
            $head = array('台类型','名称','账号','总押码','总赢','总洗码','总抽水','打赏金额','百/龙/牛/三/A','洗码费','抽水比例','抽水收益','占股','占股收益','总收益','公司收益');
            try {
                exportExcel($head, $excel, date('Y-m-d H:i:s',time()).'代理日结', '', true);
            } catch (\PHPExcel_Reader_Exception $e) {
            } catch (\PHPExcel_Exception $e) {
            }
        }
        return view('agentDay.list',['list'=>$data,'min'=>config('admin.min_date'),'input'=>$request->all()]);
    }
    public function getAncestorsByAgentId($id,$order)
    {
        $idArr = User::query()->select('id','username','nickname','fee','userType','pump','proportion')->where('parent_id','=',$id)->whereRaw('FIND_IN_SET(?,ancestors)',[$id])->get()->toArray();
        foreach ($idArr as $key=>&$value)
        {
            $value['count']=0;
            //总押码
            $value['sumMoney']=0;
            //总赢
            $value['getMoney']=0;
            //总洗码
            $value['betMoney']=0;
            $idArray = User::query()->select('id')->whereRaw('FIND_IN_SET(?,ancestors)',[$value['id']])->get()->toArray();
            $data = $order->leftJoin('user as u','u.user_id','=','order.user_id')
                ->select('order.user_id','order.bet_money','order.get_money','order.game_type','order.status')
                ->where('u.agent_id','=',$value['id'])->orWhereIn('u.agent_id',$idArray)->get()->toArray();
            foreach ($data as  $k=>$datum)
            {
                $value['count']=1;
                $value['getMoney'] = $value['getMoney'] + $datum['get_money'];
                $betMoney = json_decode($datum['bet_money'],true);
                if ($datum['game_type']==1 || $datum['game_type']==2)
                {
                    $value['sumMoney'] = $value['sumMoney'] + array_sum($betMoney);
                    if ($datum['status']==1)
                    {
                        $value['betMoney'] = $value['betMoney'] + array_sum($betMoney);
                    }
                }elseif ($datum['game_type']==3)
                {
                    $value['sumMoney'] = $value['sumMoney'] + $this->getNiuNiuBetMoney($betMoney);
                    if ($datum['status']==1)
                    {
                        $value['betMoney'] = $value['betMoney'] + $this->getNiuNiuBetMoney($betMoney);
                    }
                }elseif ($datum['game_type']==4)
                {
                    $value['sumMoney'] = $value['sumMoney'] + $this->getSanGongBetMoney($betMoney);
                    if ($datum['status']==1)
                    {
                        $value['betMoney'] = $value['betMoney'] + $this->getSanGongBetMoney($betMoney);
                    }
                }elseif ($datum['game_type']==5)
                {
                    $value['sumMoney'] = $value['sumMoney'] + $this->getA89BetMoney($betMoney);
                    if ($datum['status']==1)
                    {
                        $value['betMoney'] = $value['betMoney'] + $this->getA89BetMoney($betMoney);
                    }
                }
            }
        }
        return $idArr;
    }
    //根据游戏单号获取表名
    public function getGameRecordTableNameByRecordSn($recordSn)
    {
        return substr($recordSn,0,8);
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
     * 效验是否查询存在今日
     * @param $start
     * @param $end
     * @return bool
     */
    public function checkIsToDay($start,$end)
    {
        $bool = false;
        $data = $this->getDateTimePeriodByBeginAndEnd($start,$end);
        foreach ($data as $key)
        {
            /*if ($key==date('Ymd',time())){
                $bool = true;
                break;
            }*/
            if ($key==date('Ymd',time())){
                $bool = true;
                break;
            }
        }
        return $bool;
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
     * 根据表名获取游戏记录
     * @param $tableName
     * @param $recordSn
     * @return GameRecord|\Illuminate\Database\Eloquent\Model|null
     */
    public function getGameRecordInfo($tableName,$recordSn)
    {
        $game = new GameRecord();
        $game->setTable('game_record_'.$tableName);
        return $game->where('record_sn','=',$recordSn)->first();
    }
    /**
     * 获取抽水收益
     * @param $userInfo
     * @param $data
     * @return float|int
     */
    public function getSumPumpMoney($userInfo,$data)
    {
        $money =0;
        foreach ($data as $key=>$datum)
        {
            if ($datum->status==1){
                $tableName = $this->getGameRecordTableNameByRecordSn($datum->record_sn);
                $recordInfo = $this->getGameRecordInfo($tableName,$datum->record_sn);
                if ($recordInfo['status']==1){
                    //{"bankerPair":0,"game":7,"playerPair":0} 百家乐
                    $jsonData = json_decode($recordInfo['winner'],true);
                    $betMoney = json_decode($datum->bet_money,true);
                    $user = HqUser::getUserInfoByUserId($datum->user_id);
                    if ($datum->game_type==1){//百家乐
                        if ($jsonData['game']=="7"){    //{"banker":0,"bankerPair":0,"player":0,"playerPair":0,"tie":20000}
                            if ($betMoney['banker']>0){
                                $money = $money + ($betMoney['banker'] - ($betMoney['banker'] * $user['bjlbets_fee']['banker']/100)) * ($userInfo['pump']/100);
                            }
                            if ($user['player']/100 != 1){
                                $money = $money + ($betMoney['player'] - ($betMoney['player'] * $user['bjlbets_fee']['player']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        continue;
                    }elseif ($datum->game_type==2){//龙虎
                        if ($recordInfo['winner']=="4"){//龙 {"dragon":0,"tie":0,"tiger":10000}
                            if ($betMoney['tiger']>0){
                                $money = $money + ($betMoney['tiger'] - ($betMoney['tiger'] * $user['lhbets_fee']['tiger']/100)) * ($userInfo['pump']/100);
                            }
                        }elseif ($recordInfo['winner']=="7"){
                            if ($betMoney['dragon']>0){
                                $money = $money + ($betMoney['dragon'] - ($betMoney['dragon'] * $user['lhbets_fee']['dragon']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        continue;
                    }elseif ($datum->game_type==3){//牛牛 {"bankernum":"没牛","x1num":"没牛","x1result":"","x2num":"没牛","x2result":"win","x3num":"牛4","x3result":"win"}
                        if ($jsonData['x1result']!=""){
                            $result = $this->nConvertNumbers($jsonData['x1num']);
                            if (!empty($betMoney['x1_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x1_Super_Double'] - ($betMoney['x1_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x1_Super_Double'] - ($betMoney['x1_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x1_Super_Double'] - ($betMoney['x1_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x1_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x1_double'] - ($betMoney['x1_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }else if ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x1_double'] - ($betMoney['x1_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x1_double'] - ($betMoney['x1_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x1_equal'])){
                                $money = $money + ($betMoney['x1_equal'] - ($betMoney['x1_equal'] * $user['nnbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['x2result']!=""){
                            $result = $this->nConvertNumbers($jsonData['x2num']);
                            if (!empty($betMoney['x2_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x2_Super_Double'] - ($betMoney['x2_Super_Double'] * $user['nnbets_fee']['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x2_Super_Double'] - ($betMoney['x2_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x2_Super_Double'] - ($betMoney['x2_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x2_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x2_double'] - ($betMoney['x2_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }else if ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x2_double'] - ($betMoney['x2_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x2_double'] - ($betMoney['x2_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x2_equal'])){
                                $money = $money + ($betMoney['x2_equal'] - ($betMoney['x2_equal'] * $user['nnbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['x3result']!=""){
                            $result = $this->nConvertNumbers($jsonData['x3num']);
                            if (!empty($betMoney['x3_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x3_Super_Double'] - ($betMoney['x3_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x3_Super_Double'] - ($betMoney['x3_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x3_Super_Double'] - ($betMoney['x3_Super_Double'] * $user['nnbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x3_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x3_double'] - ($betMoney['x3_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                    dump('牛牛-闲3-翻倍'.(($betMoney['x3_double'] - ($betMoney['x3_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3);
                                }else if ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x3_double'] - ($betMoney['x3_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x3_double'] - ($betMoney['x3_double'] * $user['nnbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x3_equal'])){
                                $money = $money + ($betMoney['x3_equal'] - ($betMoney['x3_equal'] * $user['nnbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        continue;
                    }elseif ($datum->game_type==4){//三公 {"bankernum":"2点","x1num":"9点","x1result":"win","x2num":"1点","x2result":"","x3num":"1点","x3result":"","x4num":"1点","x4result":"","x5num":"3点","x5result":"win","x6num":"9点","x6result":"win"}
                        if ($jsonData['x1result']!=""){
                            $result = $this->sConvertNumbers($jsonData['x1num']);
                            if (!empty($betMoney['x1_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x1_Super_Double'] - ($betMoney['x1_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x1_Super_Double'] - ($betMoney['x1_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x1_Super_Double'] - ($betMoney['x1_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x1_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x1_double'] - ($betMoney['x1_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }elseif ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x1_double'] - ($betMoney['x1_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x1_double'] - ($betMoney['x1_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x1_equal'])){
                                $money = $money + ($betMoney['x1_equal'] - ($betMoney['x1_equal'] * $user['sgbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['x2result']!=""){
                            $result = $this->sConvertNumbers($jsonData['x2num']);
                            if (!empty($betMoney['x2_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x2_Super_Double'] - ($betMoney['x2_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x2_Super_Double'] - ($betMoney['x2_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x2_Super_Double'] - ($betMoney['x2_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x2_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x2_double'] - ($betMoney['x2_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }elseif ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x2_double'] - ($betMoney['x2_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x2_double'] - ($betMoney['x2_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x2_equal'])){
                                $money = $money + ($betMoney['x2_equal'] - ($betMoney['x2_equal'] * $user['sgbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['x3result']!=""){
                            $result = $this->sConvertNumbers($jsonData['x3num']);
                            if (!empty($betMoney['x3_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x3_Super_Double'] - ($betMoney['x3_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x3_Super_Double'] - ($betMoney['x3_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x3_Super_Double'] - ($betMoney['x3_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x3_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x3_double'] - ($betMoney['x3_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }elseif ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x3_double'] - ($betMoney['x3_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x3_double'] - ($betMoney['x3_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x3_equal'])){
                                $money = $money + ($betMoney['x3_equal'] - ($betMoney['x3_equal'] * $user['sgbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['x4result']!=""){
                            $result = $this->sConvertNumbers($jsonData['x4num']);
                            if (!empty($betMoney['x4_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x4_Super_Double'] - ($betMoney['x4_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x4_Super_Double'] - ($betMoney['x4_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x4_Super_Double'] - ($betMoney['x4_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x4_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x4_double'] - ($betMoney['x4_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }elseif ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x4_double'] - ($betMoney['x4_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x4_double'] - ($betMoney['x4_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x4_equal'])){
                                $money = $money + ($betMoney['x4_equal'] - ($betMoney['x4_equal'] * $user['sgbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['x5result']!=""){
                            $result = $this->sConvertNumbers($jsonData['x5num']);
                            if (!empty($betMoney['x5_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x5_Super_Double'] - ($betMoney['x5_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x5_Super_Double'] - ($betMoney['x5_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x5_Super_Double'] - ($betMoney['x5_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x5_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x5_double'] - ($betMoney['x5_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }elseif ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x5_double'] - ($betMoney['x5_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x5_double'] - ($betMoney['x5_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x5_equal'])){
                                $money = $money + ($betMoney['x5_equal'] - ($betMoney['x5_equal'] * $user['sgbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['x6result']!=""){
                            $result = $this->sConvertNumbers($jsonData['x6num']);
                            if (!empty($betMoney['x6_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x6_Super_Double'] - ($betMoney['x6_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['x6_Super_Double'] - ($betMoney['x6_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['x6_Super_Double'] - ($betMoney['x6_Super_Double'] * $user['sgbets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x6_double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['x6_double'] - ($betMoney['x6_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 3;
                                }elseif ($result>6 && $result<10){
                                    $money = $money + (($betMoney['x6_double'] - ($betMoney['x6_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100)) * 2;
                                }else{
                                    $money = $money + ($betMoney['x6_double'] - ($betMoney['x6_double'] * $user['sgbets_fee']['Double']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['x6_equal'])){
                                $money = $money + ($betMoney['x6_equal'] - ($betMoney['x6_equal'] * $user['sgbets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        continue;
                    }elseif ($datum->game_type==5){//A89  {"BankerNum":"7点","FanNum":"3点","Fanresult":"","ShunNum":"0点","Shunresult":"","TianNum":"9点","Tianresult":"win"}
                        if ($jsonData['Fanresult'] != ""){
                            $result = $this->aConvertNumbers($jsonData['FanNum']);
                            if (!empty($betMoney['FanMen_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['FanMen_Super_Double'] - ($betMoney['FanMen_Super_Double'] * $user['a89bets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['FanMen_Super_Double'] - ($betMoney['FanMen_Super_Double'] * $user['a89bets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['FanMen_Super_Double'] - ($betMoney['FanMen_Super_Double'] * $user['a89bets_fee']['SuperDouble']/100)) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['FanMen_equal'])){
                                $money = $money + ($betMoney['FanMen_equal'] - ($betMoney['FanMen_equal'] * $user['a89bets_fee']['Equal']/100)) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['Shunresult'] != ""){
                            $result = $this->aConvertNumbers($jsonData['ShunNum']);
                            if (!empty($betMoney['ShunMen_Super_Double'])){
                                if ($result > 9){
                                    $money = $money + (($betMoney['ShunMen_Super_Double'] - ($betMoney['ShunMen_Super_Double'] * $user['a89bets_fee']['SuperDouble'])) * ($userInfo['pump']/100)) * 10;
                                }elseif ($result>0 && $result<10){
                                    $money = $money + (($betMoney['ShunMen_Super_Double'] - ($betMoney['ShunMen_Super_Double'] * $user['a89bets_fee']['SuperDouble'])) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['ShunMen_Super_Double'] - ($betMoney['ShunMen_Super_Double'] * $user['a89bets_fee']['SuperDouble'])) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['ShunMen_equal'])){
                                $money = $money + ($betMoney['ShunMen_Super_Double'] - ($betMoney['ShunMen_Super_Double'] * $user['a89bets_fee']['Equal'])) * ($userInfo['pump']/100);
                            }
                        }
                        if ($jsonData['Tianresult'] != ""){
                            $result = $this->aConvertNumbers($jsonData['TianNum']);
                            if (!empty($betMoney['TianMen_Super_Double'])){
                                if ($result>9){
                                    $money = $money + (($betMoney['TianMen_Super_Double'] - ($betMoney['TianMen_Super_Double'] * $user['a89bets_fee']['SuperDouble'])) * ($userInfo['pump']/100)) * 10;
                                }elseif($result > 0 && $result<10){
                                    $money = $money + (($betMoney['TianMen_Super_Double'] - ($betMoney['TianMen_Super_Double'] * $user['a89bets_fee']['SuperDouble'])) * ($userInfo['pump']/100)) * $result;
                                }else{
                                    $money = $money + ($betMoney['TianMen_Super_Double'] - ($betMoney['TianMen_Super_Double'] * $user['a89bets_fee']['SuperDouble'])) * ($userInfo['pump']/100);
                                }
                            }
                            if (!empty($betMoney['TianMen_equal'])){
                                $money = $money + ($betMoney['TianMen_equal'] - ($betMoney['TianMen_equal'] * $user['a89bets_fee']['Equal'])) * ($userInfo['pump']/100);
                            }
                        }
                        continue;
                    }
                }else{
                    continue;
                }
            }
        }
        return $money;
    }

    /**
     * 把牛牛游戏结果转成数字
     * @param $str
     * @return int
     */
    public function nConvertNumbers($str){
        $num=0;
        switch ($str)
        {
            case "炸弹牛":
                $num=12;
                break;
            case "五花牛":
                $num=11;
                break;
            case "牛牛":
                $num=10;
                break;
            case "牛9":
                $num=9;
                break;
            case "牛8":
                $num=8;
                break;
            case "牛7":
                $num=7;
                break;
            case "牛6":
                $num=6;
                break;
            case "牛5":
                $num=5;
                break;
            case "牛4":
                $num=4;
                break;
            case "牛3":
                $num=3;
                break;
            case "牛2":
                $num=2;
                break;
            case "牛1":
                $num=1;
                break;
            default:
                $num=0;
                break;
        }
        return $num;
    }

    /**
     * a89结果转数字
     * @param $str
     * @return int
     */
    public function aConvertNumbers($str){
        switch ($str)
        {
            case "0点":
                $count=1;
                break;
            case "1点":
                $count=1;
                break;
            case "2点":
                $count=2;
                break;
            case "3点":
                $count=3;
            case "4点":
                $count=4;
                break;
            case "5点":
                $count=5;
                break;
            case "6点":
                $count=6;
                break;
            case "7点":
                $count=7;
                break;
            case "8点":
                $count=8;
                break;
            case "9点":
                $count=9;
                break;
            default:
                $count=10;
        }
        return $count;
    }

    /**
     * 三公结果转数字
     * @param $str
     * @return int
     */
    public function sConvertNumbers($str){
        switch ($str)
        {
            case "0点":
                $count = 0;
                break;
            case "1点":
                $count=1;
                break;
            case "2点":
                $count=2;
                break;
            case "3点":
                $count=3;
                break;
            case "4点":
                $count=4;
                break;
            case "5点":
                $count=5;
                break;
            case "6点":
                $count=6;
                break;
            case "7点":
                $count=7;
                break;
            case "8点":
                $count=8;
                break;
            case "9点":
                $count=9;
                break;
            case "混三公":
                $count=10;
                break;
            case "小三公":
                $count=11;
                break;
            default:
                $count=12;
        }
        return $count;
    }

    /**
     * 总押码
     * @param $data
     * @return float|int|mixed
     */
    public function getSumBetMoney($data){
        $money = 0;
        foreach ($data as $key=>$datum){
            if ($datum->game_type==1){
                $jsonArr = json_decode($datum->bet_money,true);
                $money = $money + array_sum($jsonArr);
            }else if($datum->game_type==2){
                $jsonArr = json_decode($datum->bet_money,true);
                $money = $money + array_sum($jsonArr);
            }else if ($datum->game_type==3){
                $jsonArr = json_decode($datum->bet_money,true);
                if (!empty($jsonArr['x1_Super_Double'])){
                    $money = $money + $jsonArr['x1_Super_Double'] *10;
                }
                if (!empty($jsonArr['x2_Super_Double'])){
                    $money = $money + $jsonArr['x2_Super_Double'] *10;
                }
                if (!empty($jsonArr['x3_Super_Double'])){
                    $money = $money + $jsonArr['x3_Super_Double'] *10;
                }
                if (!empty($jsonArr['x1_double'])){
                    $money = $money + $jsonArr['x1_double'] * 3;
                }
                if (!empty($jsonArr['x2_double'])){
                    $money = $money + $jsonArr['x2_double'] * 3;
                }
                if (!empty($jsonArr['x3_double'])){
                    $money = $money + $jsonArr['x3_double'] * 3;
                }
                if (!empty($jsonArr['x1_equal'])){
                    $money = $money + $jsonArr['x1_equal'];
                }
                if (!empty($jsonArr['x2_equal'])){
                    $money = $money + $jsonArr['x2_equal'];
                }
                if (!empty($jsonArr['x3_equal'])){
                    $money = $money + $jsonArr['x3_equal'];
                }
            }else if($datum->game_type==4){
                $jsonArr = json_decode($datum->bet_money,true);
                if (!empty($jsonArr['x1_Super_Double'])){
                    $money=$money + $jsonArr['x1_Super_Double'] * 10;
                }
                if (!empty($jsonArr['x2_Super_Double'])){
                    $money=$money + $jsonArr['x2_Super_Double'] * 10;
                }
                if (!empty($jsonArr['x3_Super_Double'])){
                    $money=$money + $jsonArr['x3_Super_Double'] * 10;
                }
                if (!empty($jsonArr['x4_Super_Double'])){
                    $money=$money + $jsonArr['x4_Super_Double'] * 10;
                }
                if (!empty($jsonArr['x5_Super_Double'])){
                    $money=$money + $jsonArr['x5_Super_Double'] * 10;
                }
                if (!empty($jsonArr['x6_Super_Double'])){
                    $money=$money + $jsonArr['x6_Super_Double'] * 10;
                }
                if (!empty($jsonArr['x1_double'])){
                    $money = $money + $jsonArr['x1_double'] * 3;
                }
                if (!empty($jsonArr['x2_double'])){
                    $money = $money + $jsonArr['x2_double'] * 3;
                }
                if (!empty($jsonArr['x3_double'])){
                    $money = $money + $jsonArr['x3_double'] * 3;
                }
                if (!empty($jsonArr['x4_double'])){
                    $money = $money + $jsonArr['x4_double'] * 3;
                }
                if (!empty($jsonArr['x5_double'])){
                    $money = $money + $jsonArr['x5_double'] * 3;
                }
                if (!empty($jsonArr['x6_double'])){
                    $money = $money + $jsonArr['x6_double'] * 3;
                }
                if (!empty($jsonArr['x1_equal'])){
                    $money = $money + $jsonArr['x1_equal'];
                }
                if (!empty($jsonArr['x2_equal'])){
                    $money = $money + $jsonArr['x2_equal'];
                }
                if (!empty($jsonArr['x3_equal'])){
                    $money = $money + $jsonArr['x3_equal'];
                }
                if (!empty($jsonArr['x4_equal'])){
                    $money = $money + $jsonArr['x4_equal'];
                }
                if (!empty($jsonArr['x5_equal'])){
                    $money = $money + $jsonArr['x5_equal'];
                }
                if (!empty($jsonArr['x6_equal'])){
                    $money = $money + $jsonArr['x6_equal'];
                }
            }else if($datum->game_type==5){
                $jsonArr = json_decode($datum->bet_money,true);
                if (!empty($jsonArr['ShunMen_Super_Double'])){
                    $money = $money + $jsonArr['ShunMen_Super_Double'] * 10;
                }
                if (!empty($jsonArr['TianMen_Super_Double'])){
                    $money = $money + $jsonArr['TianMen_Super_Double'] * 10;
                }
                if (!empty($jsonArr['FanMen_Super_Double'])){
                    $money = $money + $jsonArr['FanMen_Super_Double'] * 10;
                }
                if (!empty($jsonArr['ShunMen_equal'])){
                    $money = $money + $jsonArr['ShunMen_equal'];
                }
                if (!empty($jsonArr['TianMen_equal'])){
                    $money = $money + $jsonArr['TianMen_equal'];
                }
                if (!empty($jsonArr['FanMen_equal'])){
                    $money = $money + $jsonArr['FanMen_equal'];
                }
            }
        }
        return $money;
    }

    public function getWinMoney($data)
    {
        $money=0;
        foreach ($data as $key=>$datum)
        {
            $money = $money + $datum->get_money;
        }
        return $money;
    }

    /**
     * 获取总洗码
     * @param $data
     * @return float|int|mixed
     */
    public function getSumCode($data){
        $money = 0;
        foreach ($data as $key=>$datum){
            if ($datum->status==1){
                if ($datum->game_type==1){
                    $jsonArr = json_decode($datum->bet_money,true);
                    $money = $money + array_sum($jsonArr);
                }else if($datum->game_type==2){
                    $jsonArr = json_decode($datum->bet_money,true);
                    $money = $money + array_sum($jsonArr);
                }else if ($datum->game_type==3){
                    $jsonArr = json_decode($datum->bet_money,true);
                    if (!empty($jsonArr['x1_Super_Double'])){
                        $money = $money + $jsonArr['x1_Super_Double'] *10;
                    }
                    if (!empty($jsonArr['x2_Super_Double'])){
                        $money = $money + $jsonArr['x2_Super_Double'] *10;
                    }
                    if (!empty($jsonArr['x3_Super_Double'])){
                        $money = $money + $jsonArr['x3_Super_Double'] *10;
                    }
                    if (!empty($jsonArr['x1_double'])){
                        $money = $money + $jsonArr['x1_double'] * 3;
                    }
                    if (!empty($jsonArr['x2_double'])){
                        $money = $money + $jsonArr['x2_double'] * 3;
                    }
                    if (!empty($jsonArr['x3_double'])){
                        $money = $money + $jsonArr['x3_double'] * 3;
                    }
                    if (!empty($jsonArr['x1_equal'])){
                        $money = $money = $jsonArr['x1_equal'];
                    }
                    if (!empty($jsonArr['x2_equal'])){
                        $money = $money + $jsonArr['x2_equal'];
                    }
                    if (!empty($jsonArr['x3_equal'])){
                        $money = $money + $jsonArr['x3_equal'];
                    }
                }else if($datum->game_type==4){
                    $jsonArr = json_decode($datum->bet_money,true);
                    if (!empty($jsonArr['x1_Super_Double'])){
                        $money=$money + $jsonArr['x1_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['x2_Super_Double'])){
                        $money=$money + $jsonArr['x2_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['x3_Super_Double'])){
                        $money=$money + $jsonArr['x3_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['x4_Super_Double'])){
                        $money=$money + $jsonArr['x4_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['x5_Super_Double'])){
                        $money=$money + $jsonArr['x5_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['x6_Super_Double'])){
                        $money=$money + $jsonArr['x6_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['x1_double'])){
                        $money = $money + $jsonArr['x1_double'] * 3;
                    }
                    if (!empty($jsonArr['x2_double'])){
                        $money = $money + $jsonArr['x2_double'] * 3;
                    }
                    if (!empty($jsonArr['x3_double'])){
                        $money = $money + $jsonArr['x3_double'] * 3;
                    }
                    if (!empty($jsonArr['x4_double'])){
                        $money = $money + $jsonArr['x4_double'] * 3;
                    }
                    if (!empty($jsonArr['x5_double'])){
                        $money = $money + $jsonArr['x5_double'] * 3;
                    }
                    if (!empty($jsonArr['x6_double'])){
                        $money = $money + $jsonArr['x6_double'] * 3;
                    }
                    if (!empty($jsonArr['x1_equal'])){
                        $money = $money + $jsonArr['x1_equal'];
                    }
                    if (!empty($jsonArr['x2_equal'])){
                        $money = $money + $jsonArr['x2_equal'];
                    }
                    if (!empty($jsonArr['x3_equal'])){
                        $money = $money + $jsonArr['x3_equal'];
                    }
                    if (!empty($jsonArr['x4_equal'])){
                        $money = $money + $jsonArr['x4_equal'];
                    }
                    if (!empty($jsonArr['x5_equal'])){
                        $money = $money + $jsonArr['x5_equal'];
                    }
                    if (!empty($jsonArr['x6_equal'])){
                        $money = $money + $jsonArr['x6_equal'];
                    }
                }else if($datum->game_type==5){
                    $jsonArr = json_decode($datum->bet_money,true);
                    if (!empty($jsonArr['ShunMen_Super_Double'])){
                        $money = $money + $jsonArr['ShunMen_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['TianMen_Super_Double'])){
                        $money = $money + $jsonArr['TianMen_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['FanMen_Super_Double'])){
                        $money = $money + $jsonArr['FanMen_Super_Double'] * 10;
                    }
                    if (!empty($jsonArr['ShunMen_equal'])){
                        $money = $money + $jsonArr['ShunMen_equal'];
                    }
                    if (!empty($jsonArr['TianMen_equal'])){
                        $money = $money + $jsonArr['TianMen_equal'];
                    }
                    if (!empty($jsonArr['FanMen_equal'])){
                        $money = $money + $jsonArr['FanMen_equal'];
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
}