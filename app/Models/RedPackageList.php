<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RedPackageList extends Model
{
    protected $table = 'redpackage_list';

    /**
     * 通过id获取领取总额
     * @param $id
     * @return int|mixed
     */
    public static function getRedPackageSumMoneyByAgentId($id)
    {
        return RedPackageList::query()->where(['agent_id'=>$id])->sum('hb_money');
    }

    /**
     * 通过id获取领取次数
     * @param $id
     * @return int
     */
    public static function getRedPackageSumCountByAgentId($id)
    {
        return RedPackageList::query()->where(['agent_id'=>$id])->count('id');
    }
}