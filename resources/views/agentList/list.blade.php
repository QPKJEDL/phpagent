@section('title', '代理列表')
@section('header')
    <div class="layui-inline">
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#xe9aa;</i></button>
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="username" value="{{ $input['username'] or '' }}" name="username" placeholder="请输入代理账号" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <input type="text" lay-verify="nickname" value="{{ $input['nickname'] or '' }}" name="nickname" placeholder="请输入昵称" autocomplete="off" class="layui-input">
    </div>
    <div class="layui-inline">
        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="formDemo">搜索</button>
        <button class="layui-btn layui-btn-normal reset" lay-submit>重置</button>
        <button class="layui-btn layui-btn-normal" lay-submit>导出EXCEL</button>
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
            <col class="hidden-xs" width="300">
        </colgroup>
        <thead>
            <input type="hidden" id="token" value="{{csrf_token()}}">
        <tr>
            <th class="hidden-xs">代理账号</th>
            <th class="hidden-xs">姓名</th>
            <th class="hidden-xs">账户余额</th>
            <th class="hidden-xs">群组余额</th>
            <th class="hidden-xs">百/龙/牛/三/A洗码率</th>
            <th class="hidden-xs">占成</th>
            <th class="hidden-xs">创建时间</th>
            <th class="hidden-xs">状态</th>
            <th class="hidden-xs">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $info)
            <tr>
                <td class="hidden-xs"><a class="a" data-id="{{$info['id']}}">{{$info['username']}}</a></td>
                <td class="hidden-xs">{{$info['nickname']}}</td>
                <td class="hidden-xs">{{number_format($info['balance']/100,2)}}</td>
                <td class="hidden-xs">{{number_format($info['groupBalance']/100,2)}}</td>
                <td class="hidden-xs">{{$info['fee']['baccarat']}}/{{$info['fee']['dragonTiger']}}/{{$info['fee']['niuniu']}}/{{$info['fee']['sangong']}}/{{$info['fee']['A89']}}</td>
                <td class="hidden-xs">{{$info['proportion']}}%</td>
                <td class="hidden-xs">{{$info['created_at']}}</td>
                <td class="hidden-xs">
                    @if($info['status']==0)
                        <input type="checkbox" checked="" name="open" value="{{$info['id']}}" lay-skin="switch" lay-filter="switchTest" lay-text="正常|停用">
                    @else
                        <input type="checkbox" name="close" lay-skin="switch" value="{{$info['id']}}" lay-filter="switchTest" lay-text="正常|停用">
                    @endif
                </td>
                <td>
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-xs @if($info['userCount']==0)layui-btn-disabled @else layui-btn-normal @endif user" data-id="{{$info['id']}}" data-name="{{$info['nickname']}}" @if($info['userCount']==0) disabled @endif data-desc="下级会员"><i class="layui-icon">下级会员</i></button>
                        <button class="layui-btn layui-btn-xs @if($info['id']==$user['id'])layui-btn-disabled @elseif($info['agentCount']==0) layui-btn-disabled @else layui-btn-normal @endif agent" data-id="{{$info['id']}}"@if($info['id']==$user['id'])disabled @elseif($info['agentCount']==0) disabled @endif data-name="{{$info['nickname']}}" data-desc="下级代理"><i class="layui-icon">下级代理</i></button>
                        @if($info['id']!=$user['id'])
                            <button class="layui-btn layui-btn-xs layui-btn-normal cz" data-id="{{$info['id']}}" data-username="{{$info['username']}}" data-name="{{$info['nickname']}}"><i class="layui-icon">充值提现</i></button>
                            <button class="layui-btn layui-btn-xs layui-btn-normal agentEdit" data-id="{{$info['id']}}" data-name="{{$info['nickname']}}"><i class="layui-icon">账号编辑</i></button>
                            <button class="layui-btn layui-btn-xs layui-btn-danger resetPwd" data-id="{{$info['id']}}" data-name="{{$info['nickname']}}"><i class="layui-icon">修改密码</i></button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        @if(!$list[0])
            <tr><td colspan="6" style="text-align: center;color: orangered;">暂无数据</td></tr>
        @endif
        </tbody>
    </table>
    <div class="page-wrap">
        <div id="demo"></div>
    </div>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate','laypage', 'layer','element'], function() {
            var form = layui.form,
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer,
                element = layui.element,
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
            $(".a").click(function () {
                var id = $(this).attr('data-id');
                layer.open({
                    type:2,
                    title:'结构关系',
                    shadeClose:true,
                    offset:'10%',
                    area:['30%','50%'],
                    content:'/admin/agentList/getRelationalStruct/'+id
                });
            });
            $(".cz").click(function () {
                var id = $(this).attr('data-id');
                var nickname = $(this).attr('data-name');
                var username = $(this).attr('data-username');
                layer.open({
                    type:2,
                    title:'代理提现充值>'+nickname+'('+username+')',
                    shadeClose:true,
                    offset:'10%',
                    area:['60%','80%'],
                    content:'/admin/agentCzEdit/' + id
                });
            });
            //下级用户
            $(".user").click(function(){
                var id = $(this).attr('data-id');
                var nickname = $(this).attr('data-name');
                var index = layer.open({
                    type:2,
                    title:nickname+'的下级用户',
                    shadeClose:true,
                    offset:'10%',
                    area:['60%','80%'],
                    content:'/admin/agentList/user/'+ id
                });
                layer.full(index);
            });
            //下级代理
            $(".agent").click(function () {
                var id = $(this).attr('data-id');
                var nickname = $(this).attr('data-name');
                var index = layer.open({
                    type:2,
                    title:nickname + '的下级代理',
                    shadeClose: true,
                    offset: '10%',
                    area: ['60%','80%'],
                    content:'/admin/agentList/agent/' + id
                });
                layer.full(index);
            });
            $(".reset").click(function(){
                $("input[name='username']").val('');
                $("input[name='nickname']").val('');
            });
            //修改密码
            $(".resetPwd").click(function () {
                var id = $(this).attr('data-id');
                var name = $(this).attr('data-name');
                layer.open({
                    type:2,
                    title:name + '修改密码',
                    shadeClose:true,
                    offset:'10%',
                    area:['60%','80%'],
                    content:'/admin/agentList/resetAgentPwd/' + id
                });
            });
            //代理账号编辑
            $('.agentEdit').click(function () {
                var id = $(this).attr('data-id');
                var name = $(this).attr('data-name');
                var index = layer.open({
                    type:2,
                    title:name + '账号编辑',
                    shadeClose:true,
                    offset:'10%',
                    area:['60%','80%'],
                    content:'/admin/agentEdit/'+id
                });
                layer.full(index);
            });
            form.render();
            form.on('switch(switchTest)',function(data){
                var name = $(data.elem).attr('name');
                if(name=='open'){
                    var status = 1;    
                }else if(name='close'){
                    var status = 0;
                }
                $.ajax({
                    headers:{
                        'X-CSRF-TOKEN': $("#token").val()
                    },
                    url:"{{url('/admin/agentList/changeStatus')}}",
                    type:"post",
                    data:{
                        "id":$(data.elem).val(),
                        "status":status
                    },
                    dataType:"json",
                    success:function(res){
                        if(res.status==1){
                            layer.msg(res.msg,{icon:6});
                        }else{
                            layer.msg(res.msg,{shift:6,icon:5});
                        }
                    }
                });
            });
            form.on('submit(formDemo)', function(data) {
            });
        });
    </script>
@endsection
@extends('common.list')
