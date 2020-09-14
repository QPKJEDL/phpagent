@section('title', '会员充值提现')
@section('content')
    <input type="hidden" id="status" value="1"/>
    <input type="hidden" id="username" value="{{$info['username']}}">
    <div class="layui-form-item">
        <label class="layui-form-label">账户余额：</label>
        <div class="layui-input-inline">
            <label id="userB">{{$info['balance']/100}}</label>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block">
            <input type="radio" name="type" value="1" title="充值" lay-filter="type" checked="">
            <input type="radio" name="type" value="2" title="提现" lay-filter="type">
        </div>
    </div>

    <div class="layui-form-item" id="payType">
        <label class="layui-form-label">充值类型：</label>
        <div class="layui-input-block">
            <input type="radio" name="payType" value="1" title="到款" checked="">
            <input type="radio" name="payType" value="2" title="签单">
            <input type="radio" name="payType" value="3" title="移分">
            <input type="radio" name="payType" value="4" title="按比例">
            <input type="radio" name="payType" value="5" title="支付宝">
            <input type="radio" name="payType" value="6" title="微信">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">充值/提现金额：</label>
        <div class="layui-input-inline">
            <input type="text" name="money" lay-verify="money" style="width: 150px;" pattern="\d" placeholder="请输入充值/提现金额" autocomplete="off" class="layui-input">
        </div>
        <div class="layui-form-mid"><h4 id="h4" style="color: red;"></h4></div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label"></label>
        <div class="layui-input-inline">
            <div class="layui-form-mid layui-word-aux">您的可用额度：<b id="balance">{{$balance/100}}</b>元</div>
        </div>
    </div>
@endsection
@section('id',$id)
@section('js')
    <script>
        layui.use(['form','jquery','layer'], function() {
            var form = layui.form
                ,layer = layui.layer
                ,$ = layui.jquery;
            form.render();
            form.verify({
                money:function (value) {
                    var regu = /^[0-9]+\.?[0-9]*$/;
                    if (value=="" || value==null){
                        return '必须输入大于0，且小于余额的整数';
                    }else{
                        var v = $('input:radio[name="type"]:checked').val();
                        if(v==1){
                            if(value<=0){
                                return '必须大于0，且小于余额的整数';
                            }else{
                                if (!regu.test(value)){
                                    return '请输入整数';
                                }else{
                                    var balance = $("#balance").html();
                                    if(parseInt(balance)<parseInt(value)){
                                        return '您的余额不足，不能进行充值'
                                    }
                                }
                            }
                        }else{
                            if (value<=0){
                                return '必须大于0，且小于用户余额的整数'
                            }else{
                                if(!regu.test(value)){
                                    return '请输入整数';
                                }else{
                                    var userB = $("#userB").html();
                                    if (parseInt(userB)<parseInt(value)){
                                        return '该用户的余额不足，不能进行提现'
                                    }
                                }
                            }
                        }
                    }

                }
            });
            var id = $("input[name='id']").val();
            var index = parent.layer.getFrameIndex(window.name);
            form.on('radio(type)',function(data){
                var payType = $("#payType");
                if(data.value==1){
                    payType.show();
                    $('input[name="money"]').val('')
                    $('#status').val('1')
                }else{
                    payType.hide();
                    $('input[name="money"]').val('')
                    $('#status').val('2')
                }
            });
            $("input[name='money']").on('keyup',function(){
                var money = $(this).val();
                var str = DX(money);
                $('#h4').html(str);
            });
            form.on('submit(formDemo)', function(data) {
                var status = $("#status").val();
                var username = $('#username').val();
                if(status==1){
                    layer.confirm('您确定给代理['+username+']充值'+$("input[name='money']").val()+'('+$("#h4").html()+')吗？',{
                        btn:['确定','取消'],//按钮
                        success:function (layero, index) {
                            this.enterEsc = function (event) {
                                if(event.keyCode == 13){
                                    $('.layui-layer-btn0').click();
                                    return false;
                                }
                            };
                            $(document).on('keydown',this.enterEsc);
                        },
                        end:function () {
                            $(document).off('keydown',this.enterEsc);
                        }
                    },function () {
                        $.ajax({
                            url:"{{url('/admin/agentCzSave')}}",
                            data:$('form').serialize(),
                            type:'post',
                            dataType:'json',
                            success:function(res){
                                if(res.status == 1){
                                    layer.msg(res.msg,{icon:6});
                                    var index = layer.open({
                                        type:2,
                                        title:username+'充值提现',
                                        shadeClose:true,
                                        offset:'10%',
                                        area:['60%','80%'],
                                        content:'/admin/agent/getRecordById/'+$("input[name='id']").val()
                                    });
                                    layer.full(index);
                                }else{
                                    layer.msg(res.msg,{shift: 6,icon:5});
                                }
                            },
                            error : function(XMLHttpRequest, textStatus, errorThrown) {
                                layer.msg('网络失败', {time: 1000});
                            }
                        });
                    },function () {
                        layer.msg('您取消了对该代理的充值');
                    });
                }else{
                    layer.confirm('您确定给代理['+username+']提现'+$("input[name='money']").val()+'('+$("#h4").html()+')吗？',{
                        btn:['确定','取消'],//按钮
                        success:function (layero, index) {
                            this.enterEsc = function (event) {
                                if(event.keyCode == 13){
                                    $('.layui-layer-btn0').click();
                                    return false;
                                }
                            };
                            $(document).on('keydown',this.enterEsc);
                        },
                        end:function () {
                            $(document).off('keydown',this.enterEsc);
                        }
                    },function () {
                        $.ajax({
                            url:"{{url('/admin/agentCzSave')}}",
                            data:$('form').serialize(),
                            type:'post',
                            dataType:'json',
                            success:function(res){
                                if(res.status == 1){
                                    layer.msg(res.msg,{icon:6});
                                    var index = layer.open({
                                        type:2,
                                        title:username+'充值提现',
                                        shadeClose:true,
                                        offset:'10%',
                                        area:['60%','80%'],
                                        content:'/admin/agent/getRecordById/'+$("input[name='id']").val()
                                    });
                                    layer.full(index);
                                }else{
                                    layer.msg(res.msg,{shift: 6,icon:5});
                                }
                            },
                            error : function(XMLHttpRequest, textStatus, errorThrown) {
                                layer.msg('网络失败', {time: 1000});
                            }
                        });
                    },function () {
                        layer.msg('您取消了对该代理的提现');
                    });
                }
                return false;
            });
            function DX(n) {
                if (n == 0) {
                    return "零";
                }
                if (!/^(\+|-)?(0|[1-9]\d*)(\.\d+)?$/.test(n))
                    return "数据非法";
                var unit = "仟佰拾亿仟佰拾万仟佰拾元角分", str = "";
                n += "00";
                var a = parseFloat(n);
                if (a < 0) {
                    n = n.substr(1);
                }
                var p = n.indexOf('.');
                if (p >= 0) {
                    n = n.substring(0, p) + n.substr(p + 1, 2);
                }
                unit = unit.substr(unit.length - n.length);
                for (var i = 0; i < n.length; i++)
                    str += '零壹贰叁肆伍陆柒捌玖'.charAt(n.charAt(i)) + unit.charAt(i);
                if (a > 0) {
                    return str.replace(/零(仟|佰|拾|角)/g, "零").replace(/(零)+/g, "零").replace(/零(万|亿|元)/g, "$1").replace(/(亿)万|壹(拾)/g, "$1$2").replace(/^元零?|零分/g, "").replace(/元$/g, "元整");
                } else {
                    return "负" + str.replace(/零(仟|佰|拾|角)/g, "零").replace(/(零)+/g, "零").replace(/零(万|亿|元)/g, "$1").replace(/(亿)万|壹(拾)/g, "$1$2").replace(/^元零?|零分/g, "").replace(/元$/g, "元整");
                }
            }
        });
    </script>
@endsection
@extends('common.edit')