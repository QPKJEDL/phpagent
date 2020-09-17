@section('title', '用户编辑')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label">手机号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['phone'] or ''}}" name="phoneNumber" placeholder="请输入手机号" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">QQ号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['qq'] or ''}}" name="qqNumber" placeholder="请输入QQ号" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">微信号：</label>
        <div class="layui-input-block">
            <input type="text" value="{{$info['wx'] or ''}}" name="wxNumber" placeholder="请输入微信号" autocomplete="off" class="layui-input">
        </div>
    </div>
@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','laypage', 'layer'], function() {
            var form = layui.form,
                $ = layui.jquery;
            form.render();
            var layer = layui.layer;
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/promote/updateContact')}}",
                    type:'post',
                    data:$('form').serialize(),
                    dataType:'json',
                    success:function (res) {
                        if (res.status==1){
                            layer.msg(res.msg,{icon:6});
                            var index = parent.layer.getFrameIndex(window.name);
                            setTimeout('parent.layer.close('+index+')',2000);
                        }else{
                            layer.msg(res.msg,{shift: 6,icon:5});
                        }
                    }
                });
                return false;
            });
        });
    </script>
@endsection
@extends('common.edit')