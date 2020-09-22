@section('title', '会员列表')
@section('header')
    <div class="layui-inline">
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#xe9aa;</i></button>
    </div>
    <div class="layui-inline">
        <input class="layui-input" lay-verify="begin" name="begin" placeholder="开始时间" id="begin" value="{{ $input['begin'] or '' }}" autocomplete="off">
    </div>
    <div class="layui-inline">
        <input class="layui-input" lay-verify="end" name="end" placeholder="结束时间" id="end" value="{{ $input['end'] or '' }}" autocomplete="off">
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="account" value="{{ $input['account'] or '' }}" name="account" placeholder="查询账号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <select name="status">
            <option value="">请选择类型</option>
            <option value="1" {{isset($input['status'])&&$input['status']==1?'selected':''}}>充值</option>
            <option value="2" {{isset($input['status'])&&$input['status']==2?'selected':''}}>提现</option>
            <option value="3" {{isset($input['status'])&&$input['status']==3?'selected':''}}>在线提现</option>
        </select>
    </div>
    <div class="layui-inline">
        <select name="userType">
            <option value="">请选择用户类型</option>
            <option value="1" {{isset($input['userType'])&&$input['userType']==1?'selected':''}}>会员</option>
            <option value="2" {{isset($input['userType'])&&$input['userType']==2?'selected':''}}>代理</option>
        </select>
    </div>
    <div class="layui-inline">
        <select name="create_by">
            <option value="">请选择操作人</option>
            <option value="0" {{isset($input['create_by'])&&$input['create_by']==0?'selected':''}}>操作员</option>
        </select>
    </div>
    <div class="layui-inline">
        <select name="business_name">
            <option value="">请选择三方商户</option>
            @foreach($business as $info)
                <option value="{{$info['business_id']}}" {{isset($input['business_name'])&&$input['business_name']==$info['business_id']?'selected':''}}>{{$info['service_name']}}</option>
            @endforeach
        </select>
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
        <button class="layui-btn layui-btn-normal reset" lay-submit>重置</button>
    </div>
@endsection
@section('table')
    <table class="layui-table" lay-size="sm" id="table">
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
            <th class="hidden-xs">时间</th>
            <th class="hidden-xs">类型</th>
            <th class="hidden-xs">用户名称[账号]</th>
            <th class="hidden-xs">直属上级[账号]</th>
            <th class="hidden-xs">直属一级[账号]</th>
            <th class="hidden-xs">操作前金额</th>
            <th class="hidden-xs">充值提现金额</th>
            <th class="hidden-xs">操作后金额</th>
            <th class="hidden-xs">操作类型</th>
            <th class="hidden-xs" style="display:block; text-align: left; width:30em; overflow:hidden; white-space: nowrap; text-overflow:ellipsis;">操作人</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs">{{$info->creatime}}</td>
                <td class="hidden-xs">
                    @if($info->user_type==1)
                        会员
                    @else
                        代理
                    @endif
                </td>
                <td class="hidden-xs">
                    @if($info->user_type==1)
                        {{$info->nickname}}[{{$info->user['account']}}]
                    @else
                        {{$info->nickname}}[{{$info->user['username']}}]
                    @endif
                </td>
                <td class="hidden-xs">
                    {{$info->agent_name}}[{{$info->sj['username']}}]
                </td>
                <td class="hidden-xs">{{$info->fir_name}}[{{$info->zs['username']}}]</td>
                <td class="hidden-xs">{{number_format($info->bet_before/100,2)}}</td>
                <td class="hidden-xs">
                    @if($info->money<0)
                        <span style="color: red;">{{number_format($info->money/100,2)}}</span>
                    @else
                        <span style="color: blue;">{{number_format($info->money/100,2)}}</span>
                    @endif
                </td>
                <td class="hidden-xs">{{number_format($info->bet_after/100,2)}}</td>
                <td class="hidden-xs">
                    @if($info->business_id==0)
                        @if($info->status==1 && $info->pay_type!=8)
                            <span style="color: blue">充值</span>
                        @elseif($info->status==1 && $info->pay_type==8)
                            <span style="color: green">领取激活红包</span>
                        @elseif($info->status==3 || $info->status==2)
                            @if($info->user_type==1)
                                @if($info->status==3 &&$info->pay_type==0)
                                    <span style="color: red;">提现</span>
                                @elseif($info->status==3 && $info->pay_type==1)
                                    <span style="color: red">银行卡提现</span>
                                @endif
                            @else
                                @if($info->status==2)
                                    <span style="color: red">提现</span>
                                @elseif($info->status==3)
                                    <span style="color: green">发放红包</span>
                                @endif
                            @endif
                        @endif
                        @if($info->status==1)
                            @if($info->pay_type==1)
                                (到款)
                            @elseif($info->pay_type==2)
                                (签单)
                            @elseif($info->pay_type==3)
                                (移分)
                            @elseif($info->pay_type==4)
                                (按比例)
                            @elseif($info->pay_type==5)
                                (支付宝)
                            @elseif($info->pay_type==6)
                                (微信)
                            @endif
                        @endif
                    @else
                        <span style="color: green;">{{$info->business_name}}</span>
                    @endif
                </td>
                <td class="hidden-xs">
                    @if($info->create_by>0)
                        {{$info->creUser['username']}}
                    @else
                        {{$info->create_by}}
                    @endif
                </td>
            </tr>
        @endforeach
        @if(!$list[0])
            <tr><td colspan="10" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
    </table>
    <div class="page-wrap" style="text-align: center;">
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
                ,limits:[100,500,1000]
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
            var date = new Date();
            var max = date.getFullYear()+'-'+(date.getMonth()+1) +'-'+date.getDate();
            laydate.render({
                elem:"#begin",
                min:"{{$min}}",
                max:max
            });
            laydate.render({
                elem:"#end",
                min:"{{$min}}",
                max:max
            });
            $(".reset").click(function(){
                $("input[name='begin']").val('');
                $("input[name='end']").val('');
                $("input[name='account']").val('');
                $("select[name='userType']").val('')
                $("select[name='status']").val('');
                $("select[name='create_by']").val('');
                $("select[name='business_name']").val('')
            });
            form.render();
            form.on('submit(formDemo)', function(data) {
                var begin = $("input[name='begin']").val();
                var end = $("input[name='end']").val();
                let beginTime = new Date(begin);
                let endTime = new Date(end);
                if(beginTime>endTime){
                    layer.msg('开始时间不能大于结束时间');
                    return false;
                }
            });
        });
    </script>
@endsection
@extends('common.list')