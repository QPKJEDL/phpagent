<?php


namespace App\Http\Controllers\Online;


use App\Http\Controllers\Controller;
use App\Models\Czrecord;
use App\Models\HqUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnHqUserController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $map['agent_id']=Auth::id();
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
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['creatime']=date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('onAgent.agentList.userList',['list'=>$data,'input'=>$request->all()]);
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
}