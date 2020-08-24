@section('title', '会员列表')
@section('header')
    <div class="layui-inline">
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#xe9aa;</i></button>
    </div>
    <div class="layui-inline">
        <input class="layui-input" lay-verify="begin" name="begin" placeholder="开始时间" id="begin" value="{{ $input['begin'] or '' }}" autocomplete="off">
    </div>
    <div class="layui-inline">
        <input class="layui-input" lay-verify="end" name="end" placeholder="结束时间" id="end" value="{{ $input['begin'] or '' }}" autocomplete="off">
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="username" value="{{ $input['username'] or '' }}" name="username" placeholder="代理账号" autocomplete="off" class="layui-input">
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
        </colgroup>
        <thead>
        <tr>
            <th class="hidden-xs">代理昵称[账号]</th>
            <th class="hidden-xs">会员名称[账号]</th>
            <th class="hidden-xs">操作前金额</th>
            <th class="hidden-xs">充值提现金额</th>
            <th class="hidden-xs">操作后金额</th>
            <th class="hidden-xs">操作类型</th>
            <th class="hidden-xs">备注</th>
            <th class="hidden-xs">操作时间</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info['nickname']}}[{{$info['username']}}]</td>
                <td class="hidden-xs">{{$info['userName']}}[{{$info['account']}}]</td>
                <td class="hidden-xs">{{number_format($info['bet_before']/100,2)}}</td>
                <td class="hidden-xs">{{number_format($info['money']/100,2)}}</td>
                <td class="hidden-xs">{{number_format($info['bet_after']/100,2)}}</td>
                <td class="hidden-xs">充值</td>
                {{--<td class="hidden-xs">
                    @if($info['type']==1)
                        到款
                    @elseif($info['type']==2)
                        签单
                    @elseif($info['type']==3)
                        移分
                    @elseif($info['type']==4)
                        按比例
                    @elseif($info['type']==5)
                        支付宝
                    @elseif($info['type']==6)
                        微信
                    @endif
                </td>--}}
                <td class="hidden-xs">{{$info['remark']}}</td>
                <td class="hidden-xs">{{$info['creatime']}}</td>
            </tr>
        @endforeach
        @if(!$list[0])
            <tr><td colspan="9" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
    </table>
    <div class="page-wrap">
        {{$list->render()}}
    </div>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form,
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            laydate.render({
                elem:"#begin",
                type:"datetime"
            });
            laydate.render({
                elem:"#end",
                type:"datetime"
            });
            $(".reset").click(function(){
                $("input[name='begin']").val('');
                $("input[name='end']").val('');
                $("input[name='username']").val('');
            });
            form.render();
            form.on('submit(formDemo)', function(data) {
                console.log(data);
            });
        });
    </script>
@endsection
@extends('common.list')