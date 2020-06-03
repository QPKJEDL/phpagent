@section('title', '会员账号编辑')
@section('content')
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">昵称：</label>
            <div class="layui-input-block">
              <input type="text" name="nickname" value="{{$info['nickname']}}" lay-verify="required" value="" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            <label class="layui-form-label">日赢上限：</label>
            <div class="layui-input-block">
              <input type="text" name="" lay-verify="" value="{{$info[''] or 0}}" autocomplete="off" class="layui-input">
            </div>
          </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">百家乐洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="" lay-verify="required" readonly disabled value="{{$info['fee']['baccarat']}}" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            <label class="layui-form-label">龙虎洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="" lay-verify="" autocomplete="off" value="{{$info['fee']['dragonTiger']}}" readonly disabled class="layui-input">
            </div>
          </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">牛牛洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="" lay-verify="required" value="{{$info['fee']['niuniu']}}" autocomplete="off" readonly disabled class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">三公洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="" lay-verify="required" value="{{$info['fee']['sangong']}}" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">A89洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="" lay-verify="required" value="{{$info['fee']['A89']}}" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">押闲赔率：</label>
            <div class="layui-input-block">
              <input type="text" lay-verify="required" value="1" autocomplete="off" readonly disabled class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">押庄赔率：</label>
            <div class="layui-input-block">
              <input type="text" lay-verify="required" value="0.95" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">押虎赔率：</label>
            <div class="layui-input-block">
              <input type="text" lay-verify="required" value="0.97" autocomplete="off" readonly disabled class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">押龙赔率：</label>
            <div class="layui-input-block">
              <input type="text" lay-verify="required" value="0.97" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小限红：</label>
            <div class="layui-input-block">
              <input type="number" name="" lay-verify="required" value="10" autocomplete="off" readonly disabled class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大限红：</label>
            <div class="layui-input-block">
              <input type="number" name="" lay-verify="required" value="50000" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小和限红：</label>
            <div class="layui-input-block">
              <input type="numberv" name="" lay-verify="required" value="10" autocomplete="off" readonly disabled class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大和限红：</label>
            <div class="layui-input-block">
              <input type="number" name="" lay-verify="required" value="5000" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小对限红：</label>
            <div class="layui-input-block">
              <input type="numberv" name="" lay-verify="required" value="10" autocomplete="off" readonly disabled class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大对限红：</label>
            <div class="layui-input-block">
              <input type="number" name="" lay-verify="required" value="5000" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <div class="layui-input-block">
                <input type="checkbox" name="is_show" title="报表中显示洗码量">
            </div>
        </div>
    </div>
@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','layer'], function() {
            var form = layui.form()
                ,layer = layui.layer
                ,$ = layui.jquery;
            form.render();
            form.verify({
                
            });
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/agentList/savePwd')}}",
                    data:$('form').serialize(),
                    type:'post',
                    dataType:'json',
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
@extends('common.edit')