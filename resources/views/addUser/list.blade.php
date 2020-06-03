@section('title', '代理列表')
@section('header')
@endsection
@section('table')
    <form class="layui-form">
        <div class="layui-form-item">
            <label class="layui-form-label">直属上级：</label>
            <div class="layui-input-inline">
                <input type="hidden" name="agent_id" value="{{$user['id']}}"/>
                <input type="text" name="user" lay-verify="title" disabled autocomplete="off" value="{{$user['nickname']}}" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">昵称：</label>
            <div class="layui-input-inline">
                <input type="text" name="nickname" lay-verify="nickname" lay autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">账号：</label>
            <div class="layui-input-inline">
                <input type="text" name="account" lay-verify="account" lay autocomplete="off" class="layui-input">
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
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
            <legend>百家乐游戏赔率</legend>
        </fieldset>
        <div class="layui-form-item">
            <div class="layui-inline">
              <label class="layui-form-label">闲：</label>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[player]" lay-verify="bplayer" placeholder="" data-v="{{$game[0]['fee']['player']/100}}" value="{{$game[0]['fee']['player']/100}}" autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[player]" readonly disabled placeholder="" value="{{$game[0]['fee']['player']/100}}" autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">闲对：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[playerPair]" lay-verify="bplayerPair" placeholder="" data-v="{{$game[0]['fee']['playerPair']/100}}" value="{{$game[0]['fee']['playerPair']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[playerPair]" disabled readonly placeholder="" value="{{$game[0]['fee']['playerPair']/100}}"  autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">和：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[tie]" lay-verify="btie" placeholder="" data-v="{{$game[0]['fee']['tie']/100}}" value="{{$game[0]['fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[tie]" placeholder="" disabled readonly value="{{$game[0]['fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">庄：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[banker]" lay-verify="bbanker" placeholder="" data-v="{{$game[0]['fee']['banker']/100}}" value="{{$game[0]['fee']['banker']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[banker]" placeholder="" disabled readonly value="{{$game[0]['fee']['banker']/100}}"  autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">庄对：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[bankerPair]" lay-verify="bbankerPair" placeholder="" data-v="{{$game[0]['fee']['bankerPair']/100}}" value="{{$game[0]['fee']['bankerPair']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[bankerPair]" disabled readonly placeholder="" value="{{$game[0]['fee']['bankerPair']/100}}"  autocomplete="off" class="layui-input">
                @endif
              </div>
            </div>
        </div>
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
            <legend>龙虎游戏赔率</legend>
        </fieldset>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">龙：</label>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['dragon_tiger']==1)
                    <input type="number" name="lhbets_fee[dragon]" lay-verify='dragon' placeholder="" data-v="{{$game[1]['fee']['dragon']/100}}" value="{{$game[1]['fee']['dragon']/100}}"autocomplete="off" class="layui-input">
                    @else
                    <input disabled readonly type="number" name="lhbets_fee[dragon]" placeholder="" value="{{$game[1]['fee']['dragon']/100}}"autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">和：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['dragon_tiger']==1)
                    <input type="number" name="lhbets_fee[tie]" lay-verify="ltie" placeholder="" data-v="{{$game[1]['fee']['tie']/100}}" value="{{$game[1]['fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                    @else
                    <input disabled readonly type="number" name="lhbets_fee[tie]" placeholder="" value="{{$game[1]['fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">虎：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['dragon_tiger']==1)
                        <input type="number" name="lhbets_fee[tiger]" lay-verify='tiger' placeholder="" data-v="{{$game[1]['fee']['tiger']/100}}" value="{{$game[1]['fee']['tiger']/100}}"  autocomplete="off" class="layui-input">
                    @else
                        <input disabled readonly type="number" name="lhbets_fee[tiger]" placeholder="" value="{{$game[1]['fee']['tiger']/100}}"  autocomplete="off" class="layui-input">
                    @endif
                </div>
            </div>
        </div>
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
            <legend>牛牛游戏赔率</legend>
        </fieldset>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">平倍：</label>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$game[2]['fee']['Equal']/100}}" value="{{$game[2]['fee']['Equal']/100}}"autocomplete="off" class="layui-input">
                    @else
                    <input disabled readonly type="number" name="nnbets_fee[Equal]" placeholder="" value="{{$game[2]['fee']['Equal']/100}}"autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">翻倍：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[Double]" lay-verify='double' placeholder="" data-v="{{$game[2]['fee']['Double']/100}}" value="{{$game[2]['fee']['Double']/100}}"  autocomplete="off" class="layui-input">
                    @else
                    <input disabled readonly type="number" name="nnbets_fee[Double]" placeholder="" value="{{$game[2]['fee']['Double']/100}}"  autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">超倍：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$game[2]['fee']['SuperDouble']/100}}" value="{{$game[2]['fee']['SuperDouble']/100}}"  autocomplete="off" class="layui-input">
                    @else
                    <input disabled readonly type="number" name="nnbets_fee[SuperDouble]" placeholder="" value="{{$game[2]['fee']['SuperDouble']/100}}"  autocomplete="off" class="layui-input">
                    @endif
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
        layui.use(['form', 'jquery','laydate', 'layer','element'], function() {
            var form = layui.form(),
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer,
                element = layui.element;
            ;
            laydate({istoday: true});
            $("#account").click(function(){
                //console.log(Math.random().toString().slice(-6));
                //清空数据
                $("input[name='account']").val('');
                $("input[name='account']").val(Math.random().toString().slice(-6));
            });
            form.render();
            form.verify({
                nickname:function(value){
                    if(value.length<0){
                        return '请输入昵称'
                    }
                },
                account:function(value){
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
                },
                bplayer:function(value){
                    var v = $("input[name='bjlbets_fee[player]']").val();
                    if(value>v){
                        return '不能大于平台的赔率';
                    }
                },
                bplayerPair:function(value){
                    var v = $("input[name='bjlbets_fee[playerPair]']").val();
                    if(value>v){
                        return '不能大于平台的赔率';
                    }
                },
                btie:function(value){
                    var v = $("input[name='bjlbets_fee[tie]']").val();
                    if(value>v){
                        return '不能大于平台的赔率';
                    }
                },
                bbanker:function(value){
                    var v = $("input[name='bjlbets_fee[banker]']").val();
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                bbankerPair:function(value){
                    var v = $("input[name='bjlbets_fee[bankerPair]']").val();
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                dragon:function(value){
                    var v = $("input[name='lhbets_fee[dragon]']").val();
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                ltie:function(value){
                    var v = $("input[name='lhbets_fee[tie]']").val();
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                tiger:function(value){
                    var v = $("input[name='lhbets_fee[tiger]']").val()
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                equal:function(value){
                    var v = $("input[name='nnbets_fee[Equal]']").val();
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                double:function(value){
                    var v = $("input[name='nnbets_fee[Double]']").val()
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                superDouble:function(value){
                    var v = $("input[name='nnbets_fee[SuperDouble]']").val()
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                }
            });
            form.on('submit(formDemo)', function(data) {
                var data = $('form').serializeArray();
                $.ajax({
                    url:"{{url('/admin/addUser')}}",
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
                    }
                });
                return false;
            });
        });
    </script>
@endsection
@extends('common.list')
