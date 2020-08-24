<?php


namespace App\Http\Controllers\Online;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Czrecord;
use App\Models\HqUser;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnAgentListController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $map = array();
        if (true==$request->has('username')){
            $map['username']=HttpFilter($request->input('username'));
        }
        $sql = User::query();
        if(true == $request->has('nickname')){
            $sql->where('nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        if ($user['data_permission']==1){//当前为所有数据权限
            //条件
            $map['parent_id']=0;
            $sql->where($map);
        }else{
            $map['parent_id']=$user['id'];
            $sql->where($map)->orWhere('id','=',$user['id']);
        }
        if (true==$request->has('limit'))
        {
            $limit = (int)$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->orderBy('created_at','asc')->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$value)
        {
            $data[$key]['agentCount']=$this->getAgentCount($value['id']);
            $data[$key]['userCount']=$this->getAgentUserCount($value['id']);
            $data[$key]['groupBalance']=$this->getGroupBalance($value['id'],$value['balance']);
        }
        return view('onAgent.agentList.list',['list'=>$data,'input'=>$request->all(),'user'=>$user,'limit'=>$limit]);
    }

    public function edit($id=0)
    {
        $info = (int)$id?User::find((int)$id):[];
        $ancestors = explode(',',$info['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限','status'=>0];
        }
        return view('onAgent.agentList.edit',['info'=>$info,'id'=>$id,'user'=>Auth::user()]);
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
    public function update(StoreRequest $request)
    {
        $data = $request->all();
        $id = (int)$data['id'];
        $agentInfo = $id?User::find($id):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限','status'=>0];
        }
        unset($data['id']);
        unset($data['_token']);
        $data['limit']=json_encode($data['limit']);
        $count = User::where('id','=',$id)->update($data);
        if ($count){
            return ['msg'=>'操作成功','status'=>1];
        }else{
            return ['msg'=>'操作失败','status'=>0];
        }
    }

    public function showAgent($id,Request $request)
    {
        $agentInfo = (int)$id?User::find((int)$id):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限','status'=>0];
        }
        $map = array();
        $map['parent_id']=(int)$id;
        if (true==$request->has('username')){
            $map['username']=$request->input('username');
        }

        $sql = User::where($map);
        if (true==$request->has('nickname')){
            $sql->where('nickname','like','%'.$request->input('nickname').'%');
        }
        $data = $sql->orderBy('created_at','asc')->paginate(10)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['agentCount']=$this->getAgentCount($value['id']);
            $data[$key]['userCount']=$this->getAgentUserCount($value['id']);
            $data[$key]['groupBalance']=$this->getGroupBalance($value['id'],$value['balance']);
        }
        return view('onAgent.agentList.list',['list'=>$data,'input'=>$request->all(),'user'=>Auth::user()]);
    }

    /**
     * 未激活代理二维码
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function qrCodeShow($id)
    {
        $agentInfo = (int)$id?User::find((int)$id):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限','status'=>0];
        }
        return view('onAgent.agentList.qrocde',['id'=>$id]);
    }

    /**
     * 下级会员列表
     * @param $id
     * @param Request $request
     * @return Factory|Application|View
     */
    public function showUser($id,Request $request){
        $agentInfo = (int)$id?User::find((int)$id):[];
        $ancestors = explode(',',$agentInfo['ancestors']);
        $bool = $this->whetherAffiliatedAgent($ancestors);
        if (!$bool)
        {
            return ['msg'=>'没有权限','status'=>0];
        }
        $map = array();
        $map['agent_id']=(int)$id;
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
            $limit=$request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->paginate($limit)->appends($request->all());
        foreach($data as $key=>$value){
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('onAgent.agentList.userList',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }

    /**
     * 获取用户最近充值记录
     * @param $userId
     * @return Czrecord|\Illuminate\Database\Eloquent\Model|null
     */
    public function getUserCzCord($userId){
        $data = Czrecord::where('user_id',$userId)->orderBy('creatime','desc')->first();
        return $data;
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

    public function getGroupBalance($agentId,$balance){
        $agentList = User::where('userType','=',2)->get();
        $userList = $this->getHqUserList();
        $userMoney = $this->getAgentUserMoney($agentId,$userList);
        return $balance + $userMoney + $this->getRecursiveBalance($agentId,$agentList,$userList);
    }

    public function getHqUserList(){
        $sql = HqUser::query();
        return $sql->leftJoin('user_account','user_account.user_id','=','user.user_id')
            ->select('user.user_id','user.agent_id','user_account.balance')->get();
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
    public function getAgentInfo($agentId,$agentList){
        dump($agentList);
        $money = 0;
        foreach ($agentList as $key=>$value){
            if ($value['id']=$agentId){
                dump($value['balance']);
                $money= $value['balance'];
                break;
            }
        }
        return $money;
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

    public function getMoneyByUserList($userList){
        $money = 0;
        foreach ($userList as $key=>$value){
            $money = $money + $value['balance'];
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
}