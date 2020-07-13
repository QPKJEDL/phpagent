<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>网站后台管理系统</title>
    <link rel="stylesheet" type="text/css" href="/static/admin/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="/static/admin/css/admin.css"/>
    <link href="/static/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/admin/summernote/summernote.min.css">
</head>
<body>
<div class="wrap-container welcome-container">
    <div class="row">
        <div class="welcome-left-container col-lg-9">
            <div class="data-show">
                <ul class="clearfix">
                    <li class="col-sm-12 col-md-3 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-org f-l">
                                <span class="iconfont">&#xe600;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">代理总数</p>
                                <p><span class="color-org">0</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-3 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-blue f-l">
                                <span class="iconfont">&#xe602;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">今天新增代理</p>
                                <p><span class="color-blue">0</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-3 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-green f-l">
                                <span class="iconfont">&#xe605;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">会员总数</p>
                                <p><span class="color-green">0</span></p>
                            </div>
                        </a>
                    </li>
                    <li class="col-sm-12 col-md-3 col-xs-12">
                        <a href="javascript:;" class="clearfix">
                            <div class="icon-bg bg-green f-l">
                                <span class="iconfont">&#xe605;</span>
                            </div>
                            <div class="right-text-con">
                                <p class="name">今日新增会员</p>
                                <p><span class="color-green">0</span></p>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <input type="hidden" id="agentId" value="{{\Illuminate\Support\Facades\Auth::id()}}">
        <div id="qrcode"></div>
    </div>
    <div class="row">
        <form class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label class="layui-form-label">当前登录用户</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" style="width: 30%;" disabled value="{{$user['username']}}" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">名称</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;" value="{{$user['nickname']}}" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">身份</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;" value="{{$user['role_name']}}" placeholder="请输入标题" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">登录时间</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;" value="{{$user['login_time']}}" placeholder="请输入标题" class="layui-input">
                </div>
            </div><div class="layui-form-item">
                <label class="layui-form-label">登录IP</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;" value="{{$user['last_ip']}}" placeholder="请输入标题" class="layui-input">
                </div>
            </div><div class="layui-form-item">
                <label class="layui-form-label">可用额度</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;" value="{{$user['balance']/100}}" placeholder="请输入标题" class="layui-input">
                </div>
            </div>
            @if($user['role_name']!="线上代理")
            <div class="layui-form-item">
                <label class="layui-form-label">透支额度</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;" value="0.00" placeholder="请输入标题" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">冻结金额</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;"value="0.00" placeholder="请输入标题" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">抽水权限</label>
                <div class="layui-input-block">
                    <input type="text" name="title" disabled autocomplete="off" style="width: 30%;" value="百家乐:{{$user['baccarat']}} 龙虎：{{$user['dragon_tiger']}} 牛牛：{{$user['niuniu']}} 三公：{{$user['sangong']}} A89：{{$user['A89']}}" placeholder="请输入标题" class="layui-input">
                </div>
            </div>
            @else
            <div class="layui-form-item">
                <label class="layui-form-label">抽水比例</label>
                <div class="layui-input-block">
                    <input type="text" name="title" disabled autocomplete="off" style="width: 30%;" value="{{$user['pump']}}%" placeholder="请输入标题" class="layui-input">
                </div>
            </div>
            @endif
            <div class="layui-form-item">
                <label class="layui-form-label">占股</label>
                <div class="layui-input-block">
                    <input type="text" name="title" autocomplete="off" disabled style="width: 30%;" value="{{$user['proportion']}}%" placeholder="请输入标题" class="layui-input">
                </div>
            </div>
        </form>
    </div>
</div>
<script src="/static/admin/layui/layui.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/js/jquery-2.1.1.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/js/Chart.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/lib/echarts/echarts.js"></script>
<script src="/static/admin/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/summernote/summernote.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/js/qrcode.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    new QRCode(document.getElementById("qrcode"), window.location.host + '/admin/userRegister/'+ document.getElementById('agentId').value);  // 设置要生成二维码的链接
</script>
</body>
</html>