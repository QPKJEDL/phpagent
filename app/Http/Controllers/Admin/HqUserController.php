<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Czrecord;
use App\Models\HqUser;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class HqUserController extends Controller
{
    public function index(Request $request){
        $user = Auth::user();
        $map = array();
        $map['agent_id']=$user['id'];
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
        foreach($data as $key=>$value){
            $data[$key]['online']=$this->getUserOnline($value['user_id']);
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['fee']=json_decode($value['fee'],true);
            $data[$key]['creatime']=date('Y-m-d H:i:s');
        }
        return view('agentList.userList',['list'=>$data,'input'=>$request->all()]);
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
     */
    public function czCord($userId){
        $data = $userId?HqUser::find($userId):[];
        $userAccount = UserAccount::getUserAccountInfo($userId);
        return view('hquser.edit',['user'=>Auth::user(),'info'=>$data,'id'=>$userId,'balance'=>$userAccount['balance']]);
    }
}