<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//验证码
Route::get('/verify',                   'Admin\HomeController@verify');
//登陆模块
Route::group(['namespace'  => "Auth"], function () {
    Route::get('/register',             'BindController@index');    //绑定谷歌验证码
    Route::post('/valAccount',          'BindController@checkAccount'); //效验账号是否存在
    Route::post('/valUser',             'BindController@checkUserLogin');//效验账号密码的真实性
    Route::post('/sendSMS',             'BindController@sendSMS');//发送验证码
    Route::post('/bindCode',            'BindController@bindCode');//绑定加效验
    Route::get('/login',                'LoginController@showLoginForm')->name('login');//登录
    Route::post('/login',               'LoginController@login');
    Route::get('/logout',               'LoginController@logout')->name('logout');
});
//后台主要模块
Route::group(['namespace' => "Admin",'middleware' => ['auth', 'permission']], function () {
    Route::get('/',                     'HomeController@index');
    Route::get('/gewt',                 'HomeController@configr');
    Route::get('/index',                'HomeController@welcome');
    Route::post('/sort',                'HomeController@changeSort');
    Route::resource('/menus',           'MenuController');
    Route::resource('/logs',            'LogController');
    Route::resource('/users',           'UserController');
    Route::resource('/ucenter',         'UcenterController');
    Route::get('/userinfo',             'UserController@userInfo');
    Route::post('/saveinfo/{type}',     'UserController@saveInfo');
    Route::resource('/roles',           'RoleController');
    Route::resource('/permissions',     'PermissionController');
    //账户管理模块
    Route::resource('/agentList',       'AgentListController');//代理列表
    Route::get('/agentEdit/{id}',       'AgentListController@agentEdit');//代理账号编辑
    Route::post('/saveAgentEdit',       'AgentListController@saveAgentEdit');//代理账号编辑保存
    Route::post('/agentList/changeStatus','AgentListController@changeStatus');//代理启用停用
    Route::post('/agentList/changeUserStatus','AgentListController@changeUserStatus');//用户停用启用
    Route::get('/agentList/user/{id}','AgentListController@user');//下级会员
    Route::get('/agentList/agent/{id}','AgentListController@getAgentChildren');//下级代理
    Route::get('/agentList/getRelationalStruct/{id}','AgentListController@getRelationalStruct');//代理结构关系
    Route::get('/agentList/getUserRelation/{id}','AgentListController@getUserRelational');//用户结构关系
    Route::get('/agentList/resetAgentPwd/{id}','AgentListController@agentPasswordEdit');//代理修改密码界面
    Route::post('/agentList/saveAgentPwd','AgentListController@resetAgentPwd');//保存代理密码修改
    Route::get('/agentList/resetPwd/{id}','AgentListController@resetPwd');//修改会员密码界面
    Route::post('/agentList/savePwd',     'AgentListController@savePwd');//保存修改密码
    Route::get('/agentList/userEdit/{id}','AgentListController@userEdit');//会员编辑
    Route::resource('/addAgent',        'AddAgentUserController');//添加代理
    Route::post('/checkUniqueUserName', 'AddAgentUserController@checkUnique');//添加代理效验账号是否存在
    Route::resource('/addUser',             'AddUserController');//添加会员
    Route::resource('/hqUser',          'HqUserController');//会员列表
    Route::get('/hqUser/czCord/{id}',   'HqUserController@czCord');//在线充值提现界面
    Route::resource('/draw','DrawController');//会员提现查询
    Route::resource('/czrecord','CzController');//会员充值查询
    Route::resource('/removeAgent','RemoveAgentController');//已删代理
    Route::resource('/removeUser', 'RemoveUserController');//已删会员
    Route::resource('/online','OnlineUserController');//在线玩家
    Route::resource('/order', 'OrderController');//注单查询
    Route::resource('/userDay','UserDayEndController');//会员日结表
    Route::get('/userOrderList/{id}/{begin}/{end}','OrderController@getOrderListByUserId');//下注详情
});

Route::get('/phpinfo',function (Request $request){
   phpinfo();
});