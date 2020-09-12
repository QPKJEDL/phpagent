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
              <input type="text" name="fee[baccarat]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" readonly value="{{$info['fee']['baccarat']}}" autocomplete="off" class="layui-input">
            </div>
          </div>
          <div class="layui-inline">
            <label class="layui-form-label">龙虎洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[dragonTiger]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" autocomplete="off" value="{{$info['fee']['dragonTiger']}}" readonly class="layui-input">
            </div>
          </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">牛牛洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[niuniu]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['fee']['niuniu']}}" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">三公洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[sangong]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['fee']['sangong']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">A89洗码率：</label>
            <div class="layui-input-block">
              <input type="text" name="fee[A89]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['fee']['A89']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">占股率：</label>
        <div class="layui-input-inline">
            <input type="number" readonly name="proportion" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="proportion" autocomplete="off" data-value="{{$info['proportion']}}" value="{{$info['proportion']}}" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[min]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['limit']['min']}}" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[max]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['limit']['max']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小和限红：</label>
            <div class="layui-input-block">
              <input type="numberv" name="limit[tieMin]" lay-verify="required" value="{{$info['limit']['tieMin']}}" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大和限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[tieMax]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['limit']['tieMax']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">最小对限红：</label>
            <div class="layui-input-block">
              <input type="numberv" name="limit[pairMin]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['limit']['pairMin']}}" autocomplete="off" readonly class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">最大对限红：</label>
            <div class="layui-input-block">
              <input type="number" name="limit[pairMax]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="required" value="{{$info['limit']['pairMax']}}" autocomplete="off" class="layui-input" readonly>
            </div>
        </div>
    </div>
    @if($user['baccarat']==1 || $user['dragon_tiger']==1 || $user['niuniu']==1 || $user['sangong']==1 || $user['A89']==1)
        <div class="layui-form-item">
            <label class="layui-form-label">抽水权限：</label>
            <div class="layui-input-block">
                @if($user['baccarat']==1)
                    <input type="checkbox" id="baccarat" name="baccarat" lay-skin="primary" title="百家乐" {{isset($info['baccarat'])&&$info['baccarat']==1?'checked':''}}>
                @endif
                @if($user['dragon_tiger']==1)
                    <input type="checkbox" id="dragon_tiger" name="dragon_tiger" lay-skin="primary" title="龙虎" {{isset($info['dragon_tiger'])&&$info['dragon_tiger']==1?'checked':''}}>
                @endif
                @if($user['niuniu']==1)
                    <input type="checkbox" id="niuniu" name="niuniu" lay-skin="primary" title="牛牛" {{isset($info['niuniu'])&&$info['niuniu']==1?'checked':''}}>
                @endif
                @if($user['sangong']==1)
                    <input type="checkbox" id="sangong" name="sangong" lay-skin="primary" title="三公" {{isset($info['sangong'])&&$info['sangong']==1?'checked':''}}>
                @endif
                @if($user['A89']==1)
                    <input type="checkbox" id="A89" name="A89" lay-skin="primary" title="A89" {{isset($info['A89'])&&$info['A89']==1?'checked':''}}>
                @endif
            </div>
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
                    <input type="number" name="bjlbets_fee[player]" lay-verify="bplayer" placeholder="" data-v="{{$user['bjlbets_fee']['player']}}" value="{{$info['bjlbets_fee']['player']}}" autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[player]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly placeholder="" value="{{$info['bjlbets_fee']['player']}}" autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">闲对：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[playerPair]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly lay-verify="bplayerPair" placeholder="" data-v="{{$user['bjlbets_fee']['playerPair']}}" value="{{$info['bjlbets_fee']['playerPair']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[playerPair]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly placeholder="" value="{{$info['bjlbets_fee']['playerPair']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">和：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[tie]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly lay-verify="btie" placeholder="" data-v="{{$user['bjlbets_fee']['tie']}}" value="{{$info['bjlbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[tie]" placeholder="" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly value="{{$info['bjlbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">庄：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[banker]" lay-verify="bbanker" placeholder="" data-v="{{$user['bjlbets_fee']['banker']}}" value="{{$info['bjlbets_fee']['banker']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[banker]" placeholder="" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly value="{{$info['bjlbets_fee']['banker']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">庄对：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[bankerPair]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly lay-verify="bbankerPair" placeholder="" data-v="{{$user['bjlbets_fee']['bankerPair']}}" value="{{$info['bjlbets_fee']['bankerPair']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[bankerPair]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" readonly placeholder="" value="{{$info['bjlbets_fee']['bankerPair']}}"  autocomplete="off" class="layui-input">
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
                    <input type="number" name="lhbets_fee[dragon]" lay-verify='dragon' placeholder="" data-v="{{$user['lhbets_fee']['dragon']}}" value="{{$info['lhbets_fee']['dragon']}}"autocomplete="off" class="layui-input">
                @else
                    <input  readonly type="number" name="lhbets_fee[dragon]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['lhbets_fee']['dragon']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">和：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['dragon_tiger']==1)
                    <input type="number" name="lhbets_fee[tie]" lay-verify="ltie" placeholder="" data-v="{{$user['lhbets_fee']['tie']}}" value="{{$info['lhbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="lhbets_fee[tie]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['lhbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">虎：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['dragon_tiger']==1)
                    <input type="number" name="lhbets_fee[tiger]" lay-verify='tiger' placeholder="" data-v="{{$user['lhbets_fee']['tiger']}}" value="{{$info['lhbets_fee']['tiger']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="lhbets_fee[tiger]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['lhbets_fee']['tiger']}}"  autocomplete="off" class="layui-input">
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
                    <input type="number" name="nnbets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$user['nnbets_fee']['Equal']}}" value="{{$info['nnbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="nnbets_fee[Equal]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['nnbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">翻倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[Double]" lay-verify='double' placeholder="" data-v="{{$user['nnbets_fee']['Double']}}" value="{{$info['nnbets_fee']['Double']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="nnbets_fee[Double]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['nnbets_fee']['Double']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">超倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$user['nnbets_fee']['SuperDouble']}}" value="{{$info['nnbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="nnbets_fee[SuperDouble]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['nnbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
        </div>
    </div>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
        <legend>三公游戏赔率</legend>
    </fieldset>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">平倍：</label>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['sangong']==1)
                    <input type="number" name="sgbets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$user['sgbets_fee']['Equal']}}" value="{{$info['sgbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="sgbets_fee[Equal]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['sgbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">翻倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['niuniu']==1)
                    <input type="number" name="sgbets_fee[Double]" lay-verify='double' placeholder="" data-v="{{$user['sgbets_fee']['Double']}}" value="{{$info['sgbets_fee']['Double']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="sgbets_fee[Double]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['sgbets_fee']['Double']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">超倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['niuniu']==1)
                    <input type="number" name="sgbets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$user['sgbets_fee']['SuperDouble']}}" value="{{$info['sgbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="sgbets_fee[SuperDouble]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['sgbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
        </div>
    </div>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
        <legend>三公游戏赔率</legend>
    </fieldset>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">平倍：</label>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['A89']==1)
                    <input type="number" name="a89bets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$user['a89bets_fee']['Equal']}}" value="{{$info['a89bets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="a89bets_fee[Equal]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['a89bets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">超倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['A89']==1)
                    <input type="number" name="a89bets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$user['a89bets_fee']['SuperDouble']}}" value="{{$info['a89bets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="a89bets_fee[SuperDouble]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$info['a89bets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
        </div>
    </div>
    {{--<div class="layui-form-item">
        <div class="layui-input-block">
            <input type="checkbox" name="is_allow" title="允许其直属会员在线充值" @if($info['is_allow']==1) checked="" @endif>
        </div>
    </div>--}}
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
        window.onload=function(){
            var id = $("input[name='id']").val();
            if (id!=0){
                var baccarat = document.getElementById("baccarat");
                var dragonTiger = document.getElementById('dragon_tiger');
                var niuniu = document.getElementById('niuniu');
                var sangong = document.getElementById('sangong');
                var A89 = document.getElementById('A89');
                if (baccarat.checked){
                    baccarat.setAttribute("disabled","");
                }
            }
        }
        layui.use(['form','jquery','layer'], function() {
            var form = layui.form
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