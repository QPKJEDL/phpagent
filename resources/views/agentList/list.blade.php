@section('title', '代理列表')
@section('header')
    <div class="layui-inline">
        <button class="layui-btn layui-btn-small layui-btn-warm freshBtn"><i class="layui-icon">&#x1002;</i></button>
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
    <table class="layui-table" lay-even lay-skin="nob">
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
                <td class="hidden-xs">{{$info['username']}}</td>
                <td class="hidden-xs">{{$info['nickname']}}</td>
                <td class="hidden-xs">{{$info['']}}</td>
                <td class="hidden-xs">{{$info['']}}</td>
                <td class="hidden-xs">{{$info['fee']['baccarat']}}%/{{$info['fee']['dragonTiger']}}%/{{$info['fee']['niuniu']}}%/{{$info['fee']['sangong']}}%/{{$info['fee']['A89']}}%</td>
                <td class="hidden-xs">{{$info['']}}</td>
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
                        <button class="layui-btn layui-btn-small layui-btn-normal user" data-id="{{$info['id']}}" data-name="{{$info['nickname']}}" data-desc="下级会员"><i class="layui-icon">下级会员</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-normal agent" data-id="{{$info['id']}}" data-desc="下级代理"><i class="layui-icon">下级代理</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-normal" data-id="{{$info['id']}}" data-desc="账号编辑" data-url="{{url('/admin/roles/'. $info['id'] .'/edit')}}"><i class="layui-icon">账号编辑</i></button>
                        <button class="layui-btn layui-btn-small layui-btn-danger del-btn" data-id="{{$info['id']}}" data-url="{{url('/admin/roles/'.$info['id'])}}"><i class="layui-icon">修改密码</i></button>
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
        {{$list->render()}}
    </div>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer','element'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer,
                element = layui.element;
            ;
            laydate({istoday: true});
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
            $(".reset").click(function(){
                $("input[name='username']").val('');
                $("input[name='nickname']").val('');
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
