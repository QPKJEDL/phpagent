<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Czrecord;
use App\Models\HqUser;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $map['username']=$request->input('username');
        }

        $sql = User::query();
        //判断数据权限
        if ($user['data_permission']==1){//当前为所有数据权限
            //条件
            $map['parent_id']=0;
            $sql->where($map);
        }else{
            $map['parent_id']=$user['id'];
            $sql->where($map)->orWhere('id','=',$user['id']);
        }
        if(true == $request->has('nickname')){
            $sql->where('nickname','like','%'.$request->input('nickname').'%');
        }
        $data = $sql->orderBy('created_at','asc')->paginate(10)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['fee']=json_decode($value['fee'],true);
            $data[$key]['agentCount']=$this->getAgentCount($value['id']);
            $data[$key]['userCount']=$this->getAgentUserCount($value['id']);
            $data[$key]['groupBalance']=$this->getGroupBalance($value['id']);
        }
        return view('agentList.list',['list'=>$data,'input'=>$request->all(),'user'=>$user]);
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
        $map = array();
        $map['parent_id']=$id;
        if (true==$request->has('username')){
            $map['username']=$request->input('username');
        }

        $sql = User::where($map);
        if (true==$request->has('nickname')){
            $sql->where('nickname','like','%'.$request->input('nickname').'%');
        }
        $data = $sql->orderBy('created_at','asc')->paginate(10)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['fee']=json_decode($value['fee'],true);
            $data[$key]['agentCount']=$this->getAgentCount($value['id']);
            $data[$key]['groupBalance']=$this->getGroupBalance($value['id']);
        }
        return view('agentList.list',['list'=>$data,'input'=>$request->all(),'user'=>Auth::user()]);
    }

    public function getGroupBalance($agentId){
        $agentList = User::get();
        $userList = $this->getHqUserList();
        $userMoney = $this->getAgentUserMoney($agentId,$userList);
        $info = $this->getAgentInfo($agentId,$agentList);
        return $info + $userMoney + $this->getRecursiveBalance($agentId,$agentList,$userList);
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
                return $agentList[$key]['balance'];
                continue;
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
     */
    public function changeStatus(StoreRequest $request){
        //获取数据
        $id = $request->input('id');
        $status = $request->input('status');
        $count = User::where('id',$id)->update(['status'=>$status]);
        if($count){
            return ['msg'=>'操作成功','status'=>1];
        }else{
            return ['msg'=>'操作失败','status'=>0];
        }
    }

    /**
     * 下级会员列表
     */
    public function user($id,Request $request){
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
        $data = $sql->paginate(10)->appends($request->all());
        foreach($data as $key=>&$value){
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['fee']=json_decode($data[$key]['fee'],true);
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        //dump($data);
        return view('agentList.userList',['list'=>$data,'input'=>$request->all()]);
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
     */
    public function changeUserStatus(StoreRequest $request){
        $id = $request->input('id');
        $status = $request->input('status');
        $count = HqUser::where('user_id',$id)->update(['is_over'=>$status]);
        if($count!==false){
            //重新生成token
            $token = $this->generateToken();
            $this->updateHqUserInfoToRedis($id,$token);
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
        return view('agentList.resetpwd',['id'=>$id]);
    }

    /**
     * 保存修改密码
     */
    public function savePwd(StoreRequest $request){
        $id = $request->input('id');
        $password = $request->input('password');
        $newPwd = $request->input('newPwd');
        if($password!=$newPwd){
            return ['msg'=>'两次密码不一致，请重新输入','status'=>0];
        }else{
            $result = HqUser::where('user_id',$id)->update(['password'=>md5($password)]);
            if($result){
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
        $user = $id?HqUser::find($id):[];
        $user['fee'] = json_decode($user['fee'],true);
        return view('agentList.edit',['id'=>$id,'info'=>$user]);
    }

    public function agentPasswordEdit($id){
        return view('agentList.resetAgentPwd',['id'=>$id]);
    }

    /**
     * 代理保存修改密码
     * @param StoreRequest $request
     * @return array
     */
    public function resetAgentPwd(StoreRequest $request){
        $id = $request->input('id');
        $password = $request->input('password');
        $newPwd = $request->input('newPwd');
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
     * 代理编辑页
     * @param $id
     * @return Factory|Application|View
     */
    public function agentEdit($id){
        $data = $id?User::find($id):[];
        $data['fee']=json_decode($data['fee'],true);
        return view('agentList.AgentEdit',['id'=>$id,'info'=>$data]);
    }

    public function saveAgentEdit(StoreRequest $request){
        $id = $request->input('id');
        dump($request->all());
    }
}