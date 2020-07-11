@section('title', '角色列表')
@section('header')
@endsection
@section('table')
<form class="layui-form">
    <div class="layui-form-item">
        <label class="layui-form-label">直属上级：</label>
        <div class="layui-input-inline">
            <input type="hidden" name="parent_id" value="{{$user['id']}}"/>
            <input type="text" name="user" lay-verify="title" disabled autocomplete="off" value="{{$user['username']}}({{$user['nickname']}})" readonly class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">账号：</label>
        <div class="layui-input-inline">
            <input type="text" name="username" lay-verify="username" lay autocomplete="off" class="layui-input">
        </div>
        <div class="layui-input-inline">
            <button type="button" class="layui-btn" id="account">系统生成</button>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">昵称：</label>
        <div class="layui-input-inline">
            <input type="text" name="nickname" lay-verify="nickname" lay autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">密码：</label>
            <div class="layui-input-inline">
              <input type="password" name="password" lay-verify="password" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            <label class="layui-form-label">确认密码：</label>
            <div class="layui-input-inline">
              <input type="password" name="pwd" lay-verify="pwd" autocomplete="off" class="layui-input">
            </div>
          </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">限红范围：</label>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[min]" placeholder="￥" value="10" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[max]" placeholder="￥" value="50000" autocomplete="off" class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">和限红范围：</label>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[tieMin]" placeholder="￥" autocomplete="off" value="10" class="layui-input">
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[tieMax]" placeholder="￥" autocomplete="off"  value="5000" class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">对限红范围：</label>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[pairMin]" placeholder="￥" autocomplete="off"  class="layui-input" value="10">
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[pairMax]" placeholder="￥" autocomplete="off" class="layui-input" value="5000">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
          <button type="submit" class="layui-btn" lay-submit="" lay-filter="formDemo">立即提交</button>
          <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
      </div>
</form>
@endsection
@section('js')
    <script>
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            laydate({istoday: true});
            form.render();
            form.verify({
                username:function(value){
                    var reg = new RegExp('^[0-9]{6}$');
                    if (!reg.test(value)){
                        return '格式错误';
                    }
                },
                password:function(value){
                    if(value.length==0){
                        return '请输入密码';
                    }
                },
                pwd:function(value){
                    if(value.length==0){
                        return '请输入密码';
                    }
                    var password = $("input[name='password']").val();
                    if(value!=password){
                        return '必须与密码相同';
                    }
                }
            });
            $("#account").click(function(){
                //console.log(Math.random().toString().slice(-6));
                //清空数据
                $("input[name='username']").val('');
                $("input[name='username']").val(Math.random().toString().slice(-6));
            });
            form.on('submit(formDemo)', function(data) {
                var data = $('form').serializeArray();
                $.ajax({
                    url:"{{url('/admin/onAddAgent')}}",
                    type:"post",
                    data:data,
                    dataType:"json",
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6});
                            var index = parent.layer.getFrameIndex(window.name);
                            setTimeout('parent.layer.close('+index+')',2000);
                        }else{
                            layer.msg(res.msg,{shift: 6,icon:5});
                        }
                    },
                    error : function(XMLHttpRequest, textStatus, errorThrown) {
                        layer.msg('网络失败', {time: 1000});
                    }
                });
                return false;
            });
        });
    </script>
@endsection
@extends('common.list')