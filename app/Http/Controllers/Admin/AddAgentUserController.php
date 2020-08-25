<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\AgentRoleUser;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AddAgentUserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user['fee']=json_decode($user['fee'],true);
        $game = Game::getGameList();
        return view('addAgent.list',['user'=>$user,'game'=>$game]);
    }

    /**
     * 效验账号是否存在
     */
    public function checkUnique(StoreRequest $request)
    {
        //获取数据
        $userName = HttpFilter($request->input('userName'));
        $user = User::where("username",'=',$userName)->first();
        if($user){
            return ['msg'=>'该账户已存在','status'=>1];
        }else{
            return ['msg'=>'该账户可以使用','status'=>0];
        }
    }


    public function store(StoreRequest $request){
        $data = $request->all();
        $data['parent_id']=Auth::id();
        unset($data['_token']);
        unset($data['pwd']);
        $data['userType']=1;
        $data['password']=bcrypt(HttpFilter($data['password']));
        $data['fee']=json_encode($data['fee']);
        $data['limit']=json_encode($data['limit']);
        $data['bjlbets_fee'] = json_encode($data['bjlbets_fee']);
        $data['lhbets_fee'] = json_encode($data['lhbets_fee']);
        $data['nnbets_fee']= json_encode($data['nnbets_fee']);
        $data['sgbets_fee']=json_encode($data['sgbets_fee']);
        $data['a89bets_fee']=json_encode($data['a89bets_fee']);
        $data['ancestors']= $this->getUserAncestors(Auth::id());
        $data['created_at']=date('Y-m-d H:i:s',time());
        if (!empty($data['is_allow']))
        {
             $data['is_allow']=1;
        }else{
            $data['is_allow']=0;
        }
        $count = User::insertGetId($data);
        if($count){
            AgentRoleUser::insert(['user_id'=>$count,'role_id'=>38]);
            return ['msg'=>'操作成功！','status'=>1];
        }else{
            return ['msg'=>'操作失败！','status'=>0];
        }
    }

    public function getUserAncestors($parentId){
        $info = $parentId?User::find($parentId):[];
        return $info['parent_id'].','.$parentId;
    }
}