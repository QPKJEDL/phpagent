<!DOCTYPE html>
<html>
<head>
    <title>环球国际代理后台</title>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=devich-width, inital-scale=1, maxinum-scale=1" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" type="text/css" href="/static/tools/layui/css/layui.css"/>
    <link rel="stylesheet" type="text/css" href="/static/tools/css/admin.css"/>
    <link rel="stylesheet" type="text/css" href="/static/tools/css/bootstrap.min.css">
</head>
<body style="height: 500px;">
    <div class="larry-wrapper">
        <!-- 顶部统计数据预览 -->
        <div class="layui-row" id="homeTop">
            <div class="layui-col-xs3">
                <section>
                    <div class="layui-col-xs1"  style="border: 1px solid black; height: 100px; width: 100px;background-color: #1aa094">
                        <i class="layui-icon" style="font-size: 20px;position: absolute;top:30%;left: 35%;">&#xe613;</i>
                    </div>
                    <div class="layui-col-xs2" style="border: 1px solid black; height: 100px; width: 300px">
                        <div style="position: absolute;left: 30%">
                            <a href="javascript:;" class="agent" @if(\Illuminate\Support\Facades\Auth::user()['userType']==1) data-id="2" @else data-id="22" @endif data-title="代理列表" @if(\Illuminate\Support\Facades\Auth::user()['userType']==1) data-url="{{url('/admin/agentList')}}" @else data-url="{{url('/admin/onAgentList')}}"@endif><h3>{{$agentCount}}</h3></a>
                            <h4>代理总数</h4>
                        </div>
                    </div>
                </section>
            </div>
            <div class="layui-col-xs3">
                <section>
                    <div class="layui-col-xs1"  style="border: 1px solid black; height: 100px; width: 100px;background-color: #1aa094">
                        <i class="layui-icon" style="font-size: 20px;position: absolute;top:30%;left: 35%;">&#xe654;</i>
                    </div>
                    <div class="layui-col-xs2" style="border: 1px solid black; height: 100px; width: 300px">
                        <div style="position: absolute;left: 30%">
                            <a href="javascript:;"><h3>{{$agentToDayCount}}</h3></a>
                            <h4>今日新增代理</h4>
                        </div>
                    </div>
                </section>
            </div>
            <div class="layui-col-xs3">
                <section>
                    <div class="layui-col-xs1"  style="border: 1px solid black; height: 100px; width: 100px;background-color: #1aa094">
                        <i class="layui-icon" style="font-size: 20px;position: absolute;top:30%;left: 35%;">&#xe770;</i>
                    </div>
                    <div class="layui-col-xs2" style="border: 1px solid black; height: 100px; width: 300px">
                        <div style="position: absolute;left: 30%">
                            <a href="javascript:;" class="agent" @if(\Illuminate\Support\Facades\Auth::user()['userType']==1) data-id="3" @else data-id="23" @endif data-title="会员列表" @if(\Illuminate\Support\Facades\Auth::user()['userType']==1) data-url="{{url('/admin/hqUser')}}" @else data-url="{{url('/admin/onHqUser')}}" @endif><h3>{{$userCount}}</h3></a>
                            <h4>会员总数</h4>
                        </div>
                    </div>
                </section>
            </div>
            <div class="layui-col-xs3">
                <section>
                    <div class="layui-col-xs1"  style="border: 1px solid black; height: 100px; width: 100px;background-color: #1aa094">
                        <i class="layui-icon" style="font-size: 20px;position: absolute;top:30%;left: 35%;">&#xe613;</i>
                    </div>
                    <div class="layui-col-xs2" style="border: 1px solid black; height: 100px; width: 300px">
                        <div style="position: absolute;left: 30%">
                            <a href="javascript:;"><h3>{{$userToDayCount}}</h3></a>
                            <h4>今日新增会员</h4>
                        </div>
                    </div>
                </section>
            </div>
    </div>
        <blockquote class="layui-elem-quote layui-text">
            欢迎光临环球国际代理后台管理系统
        </blockquote>
        <div style="padding: 20px; background-color: #F2F2F2;">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">用户信息</div>
                        <div class="layui-card-body">
                            <table class="layui-table">
                                <colgroup>
                                    <col width="150">
                                    <col width="200">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>当前登录用户</th>
                                    <th>{{$user['username']}}</th>
                                </tr>
                                <tr>
                                    <th>名称</th>
                                    <th>{{$user['nickname']}}</th>
                                </tr>
                                <tr>
                                    <th>身份</th>
                                    <th>
                                        @if($user['userType']==1)
                                            线下代理
                                        @else
                                            线上代理
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    <th>登录时间</th>
                                    <th>{{date('Y-m-d H:i:s',time())}}</th>
                                </tr>
                                <tr>
                                    <th>登录IP</th>
                                    <th>{{$ip}}</th>
                                </tr>
                                <tr>
                                    <th>可用额度</th>
                                    <th>{{$user['balance']/100}} <button type="button" class="layui-btn layui-btn-primary layui-btn-xs" onclick="window.location.reload()">刷新额度</button></th>
                                </tr>
                                <tr>
                                    @if($user['userType']==1)
                                    <th>抽水权限</th>
                                    <th>
                                        百家乐-@if($user['baccarat']==1)有@else没有@endif&nbsp;
                                        龙虎-@if($user['dragon_tiger']==1)有@else没有@endif&nbsp;
                                        牛牛-@if($user['niuniu']==1)有@else没有@endif&nbsp;
                                        三公-@if($user['sangong']==1)有@else没有@endif&nbsp;
                                        A89-@if($user['A89']==1)有@else没有@endif
                                    </th>
                                    @endif
                                </tr>
                                @if($user['userType']==2)
                                    <th>抽水百分比</th>
                                    <th>{{$user['pump']}}%</th>
                                @endif
                                <tr>
                                    <th>占股</th>
                                    <th>
                                        {{$user['proportion']}}%
                                    </th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="/static/tools/js/jquery-3.3.1.min.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    $(".agent").click(function () {
        var id = $(this).attr('data-id');
        var url = $(this).attr('data-url');
        var title = $(this).attr('data-title')
        var topWindow = $(window.parent.document);
        var isActive = topWindow.find('#nav').children(":first").children("li[lay-id=" + id + "]");
        if(isActive.length>0){
            var a;
            var arr = topWindow.find('#nav').children(':first').children();
            for (var i=0;i<arr.length;i++){
                var layId = $(arr[i]).attr('lay-id');
                if(layId==id){
                    a = i
                    break;
                }
            }
            //获取到当前选中的tab选项卡
            var index = topWindow.find('#nav').children(':first').children('li[class="layui-this"]');
            index.removeClass('layui-this');
            isActive.addClass('layui-this');
            var indexHtml = topWindow.find('#nav').children(':last').children('div[class="layui-tab-item layui-show"]');
            indexHtml.removeClass('layui-show');
            //获取iframe数组
            var iframe = topWindow.find('#nav').children(':last').children();
            $(iframe[a]).addClass('layui-show')
        }else{
            //获取到当前选中的tab选项卡
            var index = topWindow.find('#nav').children(':first').children('li[class="layui-this"]');
            index.removeClass('layui-this');
            var tabUL = topWindow.find('#nav').children(':first');
            var str = '<li lay-id="'+id+'" class="layui-this">'+title+'<i class="layui-icon layui-unselect layui-tab-close" onclick="tabClose(this)">ဆ</i></li>';
            tabUL.append(str);
            var indexHtml = topWindow.find('#nav').children(':last').children('div[class="layui-tab-item layui-show"]');
            indexHtml.removeClass('layui-show');
            var tabDiv = topWindow.find('#nav').children(':last');
            var str1 = '<div class="layui-tab-item layui-show"><iframe frameborder="0" style="width: 100%;height: calc(100vh - 157px)" name="'+title+'" src="'+url+'"></iframe></div>';
            tabDiv.append(str1)
        }
    });
</script>
</html>