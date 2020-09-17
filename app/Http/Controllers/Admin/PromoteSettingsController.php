<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\RedPackage;
use App\Models\RedPackageList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * 推广设置
 * Class PromoteSettingsController
 * @package App\Http\Controllers\Admin
 */
class PromoteSettingsController extends Controller
{

    public function index(Request $request)
    {
        RedPackage::insertRedPackage();
        $info = RedPackage::where(['agent_id'=>Auth::id()])->first();
        return view('promote.settings',['info'=>$info,'money'=>RedPackageList::getRedPackageSumMoneyByAgentId(Auth::id()),'count'=>RedPackageList::getRedPackageSumCountByAgentId(Auth::id())]);
    }

    /**
     * 修改发放红包页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function updateInfo()
    {
        $info = RedPackage::getRedPackageInfoByAgentId(Auth::id());
        return view('promote.edit',['info'=>$info,'id'=>0]);
    }

    /**
     * 保存修改红包
     * @param StoreRequest $request
     * @return array
     */
    public function update(StoreRequest $request)
    {
        $data = $request->all();
        if ((int)$data['money']<=0)
        {
            return ['msg'=>'单个红包金额不能小于0','status'=>0];
        }
        if ((int)$data['num']<=0)
        {
            return ['msg'=>'红包数量不能小于0','status'=>0];
        }
        $arr = array();
        $arr['hb_money']=(int)$data['money']*100;
        $arr['hb_num']=(int)$data['num'];
        $arr['hb_count']=(int)$data['num'];
        $arr['hb_balance']=(int)$data['money']*100 * (int)$data['num'];
        $result = RedPackage::where('agent_id','=',Auth::id())->update($arr);
        if ($result!==false)
        {
            return ['msg'=>'操作成功','status'=>1];
        }
        else
        {
            return ['msg'=>'操作失败','status'=>1];
        }
    }

    /**
     * 修改联系信息页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function updateContactInformation()
    {
        $info = RedPackage::getRedPackageInfoByAgentId(Auth::id());
        return view('promote.contact',['info'=>$info,'id'=>0]);
    }

    /**
     * 更新保存联系信息
     * @param StoreRequest $request
     * @return array
     */
    public function saveContactInformation(StoreRequest $request)
    {
        $phoneNumber = HttpFilter($request->input('phoneNumber'));
        $qqNumber = HttpFilter($request->input('qqNumber'));
        $wxNumber = HttpFilter($request->input('wxNumber'));
        if (!preg_match("/^1[3456789]\d{9}$/",$phoneNumber))
        {
            return ['msg'=>'手机号格式错误','status'=>0];
        }
        if (!preg_match("/^\d{5,10}$/",$qqNumber))
        {
            return ['msg'=>'qq号格式错误','status'=>0];
        }
        if (!preg_match("/^[a-zA-Z][a-zA-Z\d_-]{5,19}$/",$wxNumber))
        {
            return ['msg'=>'微信格式错误','status'=>0];
        }
        $data = array();
        $data['phone']=$phoneNumber;
        $data['qq']=$qqNumber;
        $data['wx']=$wxNumber;
        $result = RedPackage::where('agent_id','=',Auth::id())->update($data);
        if ($result!==false)
        {
            return ['msg'=>'操作成功','status'=>1];
        }
        else
        {
            return ['msg'=>'操作失败','status'=>0];
        }
    }
}