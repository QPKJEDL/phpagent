<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Billflow;
use App\Models\Czrecord;
use App\Models\RedPackageList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 推广列表
 * Class PromoteListController
 * @package App\Http\Controllers\Admin
 */
class PromoteListController extends Controller
{
    public function index(Request $request)
    {
        $map = array();
        if (true==$request->has('account'))
        {
            $map['user.account']=HttpFilter($request->input('account'));
        }
        $map['redpackage_list.agent_id']=Auth::id();
        $sql = RedPackageList::query();
        $sql->leftJoin('user','user.user_id','=','redpackage_list.user_id')
            ->leftJoin('user_account','redpackage_list.user_id','=','user_account.user_id')
            ->select('redpackage_list.*','user.account','user.nickname','user_account.balance')->where($map);
        if (true==$request->has('nickname'))
        {
            $sql->where('user.nickname','like','%'.HttpFilter($request->input('nickname')).'%');
        }
        if (true==$request->has('limit'))
        {
            $limit = $request->input('limit');
        }
        else
        {
            $limit = config('admin.limit');
        }
        $data = $sql->paginate($limit)->appends($request->all());
        foreach ($data as $key=>$datum)
        {
            $data[$key]['cz']=$this->getUserCzCord($datum['user_id']);
            $data[$key]['create_time']=date('Y-m-d H:i:s',$datum['create_time']);
        }
        return view('promote.list',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }

    /**
     * 获取用户最近充值记录
     * @param $userId
     * @return Czrecord|\Illuminate\Database\Eloquent\Model|null
     */
    public function getUserCzCord($userId){
        $bill = new Billflow();
        $bill->setTable('user_billflow_'.date('Ymd',time()));
        $data = $bill->where('user_id','=',$userId)->orderBy('creatime','desc')->where('status','=',1)->first();
        return $data['score'];
    }

    /**
     * 红包领取记录
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function getRedPackageRecord(Request $request)
    {
        if (true==$request->has('begin'))
        {
            $begin = strtotime($request->input('begin'));
        }
        else
        {
            $begin = strtotime(date('Y-m-d',time()));
            $request->offsetSet('begin',date('Y-m-d',time()));
        }
        if (true==$request->has('end'))
        {
            $end = strtotime('+1day',strtotime($request->input('end')))-1;
        }
        else
        {
            $end = strtotime('+1day',$begin)-1;
            $request->offsetSet('end',date('Y-m-d',time()));
        }
        $map=array();
        $map['user.agent_id']=Auth::id();
        $sql = RedPackageList::query();
        $sql->leftJoin('user','user.user_id','=','redpackage_list.user_id')
            ->leftJoin('user_account','redpackage_list.user_id','=','user_account.user_id')
            ->select('redpackage_list.*','user.account','user.nickname','user_account.balance')->where($map);
        $sql->whereBetween('create_time',[$begin,$end]);

        if (true==$request->has('limit'))
        {
            $limit = $request->input('limit');
        }
        else
        {
            $limit = 10;
        }
        $data = $sql->where('hb_money','>',0)->orderBy('create_time','desc')->paginate($limit);
        foreach ($data as $key=>$datum)
        {
            $data[$key]['create_time']=date('Y-m-d H:i:s',$datum['create_time']);
        }
        return view('promote.hbList',['list'=>$data,'input'=>$request->all(),'limit'=>$limit]);
    }
}