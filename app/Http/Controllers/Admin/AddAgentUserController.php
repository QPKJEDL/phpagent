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
        $user['bjlbets_fee']=json_decode($user['bjlbets_fee'],true);
        $user['lhbets_fee']=json_decode($user['lhbets_fee'],true);
        $user['nnbets_fee']=json_decode($user['nnbets_fee'],true);
        $user['sgbets_fee']=json_decode($user['sgbets_fee'],true);
        $user['a89bets_fee']=json_decode($user['a89bets_fee'],true);
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
        if ((int)$data['proportion']>$agentInfo['proportion'] || (int)$data['proportion']<0)
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
        if ($bjl['banker']<$data['bjlbets_fee']['banker'] || $data['bjlbets_fee']['banker']<=0.89)
        {
            return ['msg'=>'赔率不能低于0.9','status'=>0];
        }
        if ($bjl['player']<$data['bjlbets_fee']['player'] || $data['bjlbets_fee']['player']<=0.94)
        {
            return ['msg'=>'赔率不能低于0.95','status'=>0];
        }
        if ($bjl['playerPair']!=$data['bjlbets_fee']['playerPair'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($bjl['tie']!=$data['bjlbets_fee']['tie'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($bjl['bankerPair']!=$data['bjlbets_fee']['bankerPair'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $lh = json_decode($agentInfo['lhbets_fee'],true);
        if ($lh['dragon']!=$data['lhbets_fee']['dragon'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($lh['tie']!=$data['lhbets_fee']['tie'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($lh['tiger']!=$data['lhbets_fee']['tiger'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $nn = json_decode($agentInfo['nnbets_fee'],true);
        if ($nn['Equal']!=$data['nnbets_fee']['Equal'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($nn['Double']!=$data['nnbets_fee']['Double'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($nn['SuperDouble']!=$data['nnbets_fee']['SuperDouble'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $sg = json_decode($agentInfo['sgbets_fee'],true);
        if ($sg['Equal']!=$data['sgbets_fee']['Equal'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($sg['Double']!=$data['sgbets_fee']['Double'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($sg['SuperDouble']!=$data['sgbets_fee']['SuperDouble'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        $a89 = json_decode($agentInfo['a89bets_fee'],true);
        if ($a89['Equal']!=$data['a89bets_fee']['Equal'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($a89['SuperDouble']!=$data['a89bets_fee']['SuperDouble'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        unset($data['_token']);
        unset($data['pwd']);
        if (!empty($data['baccarat']))
        {
            if ($agentInfo['baccarat']==1)
            {
                $data['baccarat']=1;
            }
            else
            {
                unset($data['baccarat']);
            }
        }
        if (!empty($data['dragon_tiger']))
        {
            if ($agentInfo['dragon_tiger']==1)
            {
                $data['dragon_tiger']=1;
            }
            else
            {
                unset($data['dragon_tiger']);
            }
        }
        if (!empty($data['niuniu']))
        {
            if ($agentInfo['niuniu']==1)
            {
                $data['niuniu']=1;
            }
            else
            {
                unset($data['niuniu']);
            }
        }
        if (!empty($data['sangong']))
        {
            if ($agentInfo['sangong']==1)
            {
                $data['sangong']=1;
            }
            else
            {
                unset($data['sangong']);
            }
        }
        if (!empty($data['A89']))
        {
            if ($agentInfo['A89']==1)
            {
                $data['A89']=1;
            }
            else
            {
                unset($data['A89']);
            }
        }
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
        if ($agentInfo['is_allow']==1)
        {
            if (!empty($data['is_allow']))
            {
                $data['is_allow']=1;
            }else{
                $data['is_allow']=2;
            }
        }
        else
        {
            unset($data['is_allow']);
        }
        if ($agentInfo['is_allow_draw']==1)
        {
            if (!empty($data['is_allow_draw']))
            {
                $data['is_allow_draw']=1;
            }
            else
            {
                $data['is_allow_draw']=2;
            }
        }
        else
        {
            unset($data['is_allow_draw']);
        }
        if ($agentInfo['is_allow_password']==1)
        {
            if (!empty($data['is_allow_password']))
            {
                $data['is_allow_password']=2;
            }
            else
            {
                $data['is_allow_password']=1;
            }
        }
        else
        {
            $data['is_allow_password']=2;
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
        return $info['ancestors'].','.$parentId;
    }
}