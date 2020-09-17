<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RedPackage extends Model
{
    protected $table = 'hongbao_list';
    public $timestamps = false;

    /**
     * 获取详情
     * @param $id
     * @return RedPackage|Model|null
     */
    public static function getRedPackageInfoByAgentId($id)
    {
        return RedPackage::where('agent_id','=',$id)->first();
    }


    /**
     * 判断是否存在数据 不存在添加
     */
    public static function insertRedPackage()
    {
        if (!RedPackage::where('agent_id','=',Auth::id())->exists())
        {
            $data = array();
            $data['agent_id']=Auth::id();
            $data['hb_money']=0;
            $data['hb_num']=0;
            $data['hb_count']=0;
            $data['hb_balance']=0;
            $data['code']=RedPackage::getInviteCode();
            $data['creatime']=time();
            RedPackage::insert($data);
        }
    }

    /**
     * 获取邀请码
     * @return string
     */
    public static function getInviteCode()
    {
        $code = RedPackage::createInviteCode();
        while (RedPackage::where(['code'=>$code])->exists())
        {
            $code = RedPackage::createInviteCode();
        }
        return $code;
    }

    /**
     * 生成邀请码
     * @return string
     */
    public static function createInviteCode()
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d')
            .substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $d = '',
            $f = 0;
            $f < 6;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return $d;
    }
}