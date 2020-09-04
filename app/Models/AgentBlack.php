<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 代理黑名单
 * Class AgentBlack
 * @package App\Models
 */
class AgentBlack extends Model
{
    protected $table = 'agent_blacklist';

    /**
     * 检查代理是否被封禁
     * @param $account
     * @return bool
     */
    public static function checkAgentIsLogin($account)
    {
        $bool = false;
        $data = AgentBlack::where('agent_username','=',$account)->first();
        if (!($data['start_date']<time() && $data['end_date']>time()))
        {
            $bool = true;
        }
        return $bool;
    }
}