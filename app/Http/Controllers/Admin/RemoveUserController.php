<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Czrecord;
use App\Models\HqUser;
use Illuminate\Http\Request;

/**
 * 已删会员
 * Class RemoveUserController
 * @package App\Http\Controllers\Admin
 */
class RemoveUserController extends Controller
{
    public function index(Request $request){
        $map = array();
        $map['user.del_flag']=1;
        if(true==$request->has('account')){
            $map['user.account']=HttpFilter($request->input('account'));
        }
        $sql = HqUser::query();
        $sql->leftJoin('user_account','user_account.user_id','=','user.user_id')
            ->select('user.*','user_account.balance')
            ->where($map);
        if(true==$request->has('nickname')){
            $sql->where('nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        $data = $sql->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['fee']=json_decode($value['fee'],true);
            $data[$key]['creatime'] = date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('removeUser.list',['list'=>$data,'input'=>$request->all()]);
    }

    /**
     * 获取用户最近充值记录
     */
    public function getUserCzCord($userId){
        $data = Czrecord::where('user_id',$userId)->orderBy('creatime','desc')->first();
        return $data;
    }
}