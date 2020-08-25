<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Game;
use App\Models\HqUser;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Auth;

class AddUserController extends Controller
{
    public function index(){
        $user = Auth::user();
        $user['fee']=json_decode($user['fee'],true);
        $user['limit']=json_decode($user['limit'],true);
        $user['nnbets_fee']=json_decode($user['nnbets_fee'],true);
        $user['lhbets_fee']=json_decode($user['lhbets_fee'],true);
        $user['bjlbets_fee']=json_decode($user['bjlbets_fee'],true);
        $user['sgbets_fee']=json_decode($user['sgbets_fee'],true);
        $user['a89bets_fee']=json_decode($user['a89bets_fee'],true);
        return view('addUser.list',['user'=>$user]);
    }

    /**
     * 保存新增会员
     */
    public function store(StoreRequest $request){
        $account = HttpFilter($request->input('account'));
        $password = HttpFilter($request->input('pwd'));
        $pattern = "/^\d{8}$/";
        if (!preg_match($pattern,$account))
        {
            return ['msg'=>'账号格式错误','status'=>0];
        }
        $data = $request->all();
        unset($data['_token']);
        unset($data['pwd']);
        //效验账号是否存在
        $result = HqUser::where('account','=',$account)->exists();
        if($result){
            return ['msg'=>'该账号已存在','status'=>0];
        }else{
            if($data['password']!=$password){
                return ['msg'=>'两次密码不一致','status'=>0];
            }else{
                $data['parent_id']=Auth::id();
                $agentInfo = $data['parent_id']?User::find($data['parent_id']):[];
                $fee = json_decode($agentInfo['fee'],true);
                if ($fee['baccarat']<$data['fee']['baccarat'] && $data['fee']['baccarat']<0)
                {
                    return ['msg'=>'洗码率不能小于0，大于自身'];
                }
                if ($fee['dragonTiger']<$data['fee']['dragonTiger'] && $data['fee']['dragonTiger']<0)
                {
                    return ['msg'=>'洗码率不能小于0，大于自身'];
                }
                if ($fee['niuniu']<$data['fee']['niuniu'] && $data['fee']['niuniu']<0)
                {
                    return ['msg'=>'洗码率不能小于0，大于自身'];
                }
                if ($fee['sangong']<$data['fee']['sangong'] && $data['fee']['sangong']<0)
                {
                    return ['msg'=>'洗码率不能小于0，大于自身'];
                }
                if ($fee['A89']<$data['fee']['A89'] && $data['fee']['A89']<0)
                {
                    return ['msg'=>'洗码率不能小于0，大于自身'];
                }
                $bjl=json_decode($agentInfo['bjlbets_fee'],true);//{"banker":"0.95","bankerPair":"11","player":"1","playerPair":"11","tie":"8"}
                if ($bjl['banker']<$data['bjlbets_fee']['banker'] && $data['bjlbets_fee']['banker']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($bjl['player']<$data['bjlbets_fee']['player'] && $data['bjlbets_fee']['player']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($bjl['playerPair']<$data['bjlbets_fee']['playerPair'] && $data['bjlbets_fee']['playerPair']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($bjl['tie']<$data['bjlbets_fee']['tie'] && $data['bjlbets_fee']['tie']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($bjl['bankerPair']<$data['bjlbets_fee']['bankerPair'] && $data['bjlbets_fee']['bankerPair']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                $lh = json_decode($agentInfo['lhbets_fee'],true);
                if ($lh['dragon']<$data['lhbets_fee']['dragon'] && $data['lhbets_fee']['dragon']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($lh['tie']<$data['lhbets_fee']['tie'] && $data['lhbets_fee']['tie']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($lh['tiger']<$data['lhbets_fee']['tiger'] && $data['lhbets_fee']['tiger']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                $nn = json_decode($agentInfo['nnbets_fee'],true);
                if ($nn['Equal']<$data['nnbets_fee']['Equal'] && $data['nnbets_fee']['Equal']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($nn['Double']<$data['nnbets_fee']['Double'] && $data['nnbets_fee']['Double']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($nn['SuperDouble']<$data['nnbets_fee']['SuperDouble'] && $data['nnbets_fee']['SuperDouble']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                $sg = json_decode($agentInfo['sgbets_fee'],true);
                if ($sg['Equal']<$data['sgbets_fee']['Equal'] && $data['sgbets_fee']['Equal']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($sg['Double']<$data['sgbets_fee']['Double'] && $data['sgbets_fee']['Double']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($sg['SuperDouble']<$data['sgbets_fee']['SuperDouble'] && $data['sgbets_fee']['SuperDouble']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                $a89 = json_decode($agentInfo['a89bets_fee'],true);
                if ($a89['Equal']<$data['a89bets_fee']['Equal'] && $data['a89bets_fee']['Equal']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                if ($a89['SuperDouble']<$data['a89bets_fee']['SuperDouble'] && $data['a89bets_fee']['SuperDouble']<0)
                {
                    return ['msg'=>'赔率错误','status'=>0];
                }
                $data['agent_id']=Auth::id();
                $data['fee']=json_encode($data['fee']);
                $data['limit'] = $agentInfo['limit'];
                $data['password']=md5($data['password']);

                $bjl['player']=intval($data['bjlbets_fee']['player']*100);
                $bjl['playerPair']=intval($data['bjlbets_fee']['playerPair']*100);
                $bjl['tie'] = intval($data['bjlbets_fee']['tie']*100);
                $bjl['banker']=intval($data['bjlbets_fee']['banker']*100);
                $bjl['bankerPair']=intval($data['bjlbets_fee']['bankerPair']*100);
                $data['bjlbets_fee']=json_encode($bjl);

                $lh['dragon']=intval($data['lhbets_fee']['dragon']*100);
                $lh['tie']=intval($data['lhbets_fee']['tie']*100);
                $lh['tiger']=intval($data['lhbets_fee']['tiger']*100);
                $data['lhbets_fee']=json_encode($lh);

                $nn['Equal']=intval($data['nnbets_fee']['Equal']*100);
                $nn['Double']=intval($data['nnbets_fee']['Double']*100);
                $nn['SuperDouble']=intval($data['nnbets_fee']['SuperDouble']*100);
                $data['nnbets_fee']=json_encode($nn);

                $sg['Equal']=intval($data['sgbets_fee']['Equal']*100);
                $sg['Double']=intval($data['sgbets_fee']['Double']*100);
                $sg['SuperDouble'] = intval($data['sgbets_fee']['SuperDouble']*100);
                $data['sgbets_fee']=json_encode($sg);

                $a89['Equal'] = intval($data['a89bets_fee']['Equal']*100);
                $a89['Double']=97;
                $a89['SuperDouble'] = intval($data['a89bets_fee']['SuperDouble'] * 100);
                $data['a89bets_fee']=json_encode($a89);
                if (!empty($data['is_show'])){
                    $data['is_show']=1;
                }
                $data['creatime']=time();
                $data['savetime']=(int)$data['creatime'];
                $data['user_type']=1;
                $count = HqUser::insertGetId($data);
                if($count){
                    $account = array();
                    $account['user_id']=$count;
                    $account['balance']=0;
                    $account['tol_recharge']=0;
                    $account['drawMoney']=0;
                    $account['creatime']=time();
                    $num = UserAccount::insert($account);
                    if ($num){
                        return ['msg'=>'操作成功','status'=>1];
                    }else{
                        return ['msg'=>'操作失败','status'=>0];
                    }
                }else{
                    return ['msg'=>'操作失败','status'=>0];
                }
            }
        }
    }
}