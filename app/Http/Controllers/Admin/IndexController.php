<?php


namespace App\Http\Controllers\Admin;


use App\Models\Menu;

class IndexController
{
    public function index()
    {
        $data = Menu::getMenuList();
        return view('common.index',['list'=>$data]);
    }

    /**
     * 获取菜单
     * @return array
     */
    public function getMenuList()
    {
        $data = Menu::getMenuList();
        return ['list'=>$data,'msg'=>'获取成功'];
    }
}