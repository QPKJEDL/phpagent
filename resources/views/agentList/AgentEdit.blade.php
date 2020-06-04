@section('title', '会员账号编辑')
@section('content')
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">昵称：</label>
            <div class="layui-input-block">
              <input type="text" name="nickname" value="{{$info['nickname']}}" lay-verify="required" autocomplete="off" class="layui-input">
            </div>
          </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">百家乐洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[baccarat]" lay-verify="required" readonly disabled value="{{$info['fee']['baccarat']}}" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            <label class="layui-form-label">龙虎洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[dragonTiger]" lay-verify="required" autocomplete="off" value="{{$info['fee']['dragonTiger']}}" readonly disabled class="layui-input">
            </div>
          </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">牛牛洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[niuniu]" lay-verify="required" value="{{$info['fee']['niuniu']}}" autocomplete="off" readonly disabled class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">三公洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[sangong]" lay-verify="required" value="{{$info['fee']['sangong']}}" autocomplete="off" class="layui-input" readonly disabled>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">A89洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[A89]" lay-verify="required" value="{{$info['fee']['A89']}}" autocomplete="off" class="layui-input" readonly disabled>
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
        <div class="layui-input-block">
            <input type="checkbox" name="is_allow" title="允许其直属会员在线充值" @if($info['is_allow']==1) checked="" @endif>
        </div>
    </div>
    <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">备注：</label>
        <div class="layui-input-inline">
            <textarea name="remark" id="remark" placeholder="请输入备注" class="layui-textarea">{{$info['remark']}}</textarea>
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
                var data = $('form').serializeArray();
                $.ajax({
                    url:"{{url('/admin/saveAgentEdit')}}",
                    data:data,
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