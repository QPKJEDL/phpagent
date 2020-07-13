<?php


namespace App\Http\Controllers\Online;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 新增代理
 * Class OnAddAgentController
 * @package App\Http\Controllers\Online
 */
class OnAddAgentController extends Controller
{
    public function index(Request $request)
    {
        return view('onAgent.addAgent.list',['user'=>Auth::user()]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->all();
        if ($data['password']==$data['pwd'])
        {
            unset($data['_token']);
            unset($data['pwd']);
            $data['userType']=2;
            $data['is_act']=0;
            $data['ancestors']=$this->getUserAncestors($data['parent_id']);
            $data['password']=bcrypt($data['password']);
            $data['limit']=json_encode($data['limit']);
            $data['created_at']=date('Y-m-d H:i:s',time());
            $count = User::insertGetId($data);
            if($count){
                return ['msg'=>'操作成功！','status'=>1,'agent_id'=>$count];
            }else{
                return ['msg'=>'操作失败！','status'=>0];
            }
        }else{
            return ['msg'=>'两次密码不一样','status'=>0];
        }
    }

    public function getUserAncestors($parentId){
        $info = $parentId?User::find($parentId):[];
        return $info['parent_id'].','.$parentId;
    }
}