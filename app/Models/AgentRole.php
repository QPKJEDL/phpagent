<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AgentRole extends Model
{
    protected $table = 'agent_roles';

    /**
     * 根据roleId获取角色名称
     * @param $roleId
     * @return mixed
     */
    public static function getNameByRoleId($roleId){
        $data = AgentRole::where('id','=',$roleId)->first();
        return $data['display_name'];
    }
}