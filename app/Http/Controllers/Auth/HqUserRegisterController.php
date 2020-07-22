<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\HqUser;
use App\Models\User;
use App\Models\UserAccount;

/**
 * 会员自主注册
 * Class HqUserRegisterController
 * @package App\Http\Controllers\Auth
 */
class HqUserRegisterController extends Controller
{
    public function userRegister($id){
        $info = $id?User::find($id):[];
        return view('auth.userRegister',['info'=>$info]);
    }

    /**
     * 会员注册保存
     * @param StoreRequest $request
     * @return array
     */
    public function userSave(StoreRequest $request)
    {
        $data = $request->all();
        unset($data['_token']);
        if (HqUser::where('mobile','=',$data['account'])->exists()){
            return ['msg'=>'手机号已存在','status'=>0];
        }else{
            $code = '111';
            if ($data['code']!=$code){
                return ['msg'=>'验证码不正确','status'=>0];
            }else{
                $info = $data['agent_id']?User::find($data['agent_id']):[];
                unset($data['code']);
                $data['mobile']=$data['account'];
                $dataInfo = HqUser::where('user_type','=',2)->orderBy('creatime','desc')->first();
                if (empty($dataInfo)){
                    $data['account']="100000000";
                }else{
                    $data['account']=$dataInfo['account']+$this->getAccount();
                }
                $data['password']=md5($data['password']);
                $data['reg_ip']=$request->ip();
                $data['mobile']=$data['account'];
                $data['nnbets_fee']=$info['nnbets_fee'];
                $data['lhbets_fee']=$info['lhbets_fee'];
                $data['bjlbets_fee']=$info['bjlbets_fee'];
                $data['sgbets_fee']=$info['sgbets_fee'];
                $data['a89bets_fee']=$info['a89bets_fee'];
                $data['limit']=$info['limit'];
                $data['creatime']=time();
                $data['remark']='会员扫码线上代理码注册';
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
                        return ['msg'=>'注册成功','status'=>1];
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
     * 生成一个1位或者2位随机数
     * @return int
     */
    public function getAccount()
    {
        return mt_rand(1,10);
    }
}