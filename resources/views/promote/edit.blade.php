@section('title', '用户编辑')
@section('content')
    <div class="layui-form-item">
        <label class="layui-form-label">单个红包金额：</label>
        <div class="layui-input-block">
            <input type="number" value="{{$info['hb_money']/100}}" name="money" lay-verify="money" placeholder="请输入单个红包金额" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">红包数量：</label>
        <div class="layui-input-block">
            <input type="number" value="{{$info['hb_num']}}" name="num" lay-verify="num" placeholder="请输入红包数量" autocomplete="off" class="layui-input">
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
            form.verify({
                money:function (value) {
                    if (value<=0){
                        return '不能小于0'
                    }
                },
                num:function (value) {
                    if(value<=0){
                        return '不能小于0'
                    }
                }
            });
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/promote/update')}}",
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