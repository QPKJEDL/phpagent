<?php


namespace App\Http\Controllers\Online;


use App\Http\Controllers\Controller;
use App\Models\Czrecord;
use App\Models\HqUser;
use Illuminate\Http\Request;

class OnDelUserController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        $map['user.del_flag']=1;
        if(true==$request->has('account')){
            $map['user.account']=$request->input('account');
        }
        $sql = HqUser::query();
        $sql->leftJoin('user_account','user_account.user_id','=','user.user_id')
            ->select('user.*','user_account.balance')
            ->where($map);
        if(true==$request->has('nickname')){
            $sql->where('user.nickname','like','%'.$request->input('nickname').'%');
        }
        if (true==$request->has('limit'))
        {
            $limit = $request->input('limit');
        }
        else
        {
            $limit=10;
        }
        $data = $sql->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['cz']=$this->getUserCzCord($value['user_id']);
            $data[$key]['creatime'] = date('Y-m-d H:i:s',$value['creatime']);
        }
        return view('onAgent.delUser.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }

    /**
     * 获取用户最近充值记录
     */
    public function getUserCzCord($userId)
    {
        $data = Czrecord::where('user_id',$userId)->orderBy('creatime','desc')->first();
        return $data;
    }
}