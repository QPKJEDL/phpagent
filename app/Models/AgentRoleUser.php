<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AgentRoleUser extends Model
{
    protected $table = 'agent_role_user';

    /**
     * 根据userId获取角色id
     * @param $userId
     * @return mixed
     */
    public static function getRoleIdByUserId($userId){
        $data = AgentRoleUser::where('user_id','=',$userId)->first();
        return $data['role_id'];
    }
}