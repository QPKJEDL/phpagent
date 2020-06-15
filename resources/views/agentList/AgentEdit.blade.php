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
              <input type="text" name="fee[baccarat]" lay-verify="required" readonly value="{{$info['fee']['baccarat']}}" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            <label class="layui-form-label">龙虎洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[dragonTiger]" lay-verify="required" autocomplete="off" value="{{$info['fee']['dragonTiger']}}" readonly class="layui-input">
            </div>
          </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">牛牛洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[niuniu]" lay-verify="required" value="{{$info['fee']['niuniu']}}" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">三公洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[sangong]" lay-verify="required" value="{{$info['fee']['sangong']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">A89洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[A89]" lay-verify="required" value="{{$info['fee']['A89']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">占股率：</label>
        <div class="layui-input-inline">
            <input type="number" name="proportion" lay-verify="proportion" autocomplete="off" data-value="{{$info['proportion']}}" value="{{$info['proportion']}}" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[min]" lay-verify="required" value="{{$info['limit']['min']}}" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[max]" lay-verify="required" value="{{$info['limit']['max']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小和限红：</label>
            <div class="layui-input-block">
              <input type="numberv" name="limit[tieMin]" lay-verify="required" value="{{$info['limit']['tieMin']}}" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大和限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[tieMax]" lay-verify="required" value="{{$info['limit']['tieMax']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小对限红：</label>
            <div class="layui-input-block">
              <input type="numberv" name="limit[pairMin]" lay-verify="required" value="{{$info['limit']['pairMin']}}" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大对限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[pairMax]" lay-verify="required" value="{{$info['limit']['pairMax']}}" autocomplete="off" class="layui-input" readonly>
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
                    <input  readonly type="number" name="lhbets_fee[dragon]" placeholder="" value="{{$user['lhbets_fee']['dragon']/100}}"autocomplete="off" class="layui-input">
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
                proportion:function (value) {
                    var v = $("input[name='proportion']").attr('data-value');
                    if (value>v && value<0){
                        return '请重新输入，格式错误'
                    }
                }
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