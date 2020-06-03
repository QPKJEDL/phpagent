<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Game;
use App\Models\HqUser;
use Illuminate\Support\Facades\Auth;

class AddUserController extends Controller
{
    public function index(){
        $user = Auth::user();
        $game = Game::getGameList();
        return view('addUser.list',['user'=>$user,'game'=>$game]);
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
                $data['password']=md5($data['password']);
                $bjl['player']=(int)$data['bjlbets_fee']['player'];
                $bjl['playerPair']=(int)$data['bjlbets_fee']['playerPair'];
                $bjl['tie'] = (int)$data['bjlbets_fee']['tie'];
                $bjl['banker']=(int)$data['bjlbets_fee']['banker'];
                $bjl['bankerPair']=(int)$data['bjlbets_fee']['bankerPair'];
                $data['bjlbets_fee']=json_encode($bjl);
                $lh['dragon']=(int)$data['lhbets_fee']['dragon'];
                $lh['tie']=(int)$data['lhbets_fee']['tie'];
                $lh['tiger']=(int)$data['lhbets_fee']['tiger'];
                $data['lhbets_fee']=json_encode($lh);
                $nn['Equal']=(int)$data['nnbets_fee']['Equal'];
                $nn['Double']=(int)$data['nnbets_fee']['Double'];
                $nn['SuperDouble']=(int)$data['nnbets_fee']['SuperDouble'];
                $data['nnbets_fee']=json_encode($nn);
                $count = HqUser::insert($data);
                if($count){
                    return ['msg'=>'操作成功','status'=>1];
                }else{
                    return ['msg'=>'操作失败','status'=>0];
                }
            }
        }
    }
}