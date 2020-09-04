<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\GameRecord;
use App\Models\HqUser;
use App\Models\LiveReward;
use App\Models\Maintain;
use App\Models\Order;
use App\Models\User;
use App\Models\UserRebate;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AgentDayController extends Controller
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
        return view('agentDay.list',['list'=>$data,'input'=>$request->all(),'min'=>config('admin.min_date')]);
    }

    public function checkAgentIdIsExist($agentId,$data)
    {
        $arr = array();
        $arr['exist']=1;
        foreach ($data as $key=>$datum)
        {
            if ($agentId==$datum['agent_id']){
                $arr['exist']=0;
                $arr['index']=$key;
            }
        }
        return $arr;
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
     * @param $data
     * @param $agentId
     * @return float|int
     */
    public function getSumPump($data,$agentId)
    {
        $money = 0;
        //获取当前代理信息
        $agent = $this->getUserInfoByAgentId($agentId);
        foreach ($data as $key=>$value)
        {
            if ($value->status==1){
                //获取用户信息
                $user = HqUser::getUserInfoByUserId($value->user_id);
                if ($value->game_type==1){//百家乐
                    if ($agent['baccarat']==1){//判断是否具有抽水权限
                        if ($user['bjlbets_fee']!=$agent['bjlbets_fee']){
                            //获取表名
                            $tableName = $this->getGameRecordTableNameByRecordSn($value->record_sn);
                            //获取游戏记录
                            $record = GameRecord::getGameRecordInfo($value->record_sn,$tableName);
                            $orderData = json_decode($value->bet_money,true);//下注相亲数组
                            $jsonArr = json_decode($record['winner'],true);//获取游戏结果
                            $userBetFee = json_decode($user['bjlbets_fee'],true);//获取会员赔率
                            $agentBetFee = json_decode($agent['bjlbets_fee'],true);//获取代理赔率
                            if ($jsonArr['game']==1){//和
                                if ($orderData['tie']>0){
                                    $money = $money + (($agentBetFee['tie'] - $userBetFee['tie'])/100) * $orderData['tie'];
                                }
                            }else if ($jsonArr['game']==7){//庄
                                if ($orderData['banker']>0){
                                    $money = $money + (($agentBetFee['banker'] - $userBetFee['banker'])/100) * $orderData['banker'];
                                }
                            }else if($jsonArr['game']==4){//闲
                                if ($orderData['player']>0){
                                    $money = $money + (($agentBetFee['player'] - $userBetFee['player'])/100) * $orderData['player'];
                                }
                            }
                            if ($jsonArr['bankerPair']!=0){
                                if ($orderData['bankerPair']>0){
                                    $money = $money + (($agentBetFee['bankerPair'] - $userBetFee['bankerPair'])/100) * $orderData['bankerPair'];
                                }
                            }
                            if ($jsonArr['playerPair']!=0){
                                if ($orderData['playerPair']>0){
                                    $money = $money + (($agentBetFee['playerPair'] - $userBetFee['playerPair'])/100) * $orderData['playerPair'];
                                }
                            }
                        }
                    }
                }else if ($value->game_type==2){//龙虎
                    if ($agent['dragon_tiger']==1){//判读是否具有抽水权限
                        if ($user['lhbets_fee']!=$agent['lhbets_fee']){
                            //获取表名
                            $tableName = $this->getGameRecordTableNameByRecordSn($value->record_sn);
                            //获取游戏记录
                            $record = GameRecord::getGameRecordInfo($value->record_sn,$tableName);
                            $orderData = json_decode($value->bet_money,true);//下注相亲数组
                            $userBetFee = json_decode($user['lhbets_fee'],true);//获取会员赔率
                            $agentBetFee = json_decode($agent['lhbets_fee'],true);//获取代理赔率
                            if ($record['winner']==1){//和
                                if ($orderData['tie']>0){
                                    $money = $money + (($agentBetFee['tie'] - $userBetFee['tie'])/100) * $orderData['tie'];
                                }
                            }else if ($record['winner']==4){//龙
                                if ($orderData['dragon']>0){
                                    $money = $money + (($agentBetFee['dragon'] - $userBetFee['dragon'])/100) * $orderData['dragon'];
                                }
                            }else if($record['winner']==7){//虎
                                if ($orderData['tiger']>0){
                                    $money = $money + (($agentBetFee['tiger'] - $userBetFee['tiger'])/100) * $orderData['tiger'];
                                }
                            }
                        }
                    }
                }else if ($value->game_type==3){//牛牛
                    if ($agent['niuniu']==1){//判断是否具有抽水权限
                        if ($user['nnbets_fee']!=$agent['nnbets_fee']){
                            //获取表名
                            $tableName = $this->getGameRecordTableNameByRecordSn($value->record_sn);
                            //获取游戏记录表
                            $record = GameRecord::getGameRecordInfo($value->record_sn,$tableName);
                            $jsonArr = json_decode($record['winner'],true);//获取游戏结果
                            $orderData = json_decode($value->bet_money,true);
                            $userBetFee = json_decode($user['nnbets_fee'],true);
                            $agentBetFee = json_decode($agent['nnbets_fee'],true);
                            if ($jsonArr['x1result']=="win"){
                                $result = $this->nConvertNumbers($jsonArr['x1num']);
                                if ($result>9){
                                    if ($orderData['x1_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x1_Super_Double']) * 10;
                                    }
                                    if ($orderData['x1_double']>0){
                                        $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x1_double']) * 3;
                                    }
                                    if ($orderData['x1_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x1_equal'];
                                    }
                                }else if ($result>0 && $result<10){
                                    if ($orderData['x1_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x1_Super_Double']) * $result;
                                    }
                                    if ($result<10 && $result>6){
                                        if ($orderData['x1_double']>0){
                                            $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x1_double']) * 2;
                                        }
                                    }
                                    if ($orderData['x1_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x1_equal'];
                                    }
                                }else{
                                    if ($orderData['x1_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x1_Super_Double']);
                                    }

                                    if ($orderData['x1_double']>0){
                                        $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x1_double']);
                                    }

                                    if ($orderData['x1_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x1_equal'];
                                    }
                                }
                            }
                            if ($jsonArr['x2result']=='win'){
                                $result = $this->nConvertNumbers($jsonArr['x2num']);
                                if ($result>9){
                                    if ($orderData['x2_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x2_Super_Double']) * 10;
                                    }
                                    if ($orderData['x2_double']>0){
                                        $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x2_double']) * 3;
                                    }
                                    if ($orderData['x2_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x2_equal'];
                                    }
                                }else if ($result>0 && $result<10){
                                    if ($orderData['x2_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x2_Super_Double']) * $result;
                                    }
                                    if ($result<10 && $result>6){
                                        if ($orderData['x2_double']>0){
                                            $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x2_double']) * 2;
                                        }
                                    }
                                    if ($orderData['x2_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x2_equal'];
                                    }
                                }else{
                                    if ($orderData['x2_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x2_Super_Double']);
                                    }

                                    if ($orderData['x2_double']>0){
                                        $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x2_double']);
                                    }

                                    if ($orderData['x2_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x2_equal'];
                                    }
                                }
                            }
                            if ($jsonArr['x3result']=='win'){
                                $result = $this->nConvertNumbers($jsonArr['x3num']);
                                if ($result>9){
                                    if ($orderData['x3_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x3_Super_Double']) * 10;
                                    }
                                    if ($orderData['x3_double']>0){
                                        $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x3_double']) * 3;
                                    }
                                    if ($orderData['x3_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x3_equal'];
                                    }
                                }else if ($result>0 && $result<10){
                                    if ($orderData['x3_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x3_Super_Double']) * $result;
                                    }
                                    if ($result<10 && $result>6){
                                        if ($orderData['x3_double']>0){
                                            $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x3_double']) * 2;
                                        }
                                    }
                                    if ($orderData['x3_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x3_equal'];
                                    }
                                }else{
                                    if ($orderData['x3_Super_Double']>0){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100  * $orderData['x3_Super_Double']);
                                    }

                                    if ($orderData['x3_double']>0){
                                        $money = $money + (($agentBetFee['Double'] -  $userBetFee['Double'])/100 * $orderData['x3_double']);
                                    }

                                    if ($orderData['x3_equal']>0){
                                        $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x3_equal'];
                                    }
                                }
                            }
                        }
                    }
                }else if ($value->game_type==4){//三公
                    if ($agent['sangong']==1){
                        if ($user['sgbets_fee']!=$agent['sgbets_fee']){
                            //获取表名
                            $tableName = $this->getGameRecordTableNameByRecordSn($value->record_sn);
                            //获取游戏记录表
                            $record = GameRecord::getGameRecordInfo($value->record_sn,$tableName);
                            $jsonArr = json_decode($record['winner'],true);//获取游戏结果
                            $orderData = json_decode($value->bet_money,true);
                            $userBetFee = json_decode($user['sgbets_fee'],true);
                            $agentBetFee = json_decode($agent['sgbets_fee'],true);
                            if ($jsonArr['x1result']=="win"){
                                $result = $this->sConvertNumbers($jsonArr['x1num']);
                                if ($orderData['x1_Super_Double'] >0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x1_Super_Double']) * 10;
                                    }else if ($result>0 && $result<10){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x1_Super_Double']) * $result;
                                    }else{
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100) * $orderData['x1_Super_Double'];
                                    }
                                }
                                if ($orderData['x1_double'] > 0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x1_double']) * 3;
                                    }else if ($result>6 && $result<10){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x1_double']) * 2;
                                    }else{
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100) * $orderData['x1_double'];
                                    }
                                }
                                if ($orderData['x1_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x1_equal'];
                                }
                            }
                            if ($jsonArr['x2result']=="win"){
                                $result = $this->sConvertNumbers($jsonArr['x2num']);
                                if ($orderData['x2_Super_Double'] >0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x2_Super_Double']) * 10;
                                    }else if ($result>0 && $result<10){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x2_Super_Double']) * $result;
                                    }else{
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100) * $orderData['x2_Super_Double'];
                                    }
                                }
                                if ($orderData['x2_double'] > 0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x2_double']) * 3;
                                    }else if ($result>6 && $result<10){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x2_double']) * 2;
                                    }else{
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100) * $orderData['x2_double'];
                                    }
                                }
                                if ($orderData['x2_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x2_equal'];
                                }
                            }
                            if ($jsonArr['x3result']=="win"){
                                $result = $this->sConvertNumbers($jsonArr['x3num']);
                                if ($orderData['x3_Super_Double'] >0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x3_Super_Double']) * 10;
                                    }else if ($result>0 && $result<10){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x3_Super_Double']) * $result;
                                    }else{
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100) * $orderData['x3_Super_Double'];
                                    }
                                }
                                if ($orderData['x3_double'] > 0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x3_double']) * 3;
                                    }else if ($result>6 && $result<10){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x3_double']) * 2;
                                    }else{
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100) * $orderData['x3_double'];
                                    }
                                }
                                if ($orderData['x3_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x3_equal'];
                                }
                            }
                            if ($jsonArr['x4result']=="win"){
                                $result = $this->sConvertNumbers($jsonArr['x4num']);
                                if ($orderData['x4_Super_Double'] >0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x4_Super_Double']) * 10;
                                    }else if ($result>0 && $result<10){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x4_Super_Double']) * $result;
                                    }else{
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100) * $orderData['x4_Super_Double'];
                                    }
                                }
                                if ($orderData['x4_double'] > 0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x4_double']) * 3;
                                    }else if ($result>6 && $result<10){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x4_double']) * 2;
                                    }else{
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100) * $orderData['x4_double'];
                                    }
                                }
                                if ($orderData['x4_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x4_equal'];
                                }
                            }
                            if ($jsonArr['x5result']=="win"){
                                $result = $this->sConvertNumbers($jsonArr['x5num']);
                                if ($orderData['x5_Super_Double'] >0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x5_Super_Double']) * 10;
                                    }else if ($result>0 && $result<10){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x5_Super_Double']) * $result;
                                    }else{
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100) * $orderData['x5_Super_Double'];
                                    }
                                }
                                if ($orderData['x5_double'] > 0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x5_double']) * 3;
                                    }else if ($result>6 && $result<10){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x5_double']) * 2;
                                    }else{
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100) * $orderData['x5_double'];
                                    }
                                }
                                if ($orderData['x5_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x5_equal'];
                                }
                            }
                            if ($jsonArr['x6result']=="win"){
                                $result = $this->sConvertNumbers($jsonArr['x6num']);
                                if ($orderData['x6_Super_Double'] >0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x6_Super_Double']) * 10;
                                    }else if ($result>0 && $result<10){
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['x6_Super_Double']) * $result;
                                    }else{
                                        $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100) * $orderData['x6_Super_Double'];
                                    }
                                }
                                if ($orderData['x6_double'] > 0){
                                    if ($result>9){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x6_double']) * 3;
                                    }else if ($result>6 && $result<10){
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100 * $orderData['x6_double']) * 2;
                                    }else{
                                        $money = $money + (($agentBetFee['Double'] - $userBetFee['Double'])/100) * $orderData['x6_double'];
                                    }
                                }
                                if ($orderData['x6_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['x6_equal'];
                                }
                            }
                        }
                    }
                }else if($value->game_type==5){//A89
                    if ($agent['A89']==1){
                        if ($user['a89bets_fee']!=$agent['a89bets_fee']){
                            //获取表名
                            $tableName = $this->getGameRecordTableNameByRecordSn($value->record_sn);
                            //获取游戏记录
                            $record = GameRecord::getGameRecordInfo($value->record_sn,$tableName);
                            $jsonArr = json_decode($record['winner'],true);//获取游戏结果
                            $orderData = json_decode($value->bet_money,true);
                            $userBetFee = json_decode($user['a89bets_fee'],true);
                            $agentBetFee = json_decode($agent['a89bets_fee'],true);
                            if ($jsonArr['Fanresult']=="win"){
                                $result = $this->aConvertNumbers($jsonArr['FanNum']);
                                if ($orderData['FanMen_Super_Double']>0){
                                    $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['FanMen_Super_Double']) * $result;
                                }
                                if ($orderData['FanMen_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['FanMen_equal'];
                                }
                            }
                            if ($jsonArr['Shunresult']=="win"){
                                $result = $this->aConvertNumbers($jsonArr['FanNum']);
                                if ($orderData['ShunMen_Super_Double']>0){
                                    $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['ShunMen_Super_Double']) * $result;
                                }
                                if ($orderData['ShunMen_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['ShunMen_equal'];
                                }
                            }
                            if ($jsonArr['Tianresult']=="win"){
                                $result = $this->aConvertNumbers($jsonArr['FanNum']);
                                if ($orderData['TianMen_Super_Double']>0){
                                    $money = $money + (($agentBetFee['SuperDouble'] - $userBetFee['SuperDouble'])/100 * $orderData['TianMen_Super_Double']) * $result;
                                }
                                if ($orderData['TianMen_equal']>0){
                                    $money = $money + (($agentBetFee['Equal'] - $userBetFee['Equal'])/100) * $orderData['TianMen_equal'];
                                }
                            }
                        }
                    }
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
     * 通过agentId获取代理信息
     * @param $agentId
     * @return User|User[]|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getUserInfoByAgentId($agentId){
        return $agentId?User::find($agentId):[];
    }

    /**
     * 通过userId获取用户信息
     * @param $userId
     * @return HqUser|HqUser[]|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getHqUserByUserId($userId){
        return $userId?HqUser::find($userId):[];
    }

    //根据游戏单号获取表名
    public function getGameRecordTableNameByRecordSn($recordSn)
    {
        return substr($recordSn,0,8);
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