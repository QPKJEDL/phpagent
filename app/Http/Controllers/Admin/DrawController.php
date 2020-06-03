<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Draw;
use Illuminate\Http\Request;

class DrawController extends Controller
{
    public function index(Request $request){
        $map = array();
        if(true==$request->has('account')){
            
        }
        //$data = Draw::where($map)->paginate(10)->appends($request->all());
        //$draw = Draw::query();
        //$sql = $draw->leftJoin('user','user.user_id','=','user_draw.user_id')
          //  ->select('draw.*','user.nickname','user.account')->where($map);
        $sql = Draw::where($map);
          if(true==$request->has('begin')){
            $begin = strtotime($request->input('begin'));
            if(true==$request->has('end')){
                $end = strtotime($request->input('end'));
            }else{
                $end = time();
            }
            $date = strtotime(date('Y-m-d',$begin));
            $next = strtotime('+1day',date('Y-m-d'),$end)-1;
            $sql->whereBetween('draw.creatime',[$date,$next]);
        }
        //foreach($data as $key=>$value){
        //    $data[$key]['creatime']=date('Y-m-d H:m:s',$value['creatime']);
       // }
        return view('draw.list',['list'=>$data,'input'=>$request->all(),'min'=>config('admin.min_date')]);
    }
}