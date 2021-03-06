<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>会员注册</title>
    <link rel="stylesheet" type="text/css" href="/static/admin/layui/css/layui.css" />
    <link rel="stylesheet" type="text/css" href="/static/admin/css/login.css" />
</head>

<body>
<div class="m-login-bg">
    <div class="m-login">
        <h3>会员注册</h3>
        <div class="m-login-warp">
            <form class="layui-form" id="form">
                <input type="hidden" id="token" name="_token" value="{{csrf_token()}}">
                <div class="layui-form-item">
                    <input type="hidden" name="agent_id" value="{{$id}}">
                    <input type="text" autocomplete="off" class="layui-input" disabled value="{{$info}}" readonly>
                </div>
                <div class="layui-form-item">
                    <input type="text" name="account" id="account" lay-verify="account" placeholder="手机号" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <input type="text" name="code" lay-verify="code" placeholder="验证码（后端写死111）" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-inline">
                        <input type="button" class="layui-btn layui-btn-sm" id="sendMsg" style="float: right;" onclick="buttoncss(this)" value="点击发送">
                    </div>
                </div>
                <div class="layui-form-item">
                    <input type="text" name="nickname" lay-verify="nickname" placeholder="昵称" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <input type="password" name="password" lay-verify="password" placeholder="登录密码" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <input type="password" lay-verify="pwd" id="password" placeholder="确认密码" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-item m-login-btn">
                    <div class="layui-inline">
                        <button class="layui-btn layui-btn-normal" id="act" lay-submit lay-filter="submit">注册</button>
                    </div>
                </div>
            </form>
        </div>
        <p class="copyright">Copyright 2018-{{date("Y",time())}} by 环球</p>
    </div>
</div>
<script src="/static/admin/layui/layui.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/admin/js/qrcode.js" type="text/javascript" charset="utf-8"></script>
<script src="/static/tools/js/jquery-3.3.1.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    layui.use(['form','jquery'], function() {
        var form = layui.form(),
            layer = layui.layer;
        $ = layui.jquery;
        form.verify({
            account:function (value) {
                var test=/^[1][3,4,5,7,8][0-9]{9}$/;
                if(!test.test(value)){
                    return '请输入正确的手机号'
                }
            },
            code:function (value) {
                if(value.length == 0){
                    return '验证码必填'
                }
            }
        });
        form.on('submit(submit)',function (data) {
            $.ajax({
                url:"{{url('/admin/userSave')}}",
                data:$('#form').serialize(),
                type:'post',
                dataType:'json',
                success:function(res){
                    if(res.status == 1){
                        layer.msg(res.msg,{icon:6});
                        alert("您的账号为"+res.account)
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

    //按钮样式
    function buttoncss(_this) {
        var account = document.getElementById('account').value;
        var test=/^[1][3,4,5,7,8][0-9]{9}$/;
        if(!test.test(account)){
            alert('请输入正确的手机号')
            return false;
        }
        $.ajax({
            headers:{
                "X-CSRF-TOKEN":$('#token').val()
            },
            url:"{{url('/admin/sendSms')}}",
            type:'post',
            data:{
                "mobile":account
            },
            dataType: "json",
            success:function (res) {
                if (res.status==1){
                    layer.msg(res.msg,{icon:6});
                    //禁用时间
                    var count = 60;
                    //禁用按钮
                    _this.setAttribute('class','layui-btn layui-btn-disabled');
                    //把按钮的文本改成重新发送
                    _this.value="重新发送"+count+"s";
                    var timer = setInterval(function () {
                        if(count<=0){
                            //清空时间
                            clearInterval(timer);
                            //修改样式
                            _this.setAttribute('class','layui-btn layui-btn-sm');
                            //修改文本
                            _this.value="点击发送";
                            count = 60;
                        }else{
                            _this.value="重新发送"+(count-1)+"s";
                            count--;
                        }
                    },1000);
                }else{
                    layer.msg(res.msg,{shift: 6,icon:5});
                }
            }
        });
    }
</script>
</body>

</html>