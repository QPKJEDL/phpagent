<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\AgentRoleUser;
use App\Models\AgentUserPhone;
use App\Models\User;

/**
 * 代理激活
 * Class OnAgentActController
 * @package App\Http\Controllers\Auth
 */
class OnAgentActController extends Controller
{
    /**
     * 激活页面
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function actAgent($id)
    {
        $info = $id?User::find($id):[];
        return view('auth.register',['info'=>$info,'bool'=>AgentUserPhone::checkExistByAgentId($id)]);
    }

    /**
     * 代理激活
     * @param StoreRequest $request
     * @return array
     */
    public function actSave(StoreRequest $request)
    {
        $data = $request->all();
        unset($data['_token']);
        $bool = AgentUserPhone::checkExistByAgentId($data['agent_id']);
        if ($bool==false){
            return ['msg'=>'当前账号已被激活','status'=>0];
        }else{
            $code = '111';
            //获取当前信息
            $info = $data['agent_id']?User::find($data['agent_id']):[];
            if ($data['code']==$code){
                unset($data['code']);
                $count = AgentUserPhone::insert($data);
                if ($count){
                    User::where('id','=',$data['agent_id'])->update(['is_act'=>1]);
                    return ['msg'=>'激活成功','status'=>1];
                }else{
                    return ['msg'=>'操作失败','status'=>0];
                }
            }else{
                return ['msg'=>'验证码错误','status'=>0];
            }
        }
    }
}