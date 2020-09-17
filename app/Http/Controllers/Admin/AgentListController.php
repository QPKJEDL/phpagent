<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\AgentBill;
use App\Models\AgentProportion;
use App\Models\Czrecord;
use App\Models\HqUser;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;

/**
 * 代理商数据权限
 * Class AgentListController
 * @package App\Http\Controllers\Admin
 */
class AgentListController extends Controller
{
    public function index(Request $request){
        $user = $this->getLoginUser();
        $map = array();
        if(true==$request->has('username')){
            $map['username']=HttpFilter($request->input('username'));
        }

        $sql = User::query();
        if(true == $request->has('nickname')){
            $sql->where('nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        /*//判断数据权限
        if ($user['data_permission']==1){//当前为所有数据权限
            //条件
            $map['parent_id']=0;
            $sql->where($map);
        }else{

        }*/
        $map['parent_id']=$user['id'];
        $sql->where($map)->orWhere('id','=',$user['id']);
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->orderBy('created_at','asc')->paginate($limit)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['fee']=json_decode($value['fee'],true);
            $data[$key]['agentCount']=$this->getAgentCount($value['id']);
            $data[$key]['userCount']=$this->getAgentUserCount($value['id']);
            $data[$key]['groupBalance']=$this->getGroupBalance($value['id']);
        }
        return view('agentList.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit,'user'=>$user]);
    }

    public function getHqUserList(){
        $sql = HqUser::query();
        return $sql->leftJoin('user_account','user_account.user_id','=','user.user_id')
            ->select('user.user_id','user.agent_id','user_account.balance')->get();
    }
    /**
     * 根据代理id获取到下级代理的个数
     * @param $agentId
     * @return int
     */
    public function getAgentCount($agentId){
        return User::where('parent_id','=',$agentId)->count();
    }

    /**
     * 根据代理id获取到下级会员的个数
     * @param $agentId
     * @return int
     */
    public function getAgentUserCount($agentId){
        return HqUser::where('agent_id','=',$agentId)->count();
    }

    /**
     * 获取下级代理
     * @param $id
     * @param Request $request
     * @return Factory|Application|View
     */
    public function getAgentChildren($id,Request $request){
        $agentInfo = (int)$id?User::find((int)$id):[];
        $ancestors=explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'您没有权限操作','status'=>0];
        }
        $map = array();
        $map['parent_id']=$id;
        if (true==$request->has('username')){
            $map['username']=HttpFilter($request->input('username'));
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $sql = User::where($map);
        if (true==$request->has('nickname')){
            $sql->where('nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        $data = $sql->orderBy('created_at','asc')->paginate($limit)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['fee']=json_decode($value['fee'],true);
            $data[$key]['agentCount']=$this->getAgentCount($value['id']);
            $data[$key]['groupBalance']=$this->getGroupBalance($value['id']);
        }
        return view('agentList.list',['list'=>$data,'input'=>$request->all(),'user'=>Auth::user(),'limit'=>$limit]);
    }

    public function getGroupBalance($agentId){
        $agentList = User::get();
        $userList = $this->getHqUserList();
        $userMoney = $this->getAgentUserMoney($agentId,$userList);
        $info = $agentId?User::find($agentId):[];
        return $info['balance'] + $userMoney + $this->getRecursiveBalance($agentId,$agentList,$userList);
    }
    public function getAgentUserMoney($agentId,$userList){
        $arr = array();
        foreach ($userList as $key=>$value){
            if($agentId==$value['agent_id']){
                $arr[] = $userList[$key];
            }
        }
        return $this->getMoneyByUserList($arr);
    }

    public function getMoneyByUserList($userList){
        $money = 0;
        foreach ($userList as $key=>$value){
            $money = $money + $value['balance'];
        }
        return $money;
    }
    public function getAgentInfo($agentId,$agentList){
        foreach ($agentList as $key=>$value){
            if ($value['id']=$agentId){
                return $value['balance'];
                break;
            }
        }
    }

    public function getRecursiveBalance($agentId,$agentList,$userList){
        $money = 0;
        $children = $this->getAgentChildrenList($agentId,$agentList);
        if(count($children) > 0){
            foreach ($children as $key=>$value){
                $money = $money + $value['balance'] + $this->getRecursiveBalance($value['id'],$agentList,$userList) + $this->getAgentUserMoney($value['id'],$userList);
            }
        }
        return $money;
    }

    public function getAgentChildrenList($agentId,$agentList){
        $arr = array();
        foreach ($agentList as $key=>$value){
            if ($agentId==$value['parent_id']){
                $arr[] = $agentList[$key];
            }
        }
        return $arr;
    }

    /**
     * 获取当前登录用户
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getLoginUser()
    {
        return Auth::user();
    }

    /**
     * 停用启用
     * @param StoreRequest $request
     * @return array
     */
    public function changeStatus(StoreRequest $request){
        //获取数据
        $id = (int)$request->input('id');
        $agent = User::where('id','=',$id)->select('ancestors')->first();
        $idArr = explode(',',$agent['ancestors']);
        $bool = $this->whetherAffiliatedAgent($idArr);
        if (!$bool)
        {
            return ['msg'=>'您没有权限进行操作','status'=>0];
        }
        $status = (int)$request->input('status');
        $count = User::where('id',$id)->update(['status'=>$status]);
        if($count){
            return ['msg'=>'操作成功','status'=>1];
        }else{
            return ['msg'=>'操作失败','status'=>0];
        }
    }

    /**
     * 下级会员列表
     * @param $id
     * @param Request $request
     * @return Factory|Application|View
     */
    public function user($id,Request $request){
        $agentInfo = (int)$id?User::find((int)$id):[];
        $ancestors=explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'您没有权限操作','status'=>0];
        }
        $map = array();
        $map['agent_id']=$id;
        if(true==$request->has('account')){
            $map['account']=$request->input('account');
        }
        $user = HqUser::query();
        $sql = $user->leftJoin('agent_users','user.agent_id','=','agent_users.id')
            ->leftJoin('user_account','user.user_id','=','user_account.user_id')
            ->select('user.*','agent_users.nickname as agentName','user_account.balance')->where($map);
        if(true ==$request->has('nickname')){
            $sql->where('user.nickname','like','%'.$request->input('nickname').'%');
        }
        if (true==$request->has('limit'))
        {
            $limit = $request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->paginate($limit)->appends($request->all());
        foreach($data as $key=>&$value){
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['fee']=json_decode($data[$key]['fee'],true);
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('agentList.userList',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }

    /**
     * 效验账号是否存在
     * @param StoreRequest $request
     * @return array
     */
    public function accountUnique(StoreRequest $request)
    {
        $account = HttpFilter($request->input('account'));
        if (User::where('username','=',$account)->exists())
        {
            return ['msg'=>$account.'账号已存在','status'=>0];
        }
        return ['msg'=>'可用','status'=>1];
    }

    /**
     * 获取用户最近充值记录
     */
    public function getUserCzCord($userId){
        $data = Czrecord::where('user_id',$userId)->orderBy('creatime','desc')->first();
        return $data;
    }

    /**
     * 用户状态停用
     * @param StoreRequest $request
     * @return array
     */
    public function changeUserStatus(StoreRequest $request){
        $id = (int)$request->input('id');
        $userInfo = $id?HqUser::find($id):[];
        $agentInfo = $userInfo['agent_id']?User::find($userInfo['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[] = $agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'您没有权限操作','status'=>0];
        }
        $status = (int)$request->input('status');
        $count = HqUser::where('user_id',$id)->update(['is_over'=>$status]);
        if($count!==false){
            if ($userInfo['is_online']==1)
            {
                //重新生成token
                $token = $this->generateToken();
                $this->updateHqUserInfoToRedis($id,$token);
            }
            return ['msg'=>'操作成功','status'=>1];
        }else{
            return ['msg'=>'操作失败','status'=>0];
        }
    }

    public function updateHqUserInfoToRedis($id,$token){
        HqUser::where('user_id',$id)->update(['token'=>$token]);
        $info = $id?HqUser::find($id):[];
        $data['UserId']=$info['user_id'];
        $data['NickName']=$info['nickname'];
        $data['Account']=(int)$info['account'];
        $data['Token']=$info['token'];
        $data['BankName']=$info['bank_name'];
        $data['BankCard']=(int)$info['bank_card'];
        $data['DrawPwd']=$info['draw_pwd'];
        $data['LastIp']=$info['last_ip'];
        Redis::set('UserInfo_'.$id,json_encode($data));
    }

    /**随机token
     * @return string
     */
    public function generateToken(){
        $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for($i=0;$i<32;$i++)
        {
            $key .= $str{mt_rand(0,32)};
        }
        $timestamp = time();
        $tokenSalt = "huanqiu";//自定义的18~32字符串
        return md5($key . $timestamp . $tokenSalt);
    }
    /**
     * 修改会员密码界面
     */
    public function resetPwd($id){
        $userInfo = $id?HqUser::find($id):[];
        $agentInfo = $userInfo['agent_id']?User::find($userInfo['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'该会员的不属于你，操作失败','status'=>0];
        }
        return view('agentList.resetpwd',['id'=>$id]);
    }

    /**
     * 保存修改密码
     */
    public function savePwd(StoreRequest $request){
        $id = (int)$request->input('id');
        $userInfo = (int)$id?HqUser::find((int)$id):[];
        $agentInfo = $userInfo['agent_id']?User::find($userInfo['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'该会员的不属于你，操作失败','status'=>0];
        }
        $password = HttpFilter($request->input('password'));
        $newPwd = HttpFilter($request->input('newPwd'));
        if($password!=$newPwd){
            return ['msg'=>'两次密码不一致，请重新输入','status'=>0];
        }else{
            $result = HqUser::where('user_id',$id)->update(['password'=>md5($password)]);
            if($result!==false){
                return ['msg'=>'操作成功','status'=>1];
            }else{
                return ['msg'=>'操作失败','status'=>0];
            }
        }
    }

    /**
     * 会员账号编辑
     */
    public function userEdit($id){
        $user = (int)$id?HqUser::find((int)$id):[];
        $agentInfo = $user['agent_id']?User::find($user['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'该会员的不属于你，操作失败','status'=>0];
        }
        $user['fee'] = json_decode($user['fee'],true);
        $user['nnbets_fee'] = json_decode($user['nnbets_fee'],true);
        $user['lhbets_fee'] = json_decode($user['lhbets_fee'],true);
        $user['bjlbets_fee'] = json_decode($user['bjlbets_fee'],true);
        $user['a89bets_fee'] = json_decode($user['a89bets_fee'],true);
        $user['sgbets_fee'] = json_decode($user['sgbets_fee'],true);
        $agent = Auth::user();
        $agent['nnbets_fee'] = json_decode($agent['nnbets_fee'],true);
        $agent['lhbets_fee'] = json_decode($agent['lhbets_fee'],true);
        $agent['bjlbets_fee'] = json_decode($agent['bjlbets_fee'],true);
        $agent['a89bets_fee'] = json_decode($agent['a89bets_fee'],true);
        $agent['sgbets_fee'] = json_decode($agent['sgbets_fee'],true);
        return view('agentList.edit',['id'=>$id,'info'=>$user,'user'=>$agent]);
    }

    public function agentPasswordEdit($id){
        $agentInfo = (int)$id?User::find((int)$id):[];
        $agentInfo['ancestors']=explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($agentInfo['ancestors']);
        if (!$bool)
        {
            return ['msg'=>'您没有权限操作','status'=>0];
        }
        return view('agentList.resetAgentPwd',['id'=>$id]);
    }

    /**
     * 代理保存修改密码
     * @param StoreRequest $request
     * @return array
     */
    public function resetAgentPwd(StoreRequest $request){
        $id = (int)$request->input('id');
        $agentInfo = User::where('id','=',$id)->select('ancestors')->first();
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限','status'=>0];
        }
        $password = HttpFilter($request->input('password'));
        $newPwd = HttpFilter($request->input('newPwd'));
        if($password!=$newPwd){
            return ['msg'=>'两次密码不一致，请重新输入','status'=>0];
        }else{
            $result = User::where('id',$id)->update(['password'=>bcrypt($password)]);
            if ($result){
                return ['msg'=>'操作成功','status'=>1];
            }else{
                return ['msg'=>'操作失败','status'=>0];
            }
        }
    }

    /**
     * 效验会员是不是代理
     * @param $ancestors
     * @return bool
     */
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
     * 代理编辑页
     * @param $id
     * @return Factory|Application|View
     */
    public function agentEdit($id){
        $data = $id?User::find($id):[];
        $ancestors=explode(',',$data['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'您没有权限操作','status'=>0];
        }
        $data['fee']=json_decode($data['fee'],true);
        $data['limit']=json_decode($data['limit'],true);
        $data['bjlbets_fee'] = json_decode($data['bjlbets_fee'],true);
        $data['lhbets_fee'] = json_decode($data['lhbets_fee'],true);
        $data['nnbets_fee']=json_decode($data['nnbets_fee'],true);
        $data['sgbets_fee']=json_decode($data['sgbets_fee'],true);
        $data['a89bets_fee']=json_decode($data['a89bets_fee'],true);
        $user =Auth::user();
        $user['bjlbets_fee']=json_decode($user['bjlbets_fee'],true);
        $user['lhbets_fee']=json_decode($user['lhbets_fee'],true);
        $user['nnbets_fee']=json_decode($user['nnbets_fee'],true);
        $user['sgbets_fee'] = json_decode($user['sgbets_fee'],true);
        $user['a89bets_fee'] = json_decode($user['a89bets_fee'],true);
        return view('agentList.AgentEdit',['id'=>$id,'info'=>$data,'user'=>$user]);
    }

    public function saveAgentEdit(StoreRequest $request){
        $id = (int)$request->input('id');
        $agentInfo = User::where('id','=',$id)->select('ancestors')->first();
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'你没有权限进行操作','status'=>0];
        }
        $data = $request->all();
        $data['nickname']=HttpFilter($data['nickname']);
        unset($data['_token']);
        unset($data['id']);
        unset($data['proportion']);
        unset($data['fee']);
        unset($data['limit']);
        $data['remark']=HttpFilter($data['remark']);
        $agent = Auth::id()?User::find(Auth::id()):[];
        $bjl=json_decode($agent['bjlbets_fee'],true);//{"banker":"0.95","bankerPair":"11","player":"1","playerPair":"11","tie":"8"}
            if ($bjl['banker']<$data['bjlbets_fee']['banker'] || $data['bjlbets_fee']['banker']<0.9)
            {
                return ['msg'=>'赔率不能小于0.9','status'=>0];
            }
            if ($bjl['player']<$data['bjlbets_fee']['player'] || $data['bjlbets_fee']['player']<0.95)
            {
                return ['msg'=>'赔率错误不能小于0.95','status'=>0];
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
        $lh = json_decode($agent['lhbets_fee'],true);
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
        $nn = json_decode($agent['nnbets_fee'],true);
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
        $sg = json_decode($agent['sgbets_fee'],true);
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
        $a89 = json_decode($agent['a89bets_fee'],true);
        if ($a89['Equal']!=$data['a89bets_fee']['Equal'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($a89['SuperDouble']!=$data['a89bets_fee']['SuperDouble'])
        {
            return ['msg'=>'赔率错误','status'=>0];
        }
        if ($agent['baccarat']==1){
            $data['bjlbets_fee'] = json_encode($data['bjlbets_fee']);
        }
        else
        {
            unset($data['bjlbets_fee']);
        }
        if ($agent['dragon_tiger']==1){
            $data['lhbets_fee'] = json_encode($data['lhbets_fee']);
        }
        else
        {
            unset($data['lhbets_fee']);
        }
        if ($agent['niuniu']==1){
            $data['nnbets_fee']= json_encode($data['nnbets_fee']);
        }
        else
        {
            unset($data['nnbets_fee']);
        }
        if ($agent['sangong']==1){
            $data['sgbets_fee']=json_encode($data['sgbets_fee']);
        }
        else
        {
            unset($data['sgbets_fee']);
        }
        if ($agent['A89']==1){
            $data['a89bets_fee']=json_encode($data['a89bets_fee']);
        }
        else
        {
            unset($data['a89bets_fee']);
        }
        if (!empty($data['baccarat']))
        {
            if ($agent['baccarat']==1)
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
            if ($agent['dragon_tiger']==1)
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
            if ($agent['niuniu']==1)
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
            if ($agent['sangong']==1)
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
            if ($agent['A89']==1)
            {
                $data['A89']=1;
            }
            else
            {
                unset($data['A89']);
            }
        }
        $authUser = Auth::id()?User::find(Auth::id()):[];
        if ($authUser['is_allow']==1)
        {
            if(!empty($data['is_allow'])){
                $data['is_allow']=1;
            }
            else
            {
                $data['is_allow']=2;
            }
        }
        else
        {
            unset($data['is_allow']);
        }
        if ($authUser['is_allow_draw']==1)
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
        if (!empty($data['is_allow_password']))
        {
            if ($authUser['is_allow_password']==1)
            {
                $data['is_allow_password']=2;
            }
            else
            {
                $data['is_allow_password']=1;
            }
        }
        $count = User::where('id','=',$id)->update($data);
        if($count){
            return ['msg'=>'操作成功','status'=>1];
        }else{
            return ['msg'=>'操作失败','status'=>0];
        }
    }

    /**
     * 代理结构关系
     * @param $id
     * @return Factory|Application|View
     */
    public function getRelationalStruct($id){
        $info = $id?User::find($id):[];
        $ancestors = explode(',',$info['ancestors']);
        $ancestors[]=$info['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'您没有权限进行操作','status'=>0];
        }
        $arr = array();
        if ($info['parent_id']!=0){
            $data = explode(",",$info['ancestors']);
            unset($data[0]);
            foreach ($data as $key=>$value){
                $a = $value?User::find($value):[];
                $arr[] = $a;
            }
        }
        return view('agentList.agentRelation',['info'=>$info,'parent'=>$arr]);
    }

    public function getUserRelational($id){
        $info = $id?HqUser::find($id):[];
        $agentInfo = User::where('id','=',$info['agent_id'])->first();
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'你没有权限','status'=>0];
        }
        $info['creatime']=date('Y-m-d H:i:s',$info['creatime']);
        $arr = array();
        //获得上级代理
        $agent = $info['agent_id']?User::find($info['agent_id']):[];
        $arr[]=$agent;
        if ($agent['parent_id']!=0){
            $data = explode(',',$info['ancestors']);
            unset($data[0]);
            foreach ($data as $key=>$value){
                $a = $value?User::find($value):[];
                $arr[] = $a;
            }
        }
        return view('agentList.userRelation',['info'=>$info,'parent'=>$arr]);
    }
    /**
     * redis队列锁
     * @param $userId
     * @return bool
     */
    public function redissionLock($userId){
        $code=time().rand(100000,999999);
        //锁入列
        Redis::rPush('cz_agent_lock_'.$userId,$code);

        //锁出列
        $codes = Redis::LINDEX('cz_agent_lock_'.$userId,0);
        if ($code!=$codes){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 解锁
     * @param $userId
     */
    public function unRedissLock($userId)
    {
        Redis::del('cz_agent_lock_'.$userId);
    }
    /**
     * 代理提现充值
     * @param $id
     * @return Factory|Application|View
     */
    public function czEdit($id)
    {
        $data = $id?User::find($id):[];
        $data['ancestors']=explode(',',$data['ancestors']);
        $bool = $this->whetherAffiliatedAgent($data['ancestors']);
        if (!$bool)
        {
            return ['msg'=>'您没有权限操作','status'=>0];
        }
        $user = Auth::id()?User::find(Auth::id()):[];
        return view('agentList.cz',['info'=>$data,'balance'=>$user['balance'],'id'=>$id,'user'=>$user]);
    }
    /**
     * 插入代理流水
     * @param $agentId 代理id
     * @param $userId  用户id
     * @param $money   操作金额
     * @param $before  操作前金额
     * @param $after   操作后金额
     * @param $status  操作类型
     * @param $type    充值类型
     * @param $remark  备注
     * @return bool
     */
    public function insertAgentBillFlow($agentId,$userId,$money,$before,$after,$status,$type,$remark){
        $agentInfo = $agentId?User::find($agentId):[];
        $data['agent_id']=$agentId;
        $data['agent_name']=$agentInfo['nickname'];
        $data['user_id']=$userId;
        if ($userId!=0)
        {
            $user = $userId?HqUser::find($userId):[];
            $data['user_name']=$user['nickname'];
        }
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[] = $agentInfo['id'];
        if ($agentInfo['parent_id']==0)
        {
            $data['top_name']=$agentInfo['nickname'];
        }
        else
        {
            $sj = $agentInfo['parent_id']?User::find($agentInfo['parent_id']):[];
            $data['top_name']=$sj['nickname'];
        }
        $zs = $ancestors[1]?User::find($ancestors[1]):[];
        $data['fir_name']=$zs['nickname'];
        $data['money']=$money;
        $data['bet_before']=$before;
        $data['bet_after']=$after;
        $data['status']=$status;
        $data['type']=$type;
        $data['remark']=$remark;
        $data['creatime']=time();
        return AgentBill::insert($data);
    }
    public function agentCzSave(StoreRequest $request){
        $data = $request->all();
        $agentInfo = User::where('id','=',(int)$data['id'])->select('ancestors')->first();
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限进行操作','status'=>0];
        }
        if ($data['money']<0 || $data['money']==0)
        {
            return ['msg'=>'金额必须为正整数','status'=>0];
        }
        unset($data['_token']);
        if ($data['type']==1){
            DB::beginTransaction();
            $user = User::where('id','=',Auth::id())->lockForUpdate()->first();
            $agent = User::where('id','=',(int)$data['id'])->lockForUpdate()->first();
            if ($user['balance']<$data['money']*100){
                DB::rollBack();
                return ['msg'=>'余额不足','status'=>0];
            }else{
                $bool = $this->redissionLock($agent['id']);
                if ($bool){
                    try {
                        $result = DB::table('agent_users')->where('id','=',$agent['id'])->increment('balance',$data['money']*100);
                        if ($result){
                            $count = $this->insertAgentBillFlow($agent['id'],0,$data['money']*100,$agent['balance'],$agent['balance']+$data['money']*100,$data['type'],$data['payType'],$user['username'].'[给'.$agent['username'].'充值]');
                            if ($count){
                                $a = DB::table('agent_users')->where('id','=',$user['id'])->decrement('balance',$data['money']*100);
                                if ($a){
                                    $c = $this->insertAgentBillFlow($user['id'],0,-$data['money']*100,$user['balance'],$user['balance']-$data['money']*100,$data['type'],$data['payType'],$user['username'].'[给'.$agent['username'].'充值扣除]');
                                    if ($c){
                                        DB::commit();
                                        $this->unRedissLock($agent['id']);
                                        return ['msg'=>'操作成功','status'=>1];
                                    }else{
                                        DB::rollBack();
                                        $this->unRedissLock($agent['id']);
                                        return ['msg'=>'操作失败','status'=>0];
                                    }
                                }else{
                                    DB::rollBack();
                                    $this->unRedissLock($agent['id']);
                                    return ['msg'=>'操作失败','status'=>0];
                                }
                            }else{
                                DB::rollBack();
                                $this->unRedissLock($agent['id']);
                                return ['msg'=>'操作失败','status'=>0];
                            }
                        }else{
                            DB::rollBack();
                            $this->unRedissLock($agent['id']);
                            return ['msg'=>'操作失败！','status'=>0];
                        }
                    }catch (\Exception $exception){
                        DB::rollBack();
                        $this->unRedissLock($agent['id']);
                        return ['msg'=>'发生异常，请稍后再试','status'=>0];
                    }
                }else{
                    DB::rollBack();
                    return ['msg'=>'请忽频繁提交','status'=>0];
                }
            }
        }else{
            DB::beginTransaction();//开启事务
            $user = User::where('id','=',Auth::id())->lockForUpdate()->first();
            $agent = User::where('id','=',$data['id'])->lockForUpdate()->first();
            if ($agent['balance']<$data['money']*100){
                DB::rollBack();
                return ['msg'=>'余额不足','status'=>0];
            }else{
                $bool = $this->redissionLock($agent['id']);
                if ($bool){
                    try {
                        $result = DB::table('agent_users')->where('id','=',$agent['id'])->decrement('balance',$data['money']*100);
                        if ($result){
                            $count=$this->insertAgentBillFlow($agent['id'],0,-$data['money']*100,$agent['balance'],$agent['balance']-$data['money']*100,$data['type'],$data['payType'],$user['username'].'[对'.$agent['username'].'进行提现]');
                            if ($count){
                                $a = DB::table('agent_users')->where('id','=',$user['id'])->increment('balance',$data['money']*100);
                                if ($a){
                                    $n = $this->insertAgentBillFlow($user['id'],0,$data['money']*100,$user['balance'],$user['balance']+$data['money']*100,$data['type'],$data['payType'],$user['username'].'[对'.$agent['username'].'提现到账]');
                                    if ($n){
                                        DB::commit();
                                        $this->unRedissLock($agent['id']);
                                        return ['msg'=>'操作成功','status'=>1];
                                    }else{
                                        DB::rollBack();
                                        $this->unRedissLock($agent['id']);
                                        return ['msg'=>'操作失败','status'=>0];
                                    }
                                }else{
                                    DB::rollBack();
                                    $this->unRedissLock($agent['id']);
                                    return ['msg'=>'操作失败','status'=>0];
                                }
                            }else{
                                DB::rollBack();
                                $this->unRedissLock($agent['id']);
                                return ['msg'=>'操作失败','status'=>0];
                            }
                        }else{
                            DB::rollBack();
                            $this->unRedissLock($agent['id']);
                            return ['msg'=>'操作失败','status'=>0];
                        }
                    }catch (\Exception $e){
                        DB::rollBack();
                        $this->unRedissLock($agent['id']);
                        return ['msg'=>'操作异常','status'=>0];
                    }
                }else{
                    DB::rollBack();
                    return ['msg'=>'请忽频繁提交','status'=>0];
                }
            }
        }
    }

    public function userInfo()
    {
        return view('users.userinfo');
    }
}