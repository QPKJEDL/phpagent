<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Czrecord;
use App\Models\HqUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        //判断数据权限
        if ($user['data_permission']==1){//当前为所有数据权限
            //条件
            $map['parent_id']=0;
        }else if ($user['data_permission']==2){
            //条件
            $map['parent_id']=$user['id'];
        }
        $sql = User::query();
        $sql->where($map);
        if(true == $request->has('nickname')){
            $sql->Where('nickname','like','%'.$request->input('nickname').'%');
        }
        $data = $sql->paginate(10)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['fee']=json_decode($value['fee'],true);
        }
        return view('agentList.list',['list'=>$data,'input'=>$request->all()]);
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
            $data[$key]['creatime']=date('Y-m-d H:m:s',$value['creatime']);
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
            return ['msg'=>'操作成功','status'=>1];
        }else{
            return ['msg'=>'操作失败','status'=>0];
        }
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
            if($request){
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
}