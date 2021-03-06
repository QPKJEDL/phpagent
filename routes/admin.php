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
    //Route::get('/userRegister/{id}','HqUserRegisterController@userRegister');//会员注册页面
    //Route::post('/userSave','HqUserRegisterController@userSave');//会员保存
    Route::get('/agentRegister/{id}',        'OnAgentActController@actAgent');//代理激活页面
    Route::post('/actAgent','OnAgentActController@actSave');//代理激活
    Route::get('/register',             'BindController@index');    //绑定谷歌验证码
    Route::post('/valAccount',          'BindController@checkAccount'); //效验账号是否存在
    Route::post('/valUser',             'BindController@checkUserLogin');//效验账号密码的真实性
    Route::post('/sendSMS',             'BindController@sendSMS');//发送验证码
    Route::post('/bindCode',            'BindController@bindCode');//绑定加效验
    Route::get('/login',                'LoginController@showLoginForm')->name('login');//登录
    Route::post('/login',               'LoginController@login');
    Route::get('/logout',               'LoginController@logout')->name('logout');
    Route::get('/userinfo',             'UserController@userInfo');
    Route::post('/sendSms','HqUserRegisterController@sendSms');//发送短信
    Route::post('/agentActSendSms','OnAgentActController@agentActSendSms');//线上代理发送短信
});
//后台主要模块
Route::group(['namespace' => "Admin",'middleware' => ['auth', 'permission']], function () {
    Route::post('/updatePwd','IndexController@updatePwd');//修改密码
    //菜单获取
    Route::get('/getMenuList',          'IndexController@getMenuList');
    //主页
    Route::get('/',                     'IndexController@index');
    //首页
    Route::get('/home',                 'HomeController@index');

    Route::get('/gewt',                 'HomeController@configr');
    Route::get('/index',                'HomeController@welcome');
    Route::post('/sort',                'HomeController@changeSort');
    Route::resource('/menus',           'MenuController');
    Route::resource('/logs',            'LogController');
    Route::resource('/users',           'UserController');
    Route::resource('/ucenter',         'UcenterController');
    Route::post('/saveinfo/{type}',     'UserController@saveInfo');
    Route::resource('/roles',           'RoleController');
    Route::resource('/permissions',     'PermissionController');
    //账户管理模块
    Route::resource('/delUser','DelUserController');//已删会员
    Route::resource('/delAgent','DelAgentController');//已删代理
    Route::resource('/agentList',       'AgentListController');//代理列表
    Route::get('/agentCzEdit/{id}','AgentListController@czEdit');//代理充值提现
    Route::post('/agentCzSave','AgentListController@agentCzSave');//代理充值提现保存
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
    Route::post('/agentList/accountUnique','AgentListController@accountUnique');//效验代理账号是否存在
    Route::post('/checkUniqueUserName', 'AddAgentUserController@checkUnique');//添加代理效验账号是否存在
    Route::resource('/addUser',             'AddUserController');//添加会员
    Route::post('/hqUser/checkAccountUnique','HqUserController@checkAccountUnique');//效验会员账号是否存在
    Route::resource('/hqUser',          'HqUserController');//会员列表
    Route::post('/hqUser/userUpdate','HqUserController@userUpdate');//会员编辑保存
    Route::get('/hqUser/czCord/{id}',   'HqUserController@czCord');//在线充值提现界面
    Route::resource('/draw','DrawController');//会员提现查询
    Route::resource('/czrecord','CzController');//会员充值查询
    Route::resource('/removeAgent','RemoveAgentController');//已删代理
    Route::resource('/removeUser', 'RemoveUserController');//已删会员
    Route::resource('/online','OnlineUserController');//在线玩家
    Route::resource('/order', 'OrderController');//注单查询
    Route::resource('/userDay','UserDayEndController');//会员日结表
    Route::get('/userOrderList/{id}/{begin}/{end}','OrderController@getOrderListByUserId');//下注详情
    Route::resource('/agentDay','AgentDayController');//代理日结
    Route::get('/agentDays/{id}/{begin}/{end}','AgentDayController@getIndexByParentId');//下级代理日结
    Route::get('/userDays/{id}/{begin}/{end}','UserDayEndController@getUserDayEndByAgentId');//下级会员日结
    Route::post('/czSave','HqUserController@czSave');//在线提现充值
    Route::resource('/agentDraw','AgentDrawController');//代理提现查询
    Route::resource('/agentCz','AgentCzController');//代理充值查询

    Route::resource('/qrocde','QrcodeController');//会员注册二维码

    //根据代理id查询充值提现记录
    Route::get('/agent/getRecordById/{id}','AgentCzController@getRecordById');

    //根据userId查询充值提现记录
    Route::get('/hquser/getRecordByUserId/{id}','DrawController@getRecordByUserId');

    //推广列表
    Route::resource('/promoteList','PromoteListController');
    //推广设置
    Route::resource('/promoteSettings','PromoteSettingsController');
    //修改红包发放记录页面
    Route::get('/promote/updateInfo','PromoteSettingsController@updateInfo');
    //修改保存红包发放
    Route::post('/promote/update','PromoteSettingsController@update');

    //修改联系信息
    Route::get('/promote/contact','PromoteSettingsController@updateContactInformation');
    //修改保存联系信息
    Route::post('/promote/updateContact','PromoteSettingsController@saveContactInformation');

    //红包领取记录
    Route::get('/getRedPackageRecord','PromoteListController@getRedPackageRecord');
});
Route::group(['namespace'=>"Online",'middleware'=>['auth','permission']],function (){
    Route::resource('/onAddAgent','OnAddAgentController');//新增下级代理
    Route::resource('/onDelAgent','OnDelAgentController');//线上已删代理
    Route::resource('/onDelUser','OnDelUserController');//线上已删会员
    Route::resource('/onAgentList','OnAgentListController');//代理列表
    Route::post('/onAgentList/update','OnAgentListController@update');//编辑保存
    Route::get('/onAgent/showAgent/{id}','OnAgentListController@showAgent');//下级代理
    Route::get('/onAgent/showUser/{id}','OnAgentListController@showUser');//下级会员
    Route::get('/onAgentList/qrCode/{id}','OnAgentListController@qrCodeShow');//显示未激活代理的二维码
    Route::resource('/onHqUser','OnHqUserController');//会员列表
    Route::resource('/onOrder','OnOrderController');//注单查询
    Route::resource('/onCz','OnCzController');//会员充值查询
    Route::resource('/onAgentDay','OnAgentDayController');//线上代理日结
    Route::get('/onAgentDayEnd/{id}/{begin}/{end}','OnAgentDayController@getIndexByParentId');//线上代理下级日结
});
Route::get('/phpinfo',function (Request $request){
   phpinfo();
});