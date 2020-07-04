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
                <input type="text" name="nickname" lay-verify="nickname" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">账号：</label>
            <div class="layui-input-inline">
                <input type="text" name="account" lay-verify="account" autocomplete="off" class="layui-input">
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
                <input type="text" name="limit[min]" placeholder="￥" value="{{$user['limit']['min']}}" readonly autocomplete="off" class="layui-input">
              </div>
              <div class="layui-form-mid">-</div>
              <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[max]" placeholder="￥" value="{{$user['limit']['max']}}" readonly autocomplete="off" class="layui-input">
              </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
              <label class="layui-form-label">和限红范围：</label>
              <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[tieMin]" placeholder="￥" autocomplete="off" value="{{$user['limit']['tieMin']}}" readonly class="layui-input">
              </div>
              <div class="layui-form-mid">-</div>
              <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[tieMax]" placeholder="￥" autocomplete="off" value="{{$user['limit']['tieMax']}}" readonly class="layui-input">
              </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
              <label class="layui-form-label">对限红范围：</label>
              <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[pairMin]" placeholder="￥" autocomplete="off" class="layui-input" value="{{$user['limit']['pairMin']}}" readonly>
              </div>
              <div class="layui-form-mid">-</div>
              <div class="layui-input-inline" style="width: 100px;">
                <input type="text" name="limit[pairMax]" placeholder="￥" autocomplete="off" class="layui-input" value="{{$user['limit']['pairMax']}}" readonly>
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
                    <input type="number" name="fee[baccarat]" lay-verify="title" readonly autocomplete="off" value="{{$user['fee']['baccarat']}}" class="layui-input">
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
                    <input type="number" name="fee[dragonTiger]" readonly lay-verify="title" value="{{$user['fee']['dragonTiger']}}" autocomplete="off" class="layui-input">
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
                    <input type="number" name="fee[niuniu]" lay-verify="title" readonly autocomplete="off" value="{{$user['fee']['niuniu']}}" class="layui-input">
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
                    <input type="number" name="fee[sangong]" readonly lay-verify="title" autocomplete="off" value="{{$user['fee']['sangong']}}" class="layui-input">
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
                    <input type="number" name="fee[A89]" readonly lay-verify="title" autocomplete="off" value="{{$user['fee']['A89']}}" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">小于或等于所属代理的A89洗码率(%)。默认:{{$user['fee']['A89']}}%</div>
            </div>
        @endif
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
            <legend>百家乐游戏赔率</legend>
        </fieldset>
        <div class="layui-form-item">
            <div class="layui-inline">
              <label class="layui-form-label">闲：</label>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[player]" lay-verify="bplayer" placeholder="" data-v="{{$user['bjlbets_fee']['player']/100}}" value="{{$user['bjlbets_fee']['player']/100}}" autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[player]" readonly placeholder="" value="{{$user['bjlbets_fee']['player']/100}}" autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">闲对：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[playerPair]" lay-verify="bplayerPair" placeholder="" data-v="{{$user['bjlbets_fee']['playerPair']/100}}" value="{{$user['bjlbets_fee']['playerPair']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[playerPair]" readonly placeholder="" value="{{$user['bjlbets_fee']['playerPair']/100}}"  autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">和：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[tie]" lay-verify="btie" placeholder="" data-v="{{$user['bjlbets_fee']['tie']/100}}" value="{{$user['bjlbets_fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[tie]" placeholder="" readonly value="{{$user['bjlbets_fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">庄：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[banker]" lay-verify="bbanker" placeholder="" data-v="{{$user['bjlbets_fee']['banker']/100}}" value="{{$user['bjlbets_fee']['banker']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[banker]" placeholder="" readonly value="{{$user['bjlbets_fee']['banker']/100}}"  autocomplete="off" class="layui-input">
                @endif
              </div>
              <div class="layui-form-mid">庄对：</div>
              <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                <input type="number" name="bjlbets_fee[bankerPair]" lay-verify="bbankerPair" placeholder="" data-v="{{$user['bjlbets_fee']['bankerPair']/100}}" value="{{$user['bjlbets_fee']['bankerPair']/100}}"  autocomplete="off" class="layui-input">
                @else
                <input type="number" name="bjlbets_fee[bankerPair]" readonly placeholder="" value="{{$user['bjlbets_fee']['bankerPair']/100}}"  autocomplete="off" class="layui-input">
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
                    <input type="number" name="lhbets_fee[dragon]" lay-verify='dragon' placeholder="" data-v="{{$user['lhbets_fee']['dragon']/100}}" value="{{$user['lhbets_fee']['dragon']/100}}"autocomplete="off" class="layui-input">
                    @else
                    <input readonly type="number" name="lhbets_fee[dragon]" placeholder="" value="{{$user['lhbets_fee']['dragon']/100}}"autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">和：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['dragon_tiger']==1)
                    <input type="number" name="lhbets_fee[tie]" lay-verify="ltie" placeholder="" data-v="{{$user['lhbets_fee']['tie']/100}}" value="{{$user['lhbets_fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                    @else
                    <input readonly type="number" name="lhbets_fee[tie]" placeholder="" value="{{$user['lhbets_fee']['tie']/100}}"  autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">虎：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['dragon_tiger']==1)
                        <input type="number" name="lhbets_fee[tiger]" lay-verify='tiger' placeholder="" data-v="{{$user['lhbets_fee']['tiger']/100}}" value="{{$user['lhbets_fee']['tiger']/100}}"  autocomplete="off" class="layui-input">
                    @else
                        <input readonly type="number" name="lhbets_fee[tiger]" placeholder="" value="{{$user['lhbets_fee']['tiger']/100}}"  autocomplete="off" class="layui-input">
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
                    <input type="number" name="nnbets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$user['nnbets_fee']['Equal']/100}}" value="{{$user['nnbets_fee']['Equal']/100}}"autocomplete="off" class="layui-input">
                    @else
                    <input readonly type="number" name="nnbets_fee[Equal]" placeholder="" value="{{$user['nnbets_fee']['Equal']/100}}"autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">翻倍：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[Double]" lay-verify='double' placeholder="" data-v="{{$user['nnbets_fee']['Double']/100}}" value="{{$user['nnbets_fee']['Double']/100}}"  autocomplete="off" class="layui-input">
                    @else
                    <input readonly type="number" name="nnbets_fee[Double]" placeholder="" value="{{$user['nnbets_fee']['Double']/100}}"  autocomplete="off" class="layui-input">
                    @endif
                </div>
                <div class="layui-form-mid">超倍：</div>
                <div class="layui-input-inline" style="width: 100px;">
                    @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$user['nnbets_fee']['SuperDouble']/100}}" value="{{$user['nnbets_fee']['SuperDouble']/100}}"  autocomplete="off" class="layui-input">
                    @else
                    <input readonly type="number" name="nnbets_fee[SuperDouble]" placeholder="" value="{{$user['nnbets_fee']['SuperDouble']/100}}"  autocomplete="off" class="layui-input">
                    @endif
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <input type="checkbox" name="is_show" id="isShow" title="报表中显示洗码量">
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
                $("input[name='account']").val(Math.random().toString().slice(-11));
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
                    if (value.length < 5 || value.length > 11){
                        return '密码长度大于5，小于11'
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
                    var v = $("input[name='bjlbets_fee[player]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率';
                    }
                },
                bplayerPair:function(value){
                    var v = $("input[name='bjlbets_fee[playerPair]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率';
                    }
                },
                btie:function(value){
                    var v = $("input[name='bjlbets_fee[tie]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率';
                    }
                },
                bbanker:function(value){
                    var v = $("input[name='bjlbets_fee[banker]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                bbankerPair:function(value){
                    var v = $("input[name='bjlbets_fee[bankerPair]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                dragon:function(value){
                    var v = $("input[name='lhbets_fee[dragon]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                ltie:function(value){
                    var v = $("input[name='lhbets_fee[tie]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                tiger:function(value){
                    var v = $("input[name='lhbets_fee[tiger]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                equal:function(value){
                    var v = $("input[name='nnbets_fee[Equal]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                double:function(value){
                    var v = $("input[name='nnbets_fee[Double]']").attr('data-v');
                    if(value>v){
                        return '不能大于平台的赔率'
                    }
                },
                superDouble:function(value){
                    var v = $("input[name='nnbets_fee[SuperDouble]']").attr('data-v');
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
