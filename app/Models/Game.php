<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'game';

    public static function getGameList(){
        $data = Game::where('type','=',1)->get();
        foreach($data as $key=>$value){
            $data[$key]['fee'] = json_decode($value['fee'],true);
        }
        return $data;
    }
}