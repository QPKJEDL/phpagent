@section('title', '用户列表')
@section('header')
    <div class="layui-inline">
    <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#xe9aa;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="account" value="{{ $input['account'] or '' }}" name="account" placeholder="用户账号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="nickname" value="{{ $input['nickname'] or '' }}" name="nickname" placeholder="用户昵称" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
        <button class="layui-btn layui-btn-normal reset" lay-submit>重置</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-size="sm">
        <colgroup>
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
            <col class="hidden-xs" width="100">
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">账号</th>
            <th class="hidden-xs">名称</th>
            <th class="hidden-xs">手机号码</th>
            <th class="hidden-xs">总推广量</th>
            <th class="hidden-xs">成功激活量</th>
            <th class="hidden-xs">当前余额</th>
            <th class="hidden-xs">最近充值</th>
            <th class="hidden-xs">激活红包</th>
            <th class="hidden-xs">激活时间</th>
            <th class="hidden-xs">功能</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['account']}}</td>
                <td class="hidden-xs">{{$info['nickname']}}</td>
                <td class="hidden-xs">{{$info['phone_number']}}</td>
                <td class="hidden-xs">{{$info['sum_count']}}</td>
                <td class="hidden-xs">{{$info['success_count']}}</td>
                <td class="hidden-xs">{{number_format($info['balance']/100,2)}}</td>
                <td class="hidden-xs">{{number_format($info['cz']/100,2)}}</td>
                <td class="hidden-xs">{{number_format($info['hb_money']/100,2)}}</td>
                <td class="hidden-xs">{{$info['create_time']}}</td>
                <td class="hidden-xs"></td>
            </tr>
        @endforeach
        @if(count($list)==0)
            <tr><td colspan="10" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
    </table>
    <div class="page-wrap">
        <div id="demo"></div>
    </div>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer','laypage'], function() {
            var form = layui.form,
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer,
                laypage = layui.laypage
            ;
            laydate.render({
                elem:"#begin"
            });
            laydate.render({
                elem:"#end"
            });
            var count = {{$list->total()}};
            var curr = {{$list->currentPage()}};
            var limit = {{$limit}};
            var url = "";
            //分页
            laypage.render({
                elem: 'demo'
                ,count: count
                ,curr:curr
                ,limit:limit
                ,limits:[10,50,100,150]
                ,layout: ['count', 'prev', 'page', 'next', 'limit', 'refresh', 'skip']
                ,jump: function(obj,first){
                    if(url.indexOf("?") >= 0){
                        url = url.split("?")[0] + "?page=" + obj.curr + "&limit="+ obj.limit + "&" +$("form").serialize();
                    }else{
                        url = url + "?page=" + obj.curr + "&limit="+obj.limit;
                    }
                    if (!first){
                        location.href = url;
                    }
                }
            });
            $(".reset").click(function () {
                $('input[name="account"]').val('')
                $('input[name="nickname"]').val('')
            });
            form.render();
            form.on('submit(formDemo)', function(data) {
                console.log(data);
            });
        });
    </script>
@endsection
@extends('common.list')