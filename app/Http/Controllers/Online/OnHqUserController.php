<?php


namespace App\Http\Controllers\Online;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Czrecord;
use App\Models\HqUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnHqUserController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $map['user.agent_id']=Auth::id();
        if(true==$request->has('account')){
            $map['user.account']=HttpFilter($request->input('account'));
        }
        $user = HqUser::query();
        $sql = $user->leftJoin('agent_users','user.agent_id','=','agent_users.id')
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
            $limit = config('admin.limit');
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

    public function update(StoreRequest $request)
    {
        $data = $request->all();
        dump($data);
    }
}