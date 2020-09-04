<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\HqUser;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\Verificat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * 会员自主注册
 * Class HqUserRegisterController
 * @package App\Http\Controllers\Auth
 */
class HqUserRegisterController extends Controller
{
    public function userRegister($id){
        $info = $id?User::find($id):[];
        return view('auth.userRegister',['info'=>$info['nickname'],'id'=>$id]);
    }

    /**
     * 会员注册保存
     * @param StoreRequest $request
     * @return array
     */
    public function userSave(StoreRequest $request)
    {
        $data = $request->all();
        $account = HttpFilter($data['account']);
        if (!preg_match('/^1(3|4|5|7|8)\d{9}$/',$account))
        {
            return ['msg'=>'手机号格式错误','status'=>0];
        }
        unset($data['_token']);
        if (HqUser::where('mobile','=',$account)->exists()){
            return ['msg'=>'手机号已存在','status'=>0];
        }else{
            $code = Redis::get('user_register_'.$account);
            if (HttpFilter($data['code'])!=$code){
                return ['msg'=>'验证码不正确','status'=>0];
            }else{
                $info = (int)$data['agent_id']?User::find((int)$data['agent_id']):[];
                unset($data['code']);
                $data['mobile']=$account;
                $data['account']=$this->checkAccount();
                $data['password']=md5(HttpFilter($data['password']));
                $data['reg_ip']=$request->ip();
                $data['nnbets_fee']=$info['nnbets_fee'];
                $data['lhbets_fee']=$info['lhbets_fee'];
                $data['bjlbets_fee']=$info['bjlbets_fee'];
                $data['sgbets_fee']=$info['sgbets_fee'];
                $data['a89bets_fee']=$info['a89bets_fee'];
                $data['limit']=$info['limit'];
                $data['creatime']=time();
                $data['remark']='会员扫码线上代理码注册';
                $data['user_type']=2;
                $count = HqUser::insertGetId($data);
                if ($count){
                    $account = array();
                    $account['user_id']=$count;
                    $account['balance']=0;
                    $account['tol_recharge']=0;
                    $account['drawMoney']=0;
                    $account['creatime']=time();
                    $num = UserAccount::insert($account);
                    if ($num){
                        return ['msg'=>'注册成功','status'=>1,'account'=>HttpFilter($data['account'])];
                    }else{
                        return ['msg'=>'操作失败','status'=>0];
                    }
                }else{
                    return ['msg'=>'操作失败','status'=>0];
                }
            }
        }
    }

    /**
     * 获取随机账号
     * @return string
     */
    public function getAccount()
    {
        $string='';
        for($i = 1; $i <= 9; $i++){
            $string.=rand(0,9);
        }
        return $string;
    }

    /**
     * 效验账号是否存在并返回
     * @return string
     */
    public function checkAccount()
    {
        $account = $this->getAccount();//获取账号
        while (HqUser::where('account','=',$account)->exists()){
            $account=$this->getAccount();
        }
        return $account;
    }

    /**
     * 会员发送验证码
     * @param StoreRequest $request
     * @return array
     */
    public function sendSms(StoreRequest $request)
    {
        $mobile = HttpFilter($request->input('mobile'));
        if ($mobile=='' || $mobile == null)
        {
            return ['msg'=>'手机号不能为空','status'=>0];
        }
        $preg_phone = '/^1[34578]\d{9}$/ims';
        if (!preg_match($preg_phone,$mobile))
        {
            return ['msg'=>'手机号格式不正确','status'=>0];
        }
        $ip= $request->ip();
        $code = mt_rand(100000,999999);
        $key = 'user_register_';
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
}