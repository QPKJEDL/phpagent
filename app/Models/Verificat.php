<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Verificat extends Model
{
    protected $table = 'verificat';
    public $timestamps = false;

    public static function ytxSend($mobile,$code,$ip,$key)
    {
        //判断同一个手机号1分钟内是否发送
        $status = Redis::get('hq_sendSms_lock_'.$mobile);
        if ($status==2)
        {
            return "123";
        }

        //判断同一个ip 1分钟内是否已发送
        $status = Redis::get('hq_sendSms_lock_'.$ip);
        if ($status==2)
        {
            return "123";
        }

        $data['username']='hanyun';
        $data['password']=md5(md5("5v4E6wUC").time());//密码
        $data['mobile']=$mobile;//手机号
        $data['content']='【环球视讯】您的验证码为'.$code.',在5分钟内有效。';
        $data['tKey']=time();
        $url = 'http://api.mix2.zthysms.com/v2/sendSms';
        $data = json_encode($data);
        $res = Verificat::https_post_kf($url,$data);
        $res = json_decode($res,true);
        if ($res['code']==200)
        {
            //设置短信验证码缓存为5分钟
            Redis::setex($key.$mobile,300,$code);
            //设置短信发送缓存时间为1分钟1次
            Redis::setex('hq_sendSms_lock_'.$mobile,60,2);
            //设置短信发送缓存时间为1分钟1次
            Redis::setex('hq_sendSms_lock_'.$ip,60,2);
            return "0";
        }else{
            return "1";
        }
        return $res;
    }

    /**
     * 发送请求
     * @param $url
     * @param $data
     * @return bool|string
     */
    public static function https_post_kf($url,$data)
    {
        $headers = array(
            "Content-type: application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }
}