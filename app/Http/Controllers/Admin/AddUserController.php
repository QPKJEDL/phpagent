<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Game;
use App\Models\HqUser;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Auth;

class AddUserController extends Controller
{
    public function index(){
        $user = Auth::user();
        $user['fee']=json_decode($user['fee'],true);
        $user['limit']=json_decode($user['limit'],true);
        $user['nnbets_fee']=json_decode($user['nnbets_fee'],true);
        $user['lhbets_fee']=json_decode($user['lhbets_fee'],true);
        $user['bjlbets_fee']=json_decode($user['bjlbets_fee'],true);
        $user['sgbets_fee']=json_decode($user['sgbets_fee'],true);
        $user['a89bets_fee']=json_decode($user['a89bets_fee'],true);
        return view('addUser.list',['user'=>$user]);
    }

    /**
     * 保存新增会员
     */
    public function store(StoreRequest $request){
        $account = $request->input('account');
        $password = $request->input('pwd');
        $data = $request->all();
        unset($data['_token']);
        unset($data['pwd']);
        //效验账号是否存在
        $result = HqUser::where('account','=',$account)->exists();
        if($result){
            return ['msg'=>'该账号已存在','status'=>0];
        }else{
            if($data['password']!=$password){
                return ['msg'=>'两次密码不一致','status'=>0];
            }else{
                $data['fee']=json_encode($data['fee']);
                $limit['min']=(int)$data['limit']['min']*100;
                $limit['max']=(int)$data['limit']['max']*100;
                $limit['tieMin']=(int)$data['limit']['tieMin']*100;
                $limit['tieMax']=(int)$data['limit']['tieMax']*100;
                $limit['pairMin']=(int)$data['limit']['pairMin']*100;
                $limit['pairMax']=(int)$data['limit']['pairMax']*100;
                $data['limit'] = json_encode($limit);
                $data['password']=md5($data['password']);
                $bjl['player']=(int)$data['bjlbets_fee']['player']*100;
                $bjl['playerPair']=(int)$data['bjlbets_fee']['playerPair']*100;
                $bjl['tie'] = (int)$data['bjlbets_fee']['tie']*100;
                $bjl['banker']=$data['bjlbets_fee']['banker']*100;
                $bjl['bankerPair']=(int)$data['bjlbets_fee']['bankerPair']*100;
                $data['bjlbets_fee']=json_encode($bjl);
                $lh['dragon']=$data['lhbets_fee']['dragon']*100;
                $lh['tie']=(int)$data['lhbets_fee']['tie']*100;
                $lh['tiger']=$data['lhbets_fee']['tiger']*100;
                $data['lhbets_fee']=json_encode($lh);
                $nn['Equal']=$data['nnbets_fee']['Equal']*100;
                $nn['Double']=$data['nnbets_fee']['Double']*100;
                $nn['SuperDouble']=$data['nnbets_fee']['SuperDouble']*100;
                $data['nnbets_fee']=json_encode($nn);
                $sg['Equal']=$data['sgbets_fee']['Equal']*100;
                $sg['Double']=$data['sgbets_fee']['Double']*100;
                $sg['SuperDouble'] = $data['sgbets_fee']['SuperDouble']*100;
                $data['sgbets_fee']=json_encode($sg);
                $a89['Equal'] = $data['a89bets_fee']['Equal']*100;
                $a89['Double']=97;
                $a89['SuperDouble'] = $data['a89bets_fee']['SuperDouble'] * 100;
                $data['a89bets_fee']=json_encode($a89);
                if (!empty($data['is_show'])){
                    $data['is_show']=1;
                }
                $data['creatime']=time();
                $data['savetime']=$data['creatime'];
                $data['user_type']=1;
                $count = HqUser::insertGetId($data);
                if($count){
                    $account = array();
                    $account['user_id']=$count;
                    $account['balance']=0;
                    $account['tol_recharge']=0;
                    $account['drawMoney']=0;
                    $account['creatime']=time();
                    $num = UserAccount::insert($account);
                    if ($num){
                        return ['msg'=>'操作成功','status'=>1];
                    }else{
                        return ['msg'=>'操作失败','status'=>0];
                    }
                }else{
                    return ['msg'=>'操作失败','status'=>0];
                }
            }
        }
    }
}