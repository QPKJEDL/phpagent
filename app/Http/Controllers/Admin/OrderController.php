<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billflow;
use App\Models\Desk;
use App\Models\Game;
use App\Models\GameRecord;
use App\Models\HqUser;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        if(true==$request->has('begin')){
            $tableName = date('Ymd',strtotime($request->input('begin')));
        }else{
            $tableName = date('Ymd',strtotime('-1day'));
        }
        $map = array();
        if(true==$request->has('desk_id')){
            $map['desk_id']=$request->input('desk_id');
        }
        if(true==$request->has('type')){
            $map['game_type']=$request->input('type');
        }
        if(true==$request->has('status')){
            $map['status']=$request->input('status');
        }
        $order = new Order();
        $order->setTable('order_'.$tableName);
        $data = $order->where($map)->paginate(10)->appends($request->all());
        foreach($data as $key=>&$value){
            $data[$key]['creatime']=date('Y-m-d H:m:s',$value['creatime']);
            $data[$key]['bill']=Billflow::getBillflowByOrderSn($value['order_sn'],$tableName);
            $data[$key]['user']=HqUser::getUserInfoByUserId($value['user_id']);
            //下注金额
            $data[$key]['money']=$this->getMoney($value['bet_money']);
            //获取表名
            $tableName=$this->getGameRecordTableNameByRecordSn($value['record_sn']);
            $winner = $this->getGameRecordInfo($tableName,$value['record_sn']);
            if($data[$key]['game_type']==1){
                $data[$key]['result']=$this->getBaccaratParseJson($winner);
                $data[$key]['bet_money']=$this->getBaccaratBetMoney($value['bet_money']);
            }else if($data[$key]['game_type']==2){
                $data[$key]['result']=$this->getDragonTigerJson($winner);
                $data[$key]['bet_money']=$this->getDragonTieTiger($value['bet_money']);
            }else if($data[$key]['game_type']==3){
                $data[$key]['result']=$this->getFullParseJson($winner);
                $data[$key]['bet_money'] = $this->getNiuNiuBetMoney($value['bet_money']);
            }else if($data[$key]['game_type']==4){
                $data[$key]['result']=$this->getSanGongResult($winner);
                $data[$key]['bet_money']=$this->getSanGongMoney($value['bet_money']);
            }else{
                $data[$key]['result']=$this->getA89Result($winner);
                $data[$key]['bet_money']=$this->getA89BetMoney($value['bet_money']);
            }
        }
        //dump($data);
        $min=config('admin.min_date');
        return view('order.list',['list'=>$data,'desk'=>$this->getDeskList(),'game'=>Game::getGameByType(),'input'=>$request->all(),'min'=>$min]);
    }

    public function getOrderListByUserId($id,$time,Request $request){
        $request->offsetSet('begin',$time);
        $tableName = date('Ymd',strtotime($time));
        $map = array();
        $map['user_id']=$id;
        if(true==$request->has('desk_id')){
            $map['desk_id']=$request->input('desk_id');
        }
        if(true==$request->has('type')){
            $map['game_type']=$request->input('type');
        }
        if(true==$request->has('status')){
            $map['status']=$request->input('status');
        }
        $order = new Order();
        $order->setTable('order_'.$tableName);
        $data = $order->where($map)->paginate(10)->appends($request->all());
        foreach($data as $key=>&$value){
            $data[$key]['creatime']=date('Y-m-d H:m:s',$value['creatime']);
            $data[$key]['bill']=Billflow::getBillflowByOrderSn($value['order_sn'],$tableName);
            $data[$key]['user']=HqUser::getUserInfoByUserId($value['user_id']);
            //下注金额
            $data[$key]['money']=$this->getMoney($value['bet_money']);
            //获取表名
            $tableName=$this->getGameRecordTableNameByRecordSn($value['record_sn']);
            $winner = $this->getGameRecordInfo($tableName,$value['record_sn']);
            if($data[$key]['game_type']==1){
                $data[$key]['result']=$this->getBaccaratParseJson($winner);
                $data[$key]['bet_money']=$this->getBaccaratBetMoney($value['bet_money']);
            }else if($data[$key]['game_type']==2){
                $data[$key]['result']=$this->getDragonTigerJson($winner);
                $data[$key]['bet_money']=$this->getDragonTieTiger($value['bet_money']);
            }else if($data[$key]['game_type']==3){
                $data[$key]['result']=$this->getFullParseJson($winner);
                $data[$key]['bet_money'] = $this->getNiuNiuBetMoney($value['bet_money']);
            }else if($data[$key]['game_type']==4){
                $data[$key]['result']=$this->getSanGongResult($winner);
                $data[$key]['bet_money']=$this->getSanGongMoney($value['bet_money']);
            }else{
                $data[$key]['result']=$this->getA89Result($winner);
                $data[$key]['bet_money']=$this->getA89BetMoney($value['bet_money']);
            }
        }
        //dump($data);
        $min=config('admin.min_date');
        return view('order.list',['list'=>$data,'desk'=>$this->getDeskList(),'game'=>Game::getGameByType(),'input'=>$request->all(),'min'=>$min]);
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
        //判断庄是否通吃
        if ($data['x1result']=='' && $data['x2result']=="" && $data['x3result']=="" && $data['x4result']=="" && $data['x5result']=="" && $data['x6result']==""){
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
        if ($data['x4result'] == "win") {
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
        }
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
     */
    public function getBaccaratBetMoney($betMoney){
        $data = json_decode($betMoney,true);
        $str = '';
        foreach ($data as $key=>$value) {
            if ($data['banker']>0) {
                $str = "庄".$data['banker'];
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
        }
        return $str;
    }

    public function getMoney($betMoney)
    {
        $sum = 0;
        //$data = json_decode($betMoney,true);
        $data = json_decode($betMoney,true);
        foreach($data as $key=>$value){
            $sum += $data[$key];
        }
    }

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
}