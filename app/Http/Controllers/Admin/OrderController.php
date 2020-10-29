<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billflow;
use App\Models\Desk;
use App\Models\Game;
use App\Models\GameRecord;
use App\Models\HqUser;
use App\Models\Maintain;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        //获取当前代理以下的代理id
        $idArr = array();
        $idArr[] = Auth::id();
        $agentIdData = User::whereRaw('FIND_IN_SET(' . Auth::id() . ',ancestors)', true)->select('id')->get();
        foreach ($agentIdData as $key => $datum) {
            $idArr[] = $datum['id'];
        }
        $map = array();
        $request->offsetSet('a', 0);
        if (true==$request->has('begin'))
        {
            $startDate = $request->input('begin');
            $begin = strtotime($request->input('begin'))+config('admin.beginTime');
        }else{
            $startDate = date('Y-m-d',time());
            $begin = strtotime($startDate)+config('admin.beginTime');
            $request->offsetSet('begin',$startDate);
        }
        if (true==$request->has('end'))
        {
            $endDate = $request->input('end');
            $endDateTime = date('Y-m-d H:i:s',strtotime('+1day',strtotime($endDate)));
        }else{
            $endDate = date('Y-m-d',time());
            $endDateTime = date('Y-m-d H:i:s',strtotime('+1day',strtotime($endDate)));
            $request->offsetSet('end',$endDate);
        }
        $endTime = strtotime('+1day', strtotime($endDate))+config('admin.beginTime');

        $dateArr = $this->getDateTimePeriodByBeginAndEnd($startDate, $endDateTime);
        if (Schema::hasTable('order_'.$dateArr[0])){
            //获取第一天的数据
            $order = new Order();
            $order->setTable('order_' . $dateArr[0]);
            $sql = $order->leftJoin('user', 'user.user_id', '=', 'order_' . $dateArr[0] . '.user_id')
                ->select('order_' . $dateArr[0] . '.*', 'user.account', 'user.nickname', 'user.fee')->whereIn('user.agent_id', $idArr)->where($map)->whereBetween('order_' . $dateArr[0] . '.creatime', [$begin, $endTime]);
            if (true == $request->has('desk_id'))
            {
                $sql->where('order_'.$dateArr[0].'.desk_id','=',(int)$request->input('desk_id'));
            }
            if (true==$request->has('type'))
            {
                $sql->where('order_'.$dateArr[0].'.game_type','=',(int)$request->input('type'));
            }
            if (true==$request->has('status'))
            {
                $sql->where('order_'.$dateArr[0].'.status','=',(int)$request->input('status'));
            }
            if (true==$request->has('boot_num'))
            {
                $sql->where('order_'.$dateArr[0].'.boot_num','=',(int)$request->input('boot_num'));
            }
            if (true==$request->has('pave_num'))
            {
                $sql->where('order_'.$dateArr[0].'.pave_num','=',(int)$request->input('pave_num'));
            }
            if (true==$request->has('orderSn'))
            {
                $sql->where('order_'.$dateArr[0].'.order_sn','=',(int)$request->input('orderSn'));
            }
            if (true==$request->has('account'))
            {
                $account = explode(',',HttpFilter($request->input('account')));
                $sql->whereIn('user.account',$account);
            }
            if ($dateArr[0]==date('Ymd',time()))
            {
                if (Auth::user()['is_realTime']!=1)
                {
                    $sql->whereRaw('order_'.$dateArr[0].'.status in (1,2,3,4)');
                }
            }
            $sql->orderBy('order_'.$dateArr[0].'.creatime','desc');
        }
        for($i=1;$i<count($dateArr);$i++)
        {
            if (Schema::hasTable('order_'.$dateArr[$i]))
            {
                $o = new Order();
                $o->setTable('order_'.$dateArr[$i]);
                $d = $o->leftJoin('user','user.user_id','=','order_'.$dateArr[$i].'.user_id')
                    ->select('order_'.$dateArr[$i].'.*','user.account','user.nickname','user.fee')->whereIn('user.agent_id',$idArr)->where($map)->whereBetween('order_'.$dateArr[$i].'.creatime',[$begin,$endTime]);
                if (true == $request->has('desk_id'))
                {
                    $d->where('order_'.$dateArr[$i].'.desk_id','=',(int)$request->input('desk_id'));
                }
                if (true==$request->has('type'))
                {
                    $d->where('order_'.$dateArr[$i].'.game_type','=',(int)$request->input('type'));
                }
                if (true==$request->has('status'))
                {
                    $d->where('order_'.$dateArr[$i].'.status','=',(int)$request->input('status'));
                }
                if (true==$request->has('boot_num'))
                {
                    $d->where('order_'.$dateArr[$i].'.boot_num','=',(int)$request->input('boot_num'));
                }
                if (true==$request->has('pave_num'))
                {
                    $d->where('order_'.$dateArr[$i].'.pave_num','=',(int)$request->input('pave_num'));
                }
                if (true==$request->has('orderSn'))
                {
                    $d->where('order_'.$dateArr[$i].'.order_sn','=',(int)$request->input('orderSn'));
                }
                if (true==$request->has('account'))
                {
                    $account = explode(',',HttpFilter($request->input('account')));
                    $d->whereIn('user.account',$account);
                }
                if ($dateArr[$i]==date('Ymd',time()))
                {
                    if (Auth::user()['is_realTime']!=1)
                    {
                        $d->whereRaw('order_'.$dateArr[$i].'.status in (1,2,3,4)');
                    }
                }
                $d->orderBy('order_'.$dateArr[$i].'.creatime','desc');
                $sql->unionAll($d);
            }
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = config('admin.limit');
        }
        $data = DB::table(DB::raw("({$sql->toSql()}) as a"))->mergeBindings($sql->getQuery())->orderBy('creatime','desc')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]->fee = json_decode($datum->fee,true);
            $data[$key]->money = $this->getSumBetMoney($datum);
            $data[$key]->creatime=date('Y-m-d H:i:s',$datum->creatime);
            //获取表名
            $tableName = $this->getGameRecordTableNameByRecordSn($datum->record_sn);
            $winner = $this->getGameRecordInfo($tableName,$datum->record_sn);
            $data[$key]->bill=Billflow::getBillflowByOrderSn($datum->order_sn,$tableName);
            if ($data[$key]->game_type==1){
                $data[$key]->result=$this->getBaccaratParseJson($winner);
                $data[$key]->bet_money=$this->getBaccaratBetMoney($datum->bet_money);
            }else if($data[$key]->game_type==2){
                $data[$key]->result=$this->getDragonTigerJson($winner);
                $data[$key]->bet_money=$this->getDragonTieTiger($datum->bet_money);
            }else if($data[$key]->game_type==3){
                $data[$key]->winner = json_decode($winner,true);
                $data[$key]->result=$this->getFullParseJson($winner);
                $data[$key]->bet_money=$this->getNiuNiuBetMoney($datum->bet_money);
            }else if($data[$key]->game_type==4){
                $data[$key]->winner = json_decode($winner,true);
                $data[$key]->result = $this->getSanGongResult($winner);
                $data[$key]->bet_money=$this->getSanGongMoney($datum->bet_money);
            }else if($data[$key]->game_type==5){
                $data[$key]->winner = json_decode($winner,true);
                $data[$key]->result=$this->getA89Result($winner);
                $data[$key]->bet_money=$this->getA89BetMoney($datum->bet_money);
            }
        }
        return view('order.list',['min'=>config('admin.minDate'),'list'=>$data,'desk'=>$this->getDeskList(),'game'=>Game::getGameByType(),'input'=>$request->all(),'limit'=>$limit]);
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
    public function getOrderListByUserId($id,$begin,$end,Request $request){
        $userInfo = (int)$id?HqUser::find((int)$id):[];
        $agentInfo = $userInfo['agent_id']?User::find($userInfo['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限操作','status'=>0];
        }
        $map =array();
        $map['user.user_id']=$id;
        $request->offsetSet('begin',$begin);
        $request->offsetSet('end',$end);
        $request->offsetSet('a',1);
        if (true == $request->has('begin')) {
            $startDate = HttpFilter($request->input('begin'));
        } else {
            $startDate = date('Y-m-d', time());
            $request->offsetSet('begin', $startDate);
        }
        $startTime = strtotime($startDate);
        if (true == $request->has('end')) {
            $endDate = HttpFilter($request->input('end'));
        } else {
            $endDate = date('Y-m-d', time());
            $request->offsetSet('end', $endDate);
        }
        $endTime = strtotime('+1day',strtotime($endDate))-1;
        $dateArr = $this->getDateTimePeriodByBeginAndEnd($startDate,$endDate);
        if (Schema::hasTable('order_'.$dateArr[0])){
            //获取第一天的数据
            $order = new Order();
            $order->setTable('order_' . $dateArr[0]);
            $sql = $order->leftJoin('user', 'user.user_id', '=', 'order_' . $dateArr[0] . '.user_id')
                ->select('order_' . $dateArr[0] . '.*', 'user.account', 'user.nickname', 'user.fee')->where($map)->whereBetween('order_' . $dateArr[0] . '.creatime', [$startTime, $endTime]);
            if (true == $request->has('desk_id'))
            {
                $sql->where('order_'.$dateArr[0].'.desk_id','=',(int)$request->input('desk_id'));
            }
            if (true==$request->has('type'))
            {
                $sql->where('order_'.$dateArr[0].'.game_type','=',(int)$request->input('type'));
            }
            if (true==$request->has('status'))
            {
                $sql->where('order_'.$dateArr[0].'.status','=',(int)$request->input('status'));
            }
            if (true==$request->has('boot_num'))
            {
                $sql->where('order_'.$dateArr[0].'.boot_num','=',(int)$request->input('boot_num'));
            }
            if (true==$request->has('pave_num'))
            {
                $sql->where('order_'.$dateArr[0].'.pave_num','=',(int)$request->input('pave_num'));
            }
            if (true==$request->has('orderSn'))
            {
                $sql->where('order_'.$dateArr[0].'.order_sn','=',(int)$request->input('orderSn'));
            }
            if (true==$request->has('account'))
            {
                $account = explode(',',HttpFilter($request->input('account')));
                $sql->whereIn('user.account',$account);
            }
        }
        for($i=1;$i<count($dateArr);$i++)
        {
            if (Schema::hasTable('order_'.$dateArr[$i]))
            {
                $o = new Order();
                $o->setTable('order_'.$dateArr[$i]);
                $d = $o->leftJoin('user','user.user_id','=','order_'.$dateArr[$i].'.user_id')
                    ->select('order_'.$dateArr[$i].'.*','user.account','user.nickname','user.fee')->where($map)->whereBetween('order_'.$dateArr[$i].'.creatime',[$startTime,$endTime]);
                if (true == $request->has('desk_id'))
                {
                    $d->where('order_'.$dateArr[$i].'.desk_id','=',(int)$request->input('desk_id'));
                }
                if (true==$request->has('type'))
                {
                    $d->where('order_'.$dateArr[$i].'.game_type','=',(int)$request->input('type'));
                }
                if (true==$request->has('status'))
                {
                    $d->where('order_'.$dateArr[$i].'.status','=',(int)$request->input('status'));
                }
                if (true==$request->has('boot_num'))
                {
                    $d->where('order_'.$dateArr[$i].'.boot_num','=',(int)$request->input('boot_num'));
                }
                if (true==$request->has('pave_num'))
                {
                    $d->where('order_'.$dateArr[$i].'.pave_num','=',(int)$request->input('pave_num'));
                }
                if (true==$request->has('orderSn'))
                {
                    $d->where('order_'.$dateArr[$i].'.order_sn','=',(int)$request->input('orderSn'));
                }
                if (true==$request->has('account'))
                {
                    $account = explode(',',HttpFilter($request->input('account')));
                    $d->whereIn('user.account',$account);
                }
                $sql->unionAll($d);
            }
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = DB::table(DB::raw("({$sql->toSql()}) as a"))->mergeBindings($sql->getQuery())->orderBy('creatime','desc')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]->fee = json_decode($datum->fee,true);
            $data[$key]->money = $this->getSumBetMoney($datum);
            $data[$key]->creatime=date('Y-m-d H:i:s',$datum->creatime);
            //获取表名
            $tableName = $this->getGameRecordTableNameByRecordSn($datum->record_sn);
            $winner = $this->getGameRecordInfo($tableName,$datum->record_sn);
            $data[$key]->bill=Billflow::getBillflowByOrderSn($datum->order_sn,$tableName);
            if ($data[$key]->game_type==1){
                $data[$key]->result=$this->getBaccaratParseJson($winner);
                $data[$key]->bet_money=$this->getBaccaratBetMoney($datum->bet_money);
            }else if($data[$key]->game_type==2){
                $data[$key]->result=$this->getDragonTigerJson($winner);
                $data[$key]->bet_money=$this->getDragonTieTiger($datum->bet_money);
            }else if($data[$key]->game_type==3){
                $data[$key]->winner = json_decode($winner,true);
                $data[$key]->result=$this->getFullParseJson($winner);
                $data[$key]->bet_money=$this->getNiuNiuBetMoney($datum->bet_money);
            }else if($data[$key]->game_type==4){
                $data[$key]->winner = json_decode($winner,true);
                $data[$key]->result = $this->getSanGongResult($winner);
                $data[$key]->bet_money=$this->getSanGongMoney($datum->bet_money);
            }else if($data[$key]->game_type==5){
                $data[$key]->winner = json_decode($winner,true);
                $data[$key]->result=$this->getA89Result($winner);
                $data[$key]->bet_money=$this->getA89BetMoney($datum->bet_money);
            }
        }
        return view('order.list',['min'=>config('admin.minDate'),'list'=>$data,'desk'=>$this->getDeskList(),'game'=>Game::getGameByType(),'input'=>$request->all(),'limit'=>$limit]);
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
     * 获取总下注金额
     * @param $data
     * @return float|int|mixed
     */
    public function getSumBetMoney($value){
        $money = 0;
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

        return $money;
    }
    /**
     * 获取所有台桌
     */
    public function getDeskList(){
        $data = Desk::get();
        return $data;
    }

    //根据游戏单号获取表名
    public function getGameRecordTableNameByRecordSn($recordSn)
    {
        return substr($recordSn,0,8);
    }

    /**
     * 根据表名获取游戏记录
     * @param $tableName
     * @param $recordSn
     * @return mixed
     */
    public function getGameRecordInfo($tableName,$recordSn)
    {
        $game = new GameRecord();
        $game->setTable('game_record_'.$tableName);
        $data = $game->where('record_sn','=',$recordSn)->first();
        return $data['winner'];
    }

    /**
     * 解析百家乐json数据
     * @param $jsonStr
     * @return array
     */
    public function getBaccaratParseJson($jsonStr)
    {
        $arr = array();
        //json格式数据
        //{"game":4,"playerPair":5,"bankerPair":2}
        $data = json_decode($jsonStr, true);
        if ($data['game'] == 1) {
            $arr['game'] = "和";
        } else if ($data['game'] == 4) {
            $arr['game'] = "闲";
        } else {
            $arr['game'] = "庄";
        }
        if (empty($data['playerPair'])) {
            $arr['playerPair'] = "";
        } else {
            $arr['playerPair'] = "闲对";
        }
        if (empty($data['bankerPair'])) {
            $arr['bankerPair'] = "";
        } else {
            $arr['bankerPair'] = "庄对";
        }
        return $arr;
    }

    /**
     * 龙虎
     * @param $winner
     * @return string
     */
    public function getDragonTigerJson($winner)
    {
        if ($winner == 7) {
            $result = "龙";
        } else if ($winner == 4) {
            $result = "虎";
        } else {
            $result = "和";
        }
        return $result;
    }

    /**
     * 牛牛
     * @param $jsonStr
     * @return array
     */
    public function getFullParseJson($jsonStr)
    {
        $arr = array();
        //解析json
        //{"bankernum":"牛1","x1num":"牛牛","x1result":"win","x2num":"牛2","x2result":"win","x3num":"牛3","x3result":"win"}
        $data = json_decode($jsonStr, true);
        //先判断庄是不是通吃
        if ($data['x1result'] == "" && $data['x2result'] == "" && $data['x3result'] == "") {
            $arr['bankernum'] = "庄";
        } else {
            $arr['bankernum'] = "";
        }
        if ($data['x1result'] == "win") {
            $arr['x1result'] = "闲1";
        } else {
            $arr['x1result'] = "";
        }
        if ($data['x2result'] == "win") {
            $arr['x2result'] = "闲2";
        } else {
            $arr['x2result'] = "";
        }
        if ($data['x3result'] == "win") {
            $arr['x3result'] = "闲3";
        } else {
            $arr['x3result'] = "";
        }
        return $arr;
    }
    /**
     * 三公
     * @param $jsonStr
     * @return array
     */
    public function getSanGongResult($jsonStr){
        $arr = array();
        //解析json
        $data = json_decode($jsonStr,true);
        //{"bankernum":"9点","x1num":"小三公","x1result":"win","x2num":"混三公","x2result":"win","x3num":"大三公","x3result":"win","x4num":"0点","x4result":"", "x5num":"1点", "x5result":"", "x6num":"9点", "x6result":""}
        //判断庄是否通吃   && $data['x4result']=="" && $data['x5result']=="" && $data['x6result']==""
        if ($data['x1result']=='' && $data['x2result']=="" && $data['x3result']==""){
            $arr['bankernum'] = "庄";
        }else{
            $arr['bankernum'] = "";
        }
        if ($data['x1result'] == "win") {
            $arr['x1result'] = "闲1";
        } else {
            $arr['x1result'] = "";
        }
        if ($data['x2result'] == "win") {
            $arr['x2result'] = "闲2";
        } else {
            $arr['x2result'] = "";
        }
        if ($data['x3result'] == "win") {
            $arr['x3result'] = "闲3";
        } else {
            $arr['x3result'] = "";
        }
        /*if ($data['x4result'] == "win") {
            $arr['x4result'] = "闲4";
        } else {
            $arr['x4result'] = "";
        }
        if ($data['x5result'] == "win") {
            $arr['x5result'] = "闲5";
        } else {
            $arr['x5result'] = "";
        }
        if ($data['x6result'] == "win") {
            $arr['x6result'] = "闲6";
        } else {
            $arr['x6result'] = "";
        }*/
        return $arr;
    }

    /**
     * A89
     * @param $jsonStr
     * @return array
     */
    public function getA89Result($jsonStr){
        $data = json_decode($jsonStr,true);
        //{"BankerNum":"5点","FanNum":"0点","Fanresult":"","ShunNum":"8点","Shunresult":"win","TianNum":"5点","Tianresult":"win"}
        //判断庄是否通知
        $arr = array();
        if ($data['Fanresult']=="" && $data['Shunresult']=="" && $data['Tianresult']==""){
            $arr['bankernum'] = "庄";
        }else{
            $arr['bankernum'] = "";
        }
        if ($data['Fanresult'] == "win") {
            $arr['Fanresult'] = "反门";
        } else {
            $arr['Fanresult'] = "";
        }
        if ($data['Shunresult'] == "win") {
            $arr['Shunresult'] = "顺门";
        } else {
            $arr['Shunresult'] = "";
        }
        if ($data['Tianresult']=="win"){
            $arr['Tianresult'] = "天门";
        }else{
            $arr['Tianresult'] = "";
        }
        return $arr;
    }

    /**
     * 百家乐
     * @param $betMoney
     * @return string
     */
    public function getBaccaratBetMoney($betMoney){
        $data = json_decode($betMoney,true);
        $str = '';
            if ($data['banker']>0) {
                $str = "庄".$data['banker']/100;
            }
            if ($data['bankerPair']>0) {
                $str = $str."庄对".$data['bankerPair']/100;
            }
            if ($data['player']>0) {
                $str = $str."闲".$data['player']/100;
            }
            if ($data['playerPair']>0) {
                $str = $str."庄对".$data['playerPair']/100;
            }
            if ($data['tie']>0) {
                $str = $str."和".$data['tie']/100;
            }
        return $str;
    }

    /*public function getMoney($betMoney,$type)
    {
        $sum = 0;
        //$data = json_decode($betMoney,true);
        $data = json_decode($betMoney,true);
        if ($type==1){//百家乐
            $sum = array_sum($data);
        }else if ($type==2){//龙虎
            $sum = array_sum($data);
        }else if($type==3){
            if ()
        }
    }*/

    public function getDragonTieTiger($betMoney)
    {
        $data = json_decode($betMoney,true);
        $str = '';
        if($data['dragon']>0){
            $str = "龙".$data['dragon']/100;
        }
        if($data['tie']>0){
            $str = $str." 和".$data['tie']/100;
        }
        if($data['tiger']>0){
            $str = $str." 虎".$data['tiger']/100;
        }
        return $str;
    }

    /**
     * 牛牛
     * @param $betMoney
     * @return string
     */
    public function getNiuNiuBetMoney($betMoney)
    {
        $data = json_decode($betMoney,true);
        $str = "";
        if(!empty($data['x1_equal'])){
            $str = "闲一(平倍)".$data['x1_equal']/100;
        }
        if(!empty($data['x1_double'])){
            $str = $str."闲一(翻倍)".$data['x1_double']/100;
        }
        if(!empty($data['x2_equal'])){
            $str = "闲二(平倍)".$data['x2_equal']/100;
        }
        if(!empty($data['x2_double'])){
            $str = $str."闲二(翻倍)".$data['x2_double']/100;
        }
        if(!empty($data['x3_equal'])){
            $str = "闲三（平倍）".$data['x3_equal']/100;
        }
        if(!empty($data['x3_double'])){
            $str = $str."闲三(翻倍)".$data['x3_double']/100;
        }
        return $str;
    }
    /**
     * 三公
     * @param $betMoney
     * @return string
     */
    public function getSanGongMoney($betMoney)
    {
        $data = json_decode($betMoney,true);
        //{"x1_Super_Double":10000,"x1_double":10000,"x1_equal":10000,"x2_Super_Double":10000,"x2_double":10000,"x2_equal":10000,"x3_Super_Double":10000,"x3_double":10000,"x3_equal":10000,"x4_Super_Double":10000,"x4_double":10000,"x4_equal":10000,"x5_Super_Double":10000,"x5_double":10000,"x5_equal":10000,"x6_Super_Double":10000,"x6_double":10000,"x6_equal":10000}
        $str = "";
        if (!empty($data['x1_Super_Double'])){
            $str = "闲一(超倍)".$data['x1_Super_Double']/100;
        }
        if (!empty($data['x1_double'])){
            $str = $str."闲一(翻倍)".$data['x1_double']/100;
        }
        if (!empty($data['x1_equal'])){
            $str = $str."闲一(平倍)".$data['x1_equal']/100;
        }
        if (!empty($data['x2_Super_Double'])){
            $str = $str."闲二(超倍)".$data['x2_Super_Double']/100;
        }
        if (!empty($data['x2_double'])){
            $str = $str."闲二(翻倍)".$data['x2_double']/100;
        }
        if (!empty($data['x2_equal'])){
            $str = $str.'闲二(平倍)'.$data['x2_equal']/100;
        }
        if (!empty($data['x3_Super_Double'])){
            $str = $str."闲三(超倍)".$data['x3_Super_Double']/100;
        }
        if (!empty($data['x3_double'])){
            $str = $str."闲三(翻倍)".$data['x3_double']/100;
        }
        if (!empty($data['x3_equal'])){
            $str = $str.'闲三(平倍)'.$data['x3_equal']/100;
        }
        if (!empty($data['x4_Super_Double'])){
            $str = $str."闲四(超倍)".$data['x4_Super_Double']/100;
        }
        if (!empty($data['x4_double'])){
            $str = $str."闲四(翻倍)".$data['x4_double']/100;
        }
        if (!empty($data['x4_equal'])){
            $str = $str.'闲四(平倍)'.$data['x4_equal']/100;
        }
        if (!empty($data['x5_Super_Double'])){
            $str = $str."闲五(超倍)".$data['x5_Super_Double']/100;
        }
        if (!empty($data['x5_double'])){
            $str = $str."闲五(翻倍)".$data['x5_double']/100;
        }
        if (!empty($data['x5_equal'])){
            $str = $str.'闲五(平倍)'.$data['x5_equal']/100;
        }
        if (!empty($data['x6_Super_Double'])){
            $str = $str."闲六(超倍)".$data['x6_Super_Double']/100;
        }
        if (!empty($data['x6_double'])){
            $str = $str."闲六(翻倍)".$data['x6_double']/100;
        }
        if (!empty($data['x6_equal'])){
            $str = $str.'闲六(平倍)'.$data['x6_equal']/100;
        }
        return $str;
    }

    /**
     * A89
     * @param $betMoney
     * @return string
     */
    public function getA89BetMoney($betMoney){
        $data = json_decode($betMoney,true);
        //{"ShunMen_Super_Double":10000,"TianMen_Super_Double":10000,"FanMen_Super_Double":10000,"ShunMen_equal":10000,"TianMen_equal":10000,"FanMen_equal":10000}
        $str = "";
        if(!empty($data['ShunMen_Super_Double'])){
            $str = "顺门(超倍)".$data['ShunMen_Super_Double']/100;
        }
        if(!empty($data['TianMen_Super_Double'])){
            $str =$str."天门(超倍)".$data['TianMen_Super_Double']/100;
        }
        if(!empty($data['FanMen_Super_Double'])){
            $str = $str.'反门(超倍)'.$data['FanMen_Super_Double']/100;
        }
        if (!empty($data['ShunMen_equal'])){
            $str = $str.'顺们'.$data['ShunMen_equal']/100;
        }
        if(!empty($data['TianMen_equal'])){
            $str = $str.'天门'.$data['TianMen_equal']/100;
        }
        if(!empty($data['FanMen_equal'])){
            $str = $str.'反门'.$data['FanMen_equal']/100;
        }
        return $str;
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