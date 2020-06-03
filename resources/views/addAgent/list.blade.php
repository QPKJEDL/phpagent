@section('title', '角色列表')
@section('header')
@endsection
@section('table')
<form class="layui-form">
    <div class="layui-form-item">
        <label class="layui-form-label">直属上级：</label>
        <div class="layui-input-inline">
            <input type="hidden" name="parent_id" value="{{$user['id']}}"/>
            <input type="text" name="user" lay-verify="title" disabled autocomplete="off" value="{{$user['nickname']}}" readonly class="layui-input">
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
            <input type="text" name="price_min" placeholder="￥" value="10" disabled readonly autocomplete="off" class="layui-input">
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="price_max" placeholder="￥" value="50000" disabled readonly autocomplete="off" class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">和限红范围：</label>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="price_min" placeholder="￥" autocomplete="off" disabled value="10" readonly class="layui-input">
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="price_max" placeholder="￥" autocomplete="off" disabled value="5000" readonly class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">对限红范围：</label>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="price_min" placeholder="￥" autocomplete="off" disabled class="layui-input" value="10" readonly>
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="price_max" placeholder="￥" autocomplete="off" disabled class="layui-input" value="5000" readonly>
          </div>
        </div>
    </div>
    @if($user['baccarat']!=0)
    <div class="layui-form-item">
        <label class="layui-form-label">百家乐洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[baccarat]" lay-verify="title" autocomplete="off" value="{{$user['fee']['baccarat']}}" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的百家乐洗码率(%)。默认:{{$user['fee']['baccarat']}}%</div>
    </div>
    @else
    <div class="layui-form-item">
        <label class="layui-form-label">百家乐洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[baccarat]" lay-verify="title" disabled readonly autocomplete="off" value="{{$user['fee']['baccarat']}}" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的百家乐洗码率(%)。默认:{{$user['fee']['baccarat']}}%</div>
    </div>
    @endif
    @if($user['dragon_tiger'] != 0)
    <div class="layui-form-item">
        <label class="layui-form-label">龙虎洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[dragonTiger]" lay-verify="title" value="{{$user['fee']['dragonTiger']}}" autocomplete="off" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的龙虎洗码率(%)。默认:{{$user['fee']['dragonTiger']}}%</div>
    </div>
    @else
    <div class="layui-form-item">
        <label class="layui-form-label">龙虎洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[dragonTiger]" disabled readonly lay-verify="title" value="{{$user['fee']['dragonTiger']}}" autocomplete="off" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的龙虎洗码率(%)。默认:{{$user['fee']['dragonTiger']}}%</div>
    </div>
    @endif
    @if($user['niuniu']!=0)
    <div class="layui-form-item">
        <label class="layui-form-label">牛牛洗码率：</label>
        <div class="layui-input-inline">
        <input type="number" name="fee[niuniu]" lay-verify="title" autocomplete="off" value="{{$user['fee']['niuniu']}}" class="layui-input">
        </div>
    <div class="layui-form-mid layui-word-aux">小于或等于所属代理的牛牛洗码率(%)。默认:{{$user['fee']['niuniu']}}%</div>
    </div>
    @else
    <div class="layui-form-item">
        <label class="layui-form-label">牛牛洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[niuniu]" lay-verify="title" disabled readonly autocomplete="off" value="{{$user['fee']['niuniu']}}" class="layui-input">
        </div>
    <div class="layui-form-mid layui-word-aux">小于或等于所属代理的牛牛洗码率(%)。默认:{{$user['fee']['niuniu']}}%</div>
    </div>
    @endif
    @if($user['sangong']!=0)
    <div class="layui-form-item">
        <label class="layui-form-label">三公洗码率：</label>
        <div class="layui-input-inline">
        <input type="number" name="fee[sangong]" lay-verify="title" autocomplete="off" value="{{$user['fee']['sangong']}}" class="layui-input">
        </div>
    <div class="layui-form-mid layui-word-aux">小于或等于所属代理的三公洗码率(%)。默认:{{$user['fee']['sangong']}}%</div>
    </div>
    @else
    <div class="layui-form-item">
        <label class="layui-form-label">三公洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[sangong]" disabled readonly lay-verify="title" autocomplete="off" value="{{$user['fee']['sangong']}}" class="layui-input">
        </div>
    <div class="layui-form-mid layui-word-aux">小于或等于所属代理的三公洗码率(%)。默认:{{$user['fee']['sangong']}}%</div>
    </div>
    @endif
    @if($user['A89']!=0)
    <div class="layui-form-item">
        <label class="layui-form-label">A89洗码率：</label>
        <div class="layui-input-inline">
        <input type="number" name="fee[A89]" lay-verify="title" autocomplete="off" value="{{$user['fee']['A89']}}" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的A89洗码率(%)。默认:{{$user['fee']['A89']}}%</div>
    </div>
    @else
    <div class="layui-form-item">
        <label class="layui-form-label">A89洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[A89]" disabled readonly lay-verify="title" autocomplete="off" value="{{$user['fee']['A89']}}" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的A89洗码率(%)。默认:{{$user['fee']['A89']}}%</div>
    </div>
    @endif
    <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">备注：</label>
        <div class="layui-input-inline">
            <textarea name="remark" id="remark" placeholder="请输入备注" class="layui-textarea">姓名：
电话：
备注：
            </textarea>
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
                    if(value.length==0){
                        return '请输入账号';
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
                    url:"{{url('/admin/addAgent')}}",
                    type:"post",
                    data:data,
                    dataType:"json",
                    success:function(res){
                        if(res.status == 1){
                            layer.msg(res.msg,{icon:6});
                            var index = parent.layer.getFrameIndex(window.name);
                            setTimeout('parent.layer.close('+index+')',2000);
                            //parent.layer.close(index);
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
