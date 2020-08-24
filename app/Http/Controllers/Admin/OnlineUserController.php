<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desk;
use App\Models\HqUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 在线用户
 * Class OnlineUserController
 * @package App\Http\Controllers\Admin
 */
class OnlineUserController extends Controller
{

    public function index(Request $request){
        $map = array();
        //获取所有代理id
        $idArr = array();
        $idArr[]=Auth::id();
        $agentIdData = User::whereRaw('FIND_IN_SET('.Auth::id().',ancestors)',true)->select('id')->get();
        foreach ($agentIdData as $key=>$datum)
        {
            $idArr[]=$datum['id'];
        }
        $map['user.is_online']=1;
        if(true==$request->has('username')){
            $map['agent_users.username']=HttpFilter($request->input('username'));
        }
        if(true==$request->has('account')){
            $map['user.account'] = HttpFilter($request->input('account'));
        }
        if(true==$request->has('deskId')){
            $map['user.desk_id']=(int)$request->input('deskId');
        }
        if(true==$request->has('online_type')){
            $map['user.online_type']=(int)$request->input('online_type');
        }
        $sql = HqUser::query();

        $data = $sql->leftJoin('agent_users','user.agent_id','=','agent_users.id')
            ->leftJoin('user_account','user.user_id','=','user_account.user_id')
            ->leftJoin('desk','desk.id','=','user.desk_id')
            ->select('user.*','agent_users.username','desk.desk_name','user_account.balance')->where($map)->whereIn('user.agent_id',$idArr)->orderBy('user.savetime','desc')->paginate(10)->appends($request->all());
        $countData = HqUser::query()->leftJoin('agent_users','user.agent_id','=','agent_users.id')
            ->leftJoin('user_account','user.user_id','=','user_account.user_id')
            ->leftJoin('desk','desk.id','=','user.desk_id')
            ->select('user.*','agent_users.username','desk.desk_name','user_account.balance')->where($map)->whereIn('user.agent_id',$idArr)->orderBy('user.savetime','desc')->get();
        foreach ($data as $key=>$value){
            //$url = "http://whois.pconline.com.cn/ipJson.jsp?ip=".$value['last_ip']."'&json=true";
            //$result = file_get_contents($url);
            //$result = iconv('gb2312','utf-8//IGNORE',$result);
            //$result = json_decode($result,true);
            //$data[$key]['address']=$result['addr'];
            $data[$key]['savetime']=date('Y-m-d H:i:s');
        }
        return view('online.list',['list'=>$data,'input'=>$request->all(),'desk'=>$this->getAllDeskList(),'onlineUserCount'=>count($countData),'money'=>$this->getOnlineUserMoney($countData)/100,'pc'=>$this->getOnlineCount($countData,1),'ios'=>$this->getOnlineCount($countData,2),'android'=>$this->getOnlineCount($countData,3),'h5'=>$this->getOnlineCount($countData,4)]);
    }

    /**
     * 获取所有台桌
     * @return Desk[]|Collection
     */
    public function getAllDeskList(){
        return Desk::where('status','=',0)->get();
    }

    /**
     * 获取在线用户金额
     * @param $data
     * @return int
     */
    public function getOnlineUserMoney($data){
        $money = 0;
        foreach ($data as $key=>$value){
            $money = $money + $value['balance'];
        }
        return $money;
    }

    public function getOnlineCount($data,$type){
        $count = 0;
        foreach ($data as $key=>$datum){
            if($datum['online_type']==$type){
                $count = $count + 1;
            }
        }
        return $count;
    }
}