@section('title', '用户列表')
@section('header')

@endsection
@section('table')
    <table class="layui-table" lay-size="sm" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td style="text-align: center;" colspan="2">
                    <div class="layui-inline">
                        <label class="layui-form-label layui-form-label-md">当前余额：</label>
                        <div class="layui-input-inline" style="width: 100px;">
                            <input id="balance" disabled class="layui-input" value="{{number_format(\Illuminate\Support\Facades\Auth::user()['balance']/100,2)}}">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-form-mid layui-word-aux" style="font-size: 16px;">请确保您的余额足够发放红包，否作推广激活的会员将无法领取您的红包！</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;"><button class="layui-btn layui-btn-xs" style="float: right;" type="button" id="update">修改发放红包</button></td>
                <td>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label layui-form-label-md">单个红包金额</label>
                            <div class="layui-input-inline" style="width: 80px">
                                <input id="simpleRedpacketAmount" disabled class="layui-input" value="{{number_format($info['hb_money']/100,2)}} ">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label layui-form-label-md">剩余红包个数</label>
                            <div class="layui-input-inline" style="width: 80px">
                                <input id="simpleRedpacketAmount" disabled class="layui-input" value="{{$info['hb_count']}}">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label layui-form-label-md">剩余红包总额</label>
                            <div class="layui-input-inline" style="width: 80px">
                                <input id="simpleRedpacketAmount" disabled  class="layui-input" value="{{number_format($info['hb_balance']/100,2)}}">
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;"><button class="layui-btn layui-btn-xs" style="float: right;" id="hbList" type="button">红包领取记录</button></td>
                <td>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label layui-form-label-md">已派红包总额</label>
                            <div class="layui-input-inline" style="width: 80px">
                                <input id="simpleRedpacketAmount" disabled class="layui-input" value="{{number_format($money/100,2)}}">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label layui-form-label-md">已派红包个数</label>
                            <div class="layui-input-inline" style="width: 80px">
                                <input id="simpleRedpacketAmount" disabled class="layui-input" value="{{$count}}">
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">
                    <button class="layui-btn layui-btn-xs" style="margin-left: 90px;" id="information">修改联系信息</button>
                </td>
            </tr>
            <tr>
                <td style="text-align: right">
                    <strong><i class="layui-icon">&#xe678;</i></strong>
                </td>
                <td>
                    <cite id="_phone" style="font-style: normal">{{$info['phone']}}</cite>
                </td>
            </tr>
            <tr>
                <td style="text-align: right">
                    <strong><i class="layui-icon">&#xe676;</i></strong>
                </td>
                <td>
                    <cite id="_phone" style="font-style: normal">{{$info['qq']}}</cite>
                </td>
            </tr>
            <tr>
                <td style="text-align: right">
                    <strong><i class="layui-icon">&#xe677;</i></strong>
                </td>
                <td>
                    <cite id="_phone" style="font-style: normal">{{$info['wx']}}</cite>
                </td>
            </tr>
            <tr>
                <td style="text-align: right">
                    <strong>H5推广连接：</strong>
                </td>
                <td>
                    <cite id="_phone" style="font-style: normal"><input type="text" readonly id="href" value="{{$url.$info['code']}}" style="width: 230px;">&nbsp;  <button type="button" id="copy" class="layui-btn layui-btn-primary layui-btn-xs">复制连接</button></cite>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" colspan="2">
                    <div id="qrcode"></div>
                    <br/>
                    <h2>立即扫码激活领取红包</h2>
                    <br/>
                    <button type="button" class="layui-btn layui-btn-primary layui-btn-sm" id="download">保存二维码</button>
                </td>
            </tr>
        </tbody>
    </table>
@endsection
@section('js')
    <script type="text/javascript" src="/static/tools/js/utf.js"></script>
    <script type="text/javascript" src="/static/tools/js/jquery.qrcode.js"></script>
    <script>
        window.onload=function(){
            var url = "{{$url.$info['code']}}";
            var qrcode = $("#qrcode").qrcode({
                render:'canvas',
                text:url,
                width:'200',
                height:'200',
                background:'#ffffff',
                foreground:'#000000',
                src:'/static/tools/img/logos.png'
            });
        }
        layui.use(['form', 'jquery','laydate', 'layer'], function() {
            var form = layui.form,
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            form.render();
            $("#download").click(function () {
                var canvas = $("#qrcode").find('canvas').get(0);
                try {//解决IE转base64时缓存不足,canvas转blob下载
                    var blob = canvas.msToBlob();
                    navigator.msSaveBlob(blob, '推广二维码.png');
                }catch(e){
                    var url = canvas.toDataURL('image/png');
                    var a = document.createElement('a')
                    a.setAttribute('href',url)
                    a.setAttribute('download','')
                    document.body.appendChild(a)
                    a.click();
                }
            });
            function getBlob(base64){
                var mimeString = base64.split(',')[0].split(":")[1].split(';')[0];//mime类型
                var byteString = atob(base64.split(',')[1]);//base64解码
                var arrayBuffer = new ArrayBuffer(byteString.length);//创建缓冲数组
                var intArray = new Uint8Array(arrayBuffer)//创建视图
                for(var i=0;i<byteString.length;i+=1){
                    intArray[i] = byteString.charCodeAt(i)
                }
                return new Blob([intArray],{
                    type:mimeString
                })
            }
            $("#copy").click(function () {
                var span = document.getElementById('href');
                console.log(span.value);
                span.select();
                document.execCommand("Copy");
                alert('复制成功');
            });
            //修改发放红包
            $("#update").click(function () {
                layer.open({
                    type:2,
                    title:'修改红包发放',
                    shadeClose:true,
                    shade:0.8,
                    area:['308px','308px'],
                    content:'/admin/promote/updateInfo',
                    end:function () {
                        location.reload();
                    }
                });
            });
            //修改联系信息
            $("#information").click(function () {
                layer.open({
                    type:2,
                    title: "修改联系信息",
                    shadeClose: true,
                    shade: 0.8,
                    area:['380px','380px'],
                    content:'/admin/promote/contact',
                    end:function () {
                        location.reload();
                    }
                });
            });
            $("#hbList").click(function () {
                layer.open({
                    type:2,
                    title:'红包领取记录',
                    shadeClose:true,
                    shade:0.8,
                    area:['780px','780px'],
                    content:'/admin/getRedPackageRecord',
                    end:function () {
                        location.reload();
                    }
                });
            });
            form.on('submit(formDemo)', function(data) {
                console.log(data);
            });
        });
    </script>
@endsection
@extends('common.list')