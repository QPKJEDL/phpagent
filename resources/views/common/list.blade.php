<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>@yield('title') | {{ Config::get('app.name') }}</title>
    <link rel="stylesheet" type="text/css" href="/static/tools/layui/css/layui.css" />
    <script src="/static/tools/layui/layui.js" type="text/javascript" charset="utf-8"></script>
    <script src="/static/admin/js/common.js?fsfd=1" type="text/javascript" charset="utf-8"></script>
    <script src="/static/admin/js/qrcode.js" type="text/javascript" charset="utf-8"></script>
    <script src="/static/tools/js/jquery-3.3.1.min.js" type="text/javascript" charset="utf-8"></script>
    <style>
        #table tbody .selected{
            background-color: rgb(103,173,167);
        }
        #table tbody .s{
            background-color: #7FFFD4;
        }
    </style>
</head>
<body>
<div class="wrap-container clearfix">
    <div class="column-content-detail">
        <form class="layui-form" action="">
            <div class="layui-form-item">
                <div style="width: 100%;" class="layui-inline tool-btn">
                    @yield('header')
                </div>
                {{ csrf_field() }}
            </div>
        </form>
        <div class="layui-form" id="table-list">
            @yield('table')
        </div>
    </div>
</div>
<ul class="layui-fixbar">
    <li class="layui-icon layui-fixbar-top" id="top" style="display: list-item">&#xe619;</li>
</ul>
</body>
<script type="text/javascript">
    $(document).ready(function () {
        //首先隐藏
        $("#top").hide();
        $(function () {
            $(window).scroll(function () {
                if ($(window).scrollTop() > 50){
                    $("#top").fadeIn(200);
                }else{
                    $("#top").fadeOut(200);
                }
            });
        });

        $("#top").click(function () {
            $('body,html').animate({
                scrollTop:0
            },500)
            return false;
        });
    });
    $("#table tbody tr").click(function () {
        $(this).addClass('selected').siblings().removeClass('selected').end();
    });
    $("#table tbody tr").mousemove(function () {
        $(this).addClass('s');
    }).mouseout(function () {
        $(this).removeClass('s').end();
    });
</script>
@yield('js')
</html>