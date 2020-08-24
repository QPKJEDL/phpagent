@section('title', '角色编辑')
@section('content')
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
            <input type="text" readonly value="{{$info['username']}}" name="username" lay-verify="username" lay autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">昵称：</label>
        <div class="layui-input-inline">
            <input type="text" name="nickname" value="{{$info['nickname']}}" lay-verify="nickname" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">限红范围：</label>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[min]" readonly placeholder="￥" value="10" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-form-mid">-</div>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[max]" readonly placeholder="￥" value="50000" autocomplete="off" class="layui-input">
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">和限红范围：</label>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[tieMin]" readonly placeholder="￥" autocomplete="off" value="10" class="layui-input">
            </div>
            <div class="layui-form-mid">-</div>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[tieMax]" readonly placeholder="￥" autocomplete="off"  value="5000" class="layui-input">
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">对限红范围：</label>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[pairMin]" readonly placeholder="￥" autocomplete="off"  class="layui-input" value="10">
            </div>
            <div class="layui-form-mid">-</div>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[pairMax]" readonly placeholder="￥" autocomplete="off" class="layui-input" value="5000">
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">抽水：</label>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="number" name="pump" disabled readonly  lay-verify="pump" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" value="{{$info['pump']}}" data-v="{{$user['pump']}}" placeholder="%" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-form-mid layui-word-aux">比如20%就填写20</div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">占比：</label>
            <div class="layui-input-inline" style="width: 100px;">
                <input type="number" name="proportion" disabled readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="proportion" value="{{$user['proportion']}}" data-v="{{$user['proportion']}}" placeholder="%" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-form-mid layui-word-aux">比如20%就填写20</div>
        </div>
    </div>
@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','laypage', 'layer','tree'], function() {
            var tree = layui.tree;
            var form = layui.form(),
                $ = layui.jquery;
            form.on('checkbox(pAllChoose)', function(data) {
                var child = $(".permission").find('input[type="checkbox"]');
                child.each(function(index, item) {
                    item.checked = data.elem.checked;
                });
                if(data.elem.checked)$(this).attr('title','全不选');
                else $(this).attr('title','全选');
                form.render('checkbox');
            });

            form.render();
            var layer = layui.layer;
            form.verify({
                pump:function (value) {
                    var pump = $("input[name='pump']").attr('data-v');
                    if(value>=pump){
                        return '不能大于当前代理'
                    }else if(value<pump-10){
                        return '不能低于当前代理的百分之十'
                    }
                },
                proportion:function (value) {
                    var proportion = $("input[name='proportion']").attr('data-v');
                    if(value>proportion){
                        return  '不能大于当前代理'
                    }
                }
            });
            form.on('submit(formDemo)', function(data) {
                $.ajax({
                    url:"{{url('/admin/onAgentList/update')}}",
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
