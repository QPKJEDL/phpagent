<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\AgentBill;
use App\Models\Billflow;
use App\Models\Czrecord;
use App\Models\HqUser;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use mysql_xdevapi\Exception;

class HqUserController extends Controller
{
    public function index(Request $request){
        $user = Auth::user();
        $map = array();
        $map['agent_id']=$user['id'];
        if(true==$request->has('account')){
            $map['account']=HttpFilter($request->input('account'));
        }
        $sql = HqUser::query();
        $sql->leftJoin('agent_users','user.agent_id','=','agent_users.id')
            ->leftJoin('user_account','user.user_id','=','user_account.user_id')
            ->select('user.*','agent_users.nickname as agentName','user_account.balance')->where($map);
        if(true ==$request->has('nickname')){
            $sql->where('user.nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->paginate($limit)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['fee']=json_decode($value['fee'],true);
            $data[$key]['creatime']=date('Y-m-d H:i:s');
        }
        return view('agentList.userList',['limit'=>$limit,'list'=>$data,'input'=>$request->all()]);
    }

    /**
     * 效验会员账号是否存在
     * @param StoreRequest $request
     * @return array
     */
    public function checkAccountUnique(StoreRequest $request)
    {
        $account = HttpFilter($request->input('account'));
        if (HqUser::where('account','=',$account)->exists())
        {
            return ['msg'=>$account.'账号已存在','status'=>0];
        }
        return ['msg'=>'可用','status'=>1];
    }

    /**
     * 根据userId来检查用户是否存在
     * @param $userId
     * @return mixed
     */
    public function getUserOnline($userId){
        return Redis::get('isonline_'.$userId);
    }

     /**
     * 获取用户最近充值记录
     */
    public function getUserCzCord($userId){
        $data = Czrecord::where('user_id',$userId)->orderBy('creatime','desc')->first();
        return $data;
    }

    /**
     * 会员充值提现页面
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function czCord($userId){
        $data = $userId?HqUser::find($userId):[];
        $agentInfo = $data['agent_id']?User::find($data['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'该会员的不属于你，操作失败','status'=>0];
        }
        $userAccount = UserAccount::getUserAccountInfo($userId);
        return view('hquser.edit',['user'=>Auth::user(),'info'=>$data,'id'=>$userId,'balance'=>$userAccount['balance']]);
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
    public function czSave(StoreRequest $request){
        $data = $request->all();
        $userInfo = (int)$data['id']?HqUser::find((int)$data['id']):[];
        $agentInfo = $userInfo['agent_id']?User::find($userInfo['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'你没有权限','status'=>0];
        }
        if ($data['money']<0 || $data['money']==0)
        {
            return ['msg'=>'金额异常','status'=>0];
        }
        if ((int)$data['type']==1){
            DB::beginTransaction();//开启事务
            $userAccount = UserAccount::getUserAccountInfo((int)$data['id']);
            $agent = User::getUserInfo(Auth::user()['username']);
            if ($agent['balance']<(int)$data['money']*100){
                DB::rollBack();
                return ['msg'=>'余额不足，不能进行充值','status'=>0];
            }else{
                $bool = $this->redissionLock((int)$data['id']);
                if ((int)$data['money']<0 && (int)$data['money']==0)
                {
                    $this->unRedissLock((int)$data['id']);
                    return ['msg'=>'金额输入异常','status'=>0];
                }
                if ($bool){
                    try {
                        $result = DB::table('user_account')->where('user_id','=',(int)$data['id'])->increment('balance',(int)$data['money']*100);
                        if ($result){
                            $count = $this->insertUserBillflow((int)$data['id'],(int)$data['money']*100,(int)$userAccount['balance'],(int)$userAccount['balance']+(int)$data['money']*100,1,$data['payType'],Auth::user()['username'].'[代理代充]');
                            if ($count){
                                $kc = DB::table('agent_users')->where('id','=',(int)$agent['id'])->decrement('balance',(int)$data['money']*100);
                                if ($kc){
                                    DB::commit();
                                    $this->unRedissLock((int)$data['id']);
                                    $this->getCzAndDrawPostMessage($data['id'],1,$data['money']*100);
                                    return ['msg'=>'操作成功！','status'=>1];
                                }else{
                                    DB::rollBack();
                                    $this->unRedissLock((int)$data['id']);
                                    return ['msg'=>'操作失败','status'=>0];
                                }
                            }else{
                                DB::rollBack();
                                $this->unRedissLock((int)$data['id']);
                                return ['msg'=>'操作失败','status'=>0];
                            }
                        }else{
                            DB::rollBack();
                            $this->unRedissLock((int)$data['id']);
                            return ['msg'=>'操作异常，请稍后再试','status'=>0];
                        }
                    }catch (Exception $e){
                        DB::rollBack();
                        $this->unRedissLock((int)$data['id']);
                        return ['msg'=>'操作异常！请稍后再试','status'=>0];
                    }
                }else{
                    DB::rollBack();
                    return ['msg'=>'请忽频繁提交','status'=>0];
                }
            }
        }else{
            DB::beginTransaction();//开启事务
            $userAccount = UserAccount::getUserAccountInfo((int)$data['id']);
            $agent = User::getUserInfo(Auth::user()['username']);
            if ((int)$userAccount['balance']<(int)$data['money']*100){
                return ['msg'=>'余额不足，不能提现','status'=>0];
            }else{
                $bool = $this->redissionLock((int)$data['id']);
                if ((int)$data['money']<0 && (int)$data['money']==0)
                {
                    $this->unRedissLock((int)$data['id']);
                    return ['msg'=>'金额输入异常','status'=>0];
                }
                if ($bool){
                    try {
                        $result = DB::table('user_account')->where('user_id','=',(int)$data['id'])->decrement('balance',(int)$data['money']*100);
                        if ($result){
                            $count = $this->insertUserBillflow($data['id'],$data['money']*100,$userAccount['balance'],$userAccount['balance']-$data['money']*100,3,0,Auth::user()['username'].'代理代扣');
                            if ($count){
                                $add =  DB::table('agent_users')->where('id','=',$agent['id'])->increment('balance',(int)$data['money']*100);
                                if ($add){
                                    DB::commit();
                                    $this->unRedissLock((int)$data['id']);
                                    $this->getCzAndDrawPostMessage($data['id'],2,$data['money']*100);
                                    return ['msg'=>'操作成功','status'=>1];
                                }else{
                                    DB::rollBack();
                                    $this->unRedissLock((int)$data['id']);
                                    return ['msg'=>'操作失败','status'=>0];
                                }
                            }else{
                                DB::rollBack();
                                $this->unRedissLock((int)$data['id']);
                                return ['msg'=>'操作失败','status'=>0];
                            }
                        }else{
                            DB::rollBack();
                            $this->unRedissLock((int)$data['id']);
                            return ['msg'=>'操作失败','status'=>0];
                        }
                    }catch (Exception $e){
                        DB::rollBack();
                        $this->unRedissLock((int)$data['id']);
                        return ['msg'=>'操作异常，请稍后再试','status'=>0];
                    }
                }else{
                    DB::rollBack();
                    $this->unRedissLock((int)$data['id']);
                    return ['msg'=>'请忽频繁提交','status'=>0];
                }
            }
        }
    }

    /**
     * 充值提现推送
     * @param $userId
     * @param $type
     * @param $balance
     */
    public function getCzAndDrawPostMessage($userId,$type,$balance)
    {
        $data['uid']=(int)$userId;
        $data['appid']=(int)1;
        $content = array();
        $content['Cmd']=(int)31;
        $user = UserAccount::where('user_id','=',$userId)->first();
        $content['Money']=(float)$balance/100;
        $content['Balance']=(float)$user['balance']/100;
        $content['Type']=(int)$type;
        $data['content']=json_encode($content);
        $url = Redis::get('hq_admin_push_url');
        $this->https_post_kf($url,$data);
    }

    //http 请求
    private function https_post_kf($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //curl_setopt($curl, CURLOPT_USERAGENT, "Dalvik/1.6.0 (Linux; U; Android 4.1.2; DROID RAZR HD Build/9.8.1Q-62_VQW_MR-2)");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }
    /**
     * redis队列锁
     * @param $userId
     * @return bool
     */
    public function redissionLock($userId){
        $code=time().rand(100000,999999);
        //锁入列
        Redis::rPush('cz_user_lock_'.$userId,$code);

        //锁出列
        $codes = Redis::LINDEX('cz_user_lock_'.$userId,0);
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
        Redis::del('cz_user_lock_'.(int)$userId);
    }
    /**无缓存的唯一订单号
     * @return string
     */
    public function getrequestId(){
        @date_default_timezone_set("PRC");
        return date("YmdHis").rand(11111111,99999999);
    }

    /**
     * 插入流水
     * @param $userId 操作用户
     * @param $money  金额
     * @param $before 操作前金额
     * @param $after  操作后金额
     * @param $status
     * @param $payType
     * @param $remark 备注
     * @return bool
     */
    public function insertUserBillflow($userId,$money,$before,$after,$status,$payType,$remark){
        $data['user_id']=(int)$userId;
        $user = $userId?HqUser::find($userId):[];
        $data['nickname']=$user['nickname'];
        $sj = $this->getAgentInfoById($user['agent_id']);
        $data['agent_name']=$sj['nickname'];
        $ancestors = explode(',',$sj['ancestors']);
        $ancestors[] = $sj['id'];
        $zs = $this->getAgentInfoById($ancestors[1]);
        $data['fir_name']=$zs['nickname'];
        $data['order_sn']=$this->getrequestId();
        $data['score']=(int)$money;
        $data['bet_before']=(int)$before;
        $data['bet_after']=(int)$after;
        $data['status']=(int)$status;
        $data['pay_type']=(int)$payType;
        $data['remark']=HttpFilter($remark);
        $data['creatime']=time();
        $data['create_by']=Auth::user()['username'];
        $tableName = date('Ymd',time());
        $bill = new Billflow();
        $bill->setTable('user_billflow_'.$tableName);
        return $bill->insert($data);
    }

    /**
     * 根据代理id获取信息
     * @param $agentId
     * @return User|mixed
     */
    public function getAgentInfoById($agentId)
    {
        $info = $agentId?User::find($agentId):[];
        return $info;
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
        $data['agent_id']=(int)$agentId;
        $data['agent_name']=$agentInfo['nickname'];
        $data['user_id']=(int)$userId;
        $user = $userId?HqUser::find($userId):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[] = $agentInfo['id'];
        $zs = $ancestors[1]?User::find($ancestors[1]):[];
        $data['user_name']=$user['nickname'];
        if ($agentInfo['parent_id']==0)
        {
            $data['top_name']=$agentInfo['nickname'];
        }
        else
        {
            $sj = $agentInfo['parent_id']?User::find($agentInfo['parent_id']):[];
            $data['top_name']=$sj['nickname'];
        }
        $data['fir_name']=$zs['nickname'];
        $data['money']=(int)$money;
        $data['bet_before']=(int)$before;
        $data['bet_after']=(int)$after;
        $data['status']=(int)$status;
        $data['type']=(int)$type;
        $data['remark']=HttpFilter($remark);
        $data['creatime']=time();
        return AgentBill::insert($data);
    }
    /**
     * 会员编辑保存
     * @param StoreRequest $request
     * @return array
     */
    public function userUpdate(StoreRequest $request){
        $data = $request->all();
        $id = (int)$data['id'];
        $userInfo = $id?HqUser::find($id):[];
        $agentInfo = $userInfo['agent_id']?User::find($userInfo['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $ancestors[]=$agentInfo['id'];
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'该会员的不属于你，操作失败','status'=>0];
        }
        unset($data['_token']);
        unset($data['id']);
        if ($userInfo['user_type']==1)
        {
            $agent = Auth::id()?User::find(Auth::id()):[];
            $bjl=json_decode($agent['bjlbets_fee'],true);//{"banker":"0.95","bankerPair":"11","player":"1","playerPair":"11","tie":"8"}
            if ($bjl['banker']<$data['bjlbets_fee']['banker'] || $data['bjlbets_fee']['banker']<0.9)
            {
                return ['msg'=>'赔率错误不能低于0.9','status'=>0];
            }
            if ($bjl['player']<$data['bjlbets_fee']['player'] || $data['bjlbets_fee']['player']<=0.95)
            {
                return ['msg'=>'赔率错误不能低于0.95','status'=>0];
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
            $bjl['player']=intval($data['bjlbets_fee']['player'] * 100);
            $bjl['playerPair']=intval($data['bjlbets_fee']['playerPair'] * 100);
            $bjl['tie']=intval($data['bjlbets_fee']['tie'] * 100);
            $bjl['banker']=intval($data['bjlbets_fee']['banker'] *100);
            $bjl['bankerPair']=intval($data['bjlbets_fee']['bankerPair'] * 100);
            $data['bjlbets_fee']=json_encode($bjl);
            $lh['dragon']=intval($data['lhbets_fee']['dragon'] * 100);
            $lh['tie']=intval($data['lhbets_fee']['tie'] *100);
            $lh['tiger']=intval($data['lhbets_fee']['tiger']*100);
            $data['lhbets_fee']=json_encode($lh);
            $nn['Equal']=intval($data['nnbets_fee']['Equal'] *100);
            $nn['Double']=intval($data['nnbets_fee']['Double'] *100);
            $nn['SuperDouble']=intval($data['nnbets_fee']['SuperDouble']*100);
            $data['nnbets_fee']=json_encode($nn);
            $sg['Equal']=intval($data['sgbets_fee']['Equal']*100);
            $sg['Double']=intval($data['sgbets_fee']['Double']*100);
            $sg['SuperDouble']=intval($data['sgbets_fee']['SuperDouble']*100);
            $data['sgbets_fee']=json_encode($sg);
            $a89['Equal']=intval($data['a89bets_fee']['Equal']*100);
            $a89['Double']=95;
            $a89['SuperDouble']=intval($data['a89bets_fee']['SuperDouble']*100);
            $data['a89bets_fee']=json_encode($a89);
        }
        if (!empty($data['is_show'])){
            $data['is_show']=1;
        }else{
            $data['is_show']=0;
        }
        $count = HqUser::where('user_id','=',$id)->update($data);
        if ($count!==false){
            return ['msg'=>'操作成功','status'=>1];
        }else{
            return ['msg'=>'操作失败','status'=>0];
        }
    }

    /**
     * 会员关系结构
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function userRelation($id)
    {
        $user = (int)$id?HqUser::find((int)$id):[];
        $agentInfo = $user['agent_id']?User::find($user['agent_id']):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        unset($ancestors[0]);
        $ancestors[]=$agentInfo['id'];
        $data = User::query()->whereIn('id',$ancestors)->get();
        return view('hquser.userRelation',['info'=>$user,'parent'=>$data]);
    }
}