@section('title', '角色编辑')
@section('content')
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>{{$info['account']}}结构关系</legend>
    </fieldset>
    <ul class="layui-timeline">
        <li class="layui-timeline-item">
            <i class="layui-icon layui-timeline-axis"></i>
            <div class="layui-timeline-content layui-text">
                <h3 class="layui-timeline-title">admin(总公司)</h3>
            </div>
        </li>
        @foreach($parent as $p)
            <li class="layui-timeline-item">
                <i class="layui-icon layui-timeline-axis"></i>
                <div class="layui-timeline-content layui-text">
                    <h3 class="layui-timeline-title">{{$p['username']}}({{$p['nickname']}})</h3>
                    <p>
                        @if($p['userType']==2)[抽水{{$p['pump']}}%]@endif[占成{{$p['proportion']}}%]<br/>
                        创建日期：{{$p['created_at']}}
                    </p>
                </div>
            </li>
        @endforeach
        <li class="layui-timeline-item">
            <i class="layui-icon layui-timeline-axis"></i>
            <div class="layui-timeline-content layui-text">
                <h3 class="layui-timeline-title">{{$info['account']}}({{$info['nickname']}})</h3>
                <p>
                    创建日期：{{$info['creatime']}}
                </p>
            </div>
        </li>
    </ul>
@endsection
@section('id','')
@section('js')
    <script>
        layui.use(['form','jquery','laypage', 'layer','tree'], function() {
            var tree = layui.tree;
            var form = layui.form,
                $ = layui.jquery;
            var layer = layui.layer;
            form.verify({
            });
            form.on('submit(formDemo)', function(data) {
                return false;
            });
        });
    </script>
@endsection
@extends('common.edita')