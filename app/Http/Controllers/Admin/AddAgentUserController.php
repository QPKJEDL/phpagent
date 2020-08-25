<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\AgentRoleUser;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AddAgentUserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user['fee']=json_decode($user['fee'],true);
        $game = Game::getGameList();
        return view('addAgent.list',['user'=>$user,'game'=>$game]);
    }

    /**
     * 效验账号是否存在
     */
    public function checkUnique(StoreRequest $request)
    {
        //获取数据
        $userName = HttpFilter($request->input('userName'));
        $user = User::where("username",'=',$userName)->first();
        if($user){
            return ['msg'=>'该账户已存在','status'=>1];
        }else{
            return ['msg'=>'该账户可以使用','status'=>0];
        }
    }


    public function store(StoreRequest $request){
        $data = $request->all();
        $pattern = "/^\d{6}$/";
        if (!preg_match($pattern,$data['username']))
        {
            return ['msg'=>'账号格式错误','status'=>0];
        }
        if (User::where('username','=',HttpFilter($data['username']))->exists())
        {
            return ['msg'=>'账号已存在','status'=>0];
        }
        $data['nickname']=HttpFilter($data['nickname']);
        $data['parent_id']=Auth::id();
        $agentInfo = $data['parent_id']?User::find($data['parent_id']):[];
        if ((int)$data['proportion']>$agentInfo['proportion'])
        {
            return ['msg'=>'占股错误','status'=>0];
        }
        $fee = json_decode($agentInfo['fee'],true);
        if ($fee['baccarat']<$data['fee']['baccarat'] || $data['fee']['baccarat']<=0)
        {
            return ['msg'=>'洗码率不能小于0，大于自身'];
        }
        if ($fee['dragonTiger']<$data['fee']['dragonTiger'] || $data['fee']['dragonTiger']<=0)
        {
            return ['msg'=>'洗码率不能小于0，大于自身'];
        }
        if ($fee['niuniu']<$data['fee']['niuniu'] || $data['fee']['niuniu']<=0)
        {
            return ['msg'=>'洗码率不能小于0，大于自身'];
        }
        if ($fee['sangong']<$data['fee']['sangong'] || $data['fee']['sangong']<=0)
        {
            return ['msg'=>'洗码率不能小于0，大于自身'];
        }
        if ($fee['A89']<$data['fee']['A89'] || $data['fee']['A89']<=0)
        {
            return ['msg'=>'洗码率不能小于0，大于自身'];
        }
        $bjl=json_decode($agentInfo['bjlbets_fee'],true);//{"banker":"0.95","bankerPair":"11","player":"1","playerPair":"11","tie":"8"}
        if ($bjl['banker']<$data['bjlbets_fee']['banker'] || $data['bjlbets_fee']['banker']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($bjl['player']<$data['bjlbets_fee']['player'] || $data['bjlbets_fee']['player']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($bjl['playerPair']<$data['bjlbets_fee']['playerPair'] || $data['bjlbets_fee']['playerPair']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($bjl['tie']<$data['bjlbets_fee']['tie'] || $data['bjlbets_fee']['tie']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($bjl['bankerPair']<$data['bjlbets_fee']['bankerPair'] || $data['bjlbets_fee']['bankerPair']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $lh = json_decode($agentInfo['lhbets_fee'],true);
        if ($lh['dragon']<$data['lhbets_fee']['dragon'] || $data['lhbets_fee']['dragon']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($lh['tie']<$data['lhbets_fee']['tie'] || $data['lhbets_fee']['tie']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($lh['tiger']<$data['lhbets_fee']['tiger'] || $data['lhbets_fee']['tiger']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $nn = json_decode($agentInfo['nnbets_fee'],true);
        if ($nn['Equal']<$data['nnbets_fee']['Equal'] || $data['nnbets_fee']['Equal']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($nn['Double']<$data['nnbets_fee']['Double'] || $data['nnbets_fee']['Double']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($nn['SuperDouble']<$data['nnbets_fee']['SuperDouble'] || $data['nnbets_fee']['SuperDouble']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $sg = json_decode($agentInfo['sgbets_fee'],true);
        if ($sg['Equal']<$data['sgbets_fee']['Equal'] || $data['sgbets_fee']['Equal']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($sg['Double']<$data['sgbets_fee']['Double'] || $data['sgbets_fee']['Double']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($sg['SuperDouble']<$data['sgbets_fee']['SuperDouble'] || $data['sgbets_fee']['SuperDouble']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $a89 = json_decode($agentInfo['a89bets_fee'],true);
        if ($a89['Equal']<$data['a89bets_fee']['Equal'] || $data['a89bets_fee']['Equal']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($a89['SuperDouble']<$data['a89bets_fee']['SuperDouble'] || $data['a89bets_fee']['SuperDouble']<=0)
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        unset($data['_token']);
        unset($data['pwd']);
        $data['userType']=1;
        $data['password']=bcrypt(HttpFilter($data['password']));
        $data['fee']=json_encode($data['fee']);
        $data['limit']=$agentInfo['limit'];
        $data['bjlbets_fee'] = json_encode($data['bjlbets_fee']);
        $data['lhbets_fee'] = json_encode($data['lhbets_fee']);
        $data['nnbets_fee']= json_encode($data['nnbets_fee']);
        $data['sgbets_fee']=json_encode($data['sgbets_fee']);
        $data['a89bets_fee']=json_encode($data['a89bets_fee']);
        $data['ancestors']= $this->getUserAncestors(Auth::id());
        $data['created_at']=date('Y-m-d H:i:s',time());
        if (!empty($data['is_allow']))
        {
             $data['is_allow']=1;
        }else{
            $data['is_allow']=0;
        }
        $count = User::insertGetId($data);
        if($count){
            AgentRoleUser::insert(['user_id'=>$count,'role_id'=>38]);
            return ['msg'=>'操作成功！','status'=>1];
        }else{
            return ['msg'=>'操作失败！','status'=>0];
        }
    }

    public function getUserAncestors($parentId){
        $info = $parentId?User::find($parentId):[];
        return $info['parent_id'].','.$parentId;
    }
}