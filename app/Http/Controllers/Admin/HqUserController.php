<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
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
            //$data[$key]['online']=$this->getUserOnline($value['user_id']);
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

    public function userUpdate(StoreRequest $request){
        $data = $request->all();
        $id = $data['id'];
        unset($data['_token']);
        unset($data['id']);
        $bjl['player']=(int)$data['bjlbets_fee']['player'] * 100;
        $bjl['playerPair']=(int)$data['bjlbets_fee']['playerPair'] * 100;
        $bjl['tie']=(int)$data['bjlbets_fee']['tie'] * 100;
        $bjl['banker']=(int)$data['bjlbets_fee']['banker'] *100;
        $bjl['bankerPair']=(int)$data['bjlbets_fee']['bankerPair'] * 100;
        $data['bjlbets_fee']=json_encode($bjl);
        $lh['dragon']=(int)$data['lhbets_fee']['dragon'] * 100;
        $lh['tie']=(int)$data['lhbets_fee']['tie'] *100;
        $lh['tiger']=(int)$data['lhbets_fee']['tiger']*100;
        $data['lhbets_fee']=json_encode($lh);
        $nn['Equal']=(int)$data['nnbets_fee']['Equal'] *100;
        $nn['Double']=(int)$data['nnbets_fee']['Double'] *100;
        $nn['SuperDouble']=(int)$data['nnbets_fee']['SuperDouble']*100;
        $data['nnbets_fee']=json_encode($nn);
        $sg['Equal']=(int)$data['sgbets_fee']['Equal']*100;
        $sg['Double']=(int)$data['sgbets_fee']['Double']*100;
        $sg['SuperDouble']=(int)$data['sgbets_fee']['SuperDouble']*100;
        $data['sgbets_fee']=json_encode($sg);
        $a89['Equal']=(int)$data['a89bets_fee']['Equal']*100;
        $a89['Double']=95;
        $a89['SuperDouble']=(int)$data['a89bets_fee']['SuperDouble']*100;
        $data['a89bets_fee']=json_encode($a89);
        if ($data['is_show']=='on'){
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
}