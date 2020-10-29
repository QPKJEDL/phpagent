<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\AgentRoleUser;
use App\Models\AgentUserPhone;
use App\Models\User;
use App\Models\Verificat;
use Illuminate\Support\Facades\Redis;

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
        if ($info['userType']==1)
        {
            return ['msg'=>'当前代理不是线上代理','status'=>0];
        }
        return view('auth.register',['info'=>$info,'bool'=>AgentUserPhone::checkExistByAgentId($id)]);
    }

    /**
     * 代理激活验证码
     * @param StoreRequest $request
     * @return array
     */
    public function agentActSendSms(StoreRequest $request)
    {
        $mobile = HttpFilter($request->input('mobile'));
        if ($mobile=='' || $mobile == null)
        {
            return ['msg'=>'手机号不能为空','status'=>0];
        }
        $preg_phone = '/^1[3456789]\d{9}$/';
        if (!preg_match($preg_phone,$mobile))
        {
            return ['msg'=>'手机号格式不正确','status'=>0];
        }
        $ip= $request->ip();
        $code = mt_rand(100000,999999);
        $key = 'agent_act_';
        $bool = Verificat::ytxSend($mobile,$code,$ip,$key);
        if ($bool=="123")
        {
            return ['msg'=>'一分钟只能发送一条','status'=>0];
        }
        if ($bool=="1")
        {
            return ['msg'=>'发送失败','status'=>0];
        }
        return ['msg'=>'发送成功','status'=>1];
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
        $mobile = HttpFilter($request->input('phone_number'));
        if ($mobile=='' || $mobile == null)
        {
            return ['msg'=>'手机号不能为空','status'=>0];
        }
        $preg_phone = '/^1[34578]\d{9}$/ims';
        if (!preg_match($preg_phone,$mobile))
        {
            return ['msg'=>'手机号格式不正确','status'=>0];
        }
        $bool = AgentUserPhone::checkExistByAgentId($data['agent_id']);
        if ($bool==false){
            return ['msg'=>'当前账号已被激活','status'=>0];
        }else{
            $code = Redis::get('agent_act_'.$mobile);
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