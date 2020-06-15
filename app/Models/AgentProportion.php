<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AgentProportion extends Model
{
    protected $table = "agent_proportion";

    public static function insertAgentProportionLog($agentId,$result,$afterResult){
        $data['agent_id']=$agentId;
        $data['proportion']=$result;
        $data['after_proportion']=$afterResult;
        $data['create_time']=time();
        return AgentProportion::insert($data);
    }
}