@section('title', '角色列表')
@section('header')
@endsection
@section('table')
<form class="layui-form">
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
            <input type="text" name="username" lay-verify="username" lay autocomplete="off" class="layui-input">
        </div>
        <div class="layui-input-inline">
            <button type="button" class="layui-btn" id="account">系统生成</button>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">昵称：</label>
        <div class="layui-input-inline">
            <input type="text" name="nickname" lay-verify="nickname" lay autocomplete="off" class="layui-input">
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
            <input type="text" name="limit[min]" placeholder="￥" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" value="10" readonly autocomplete="off" class="layui-input">
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[max]" placeholder="￥" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" value="50000" readonly autocomplete="off" class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">和限红范围：</label>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[tieMin]" placeholder="￥" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" autocomplete="off" value="10" readonly class="layui-input">
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[tieMax]" placeholder="￥" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" autocomplete="off"  value="5000" readonly class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">对限红范围：</label>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[pairMin]" placeholder="￥" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" autocomplete="off"  class="layui-input" value="10" readonly>
          </div>
          <div class="layui-form-mid">-</div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="text" name="limit[pairMax]" placeholder="￥" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" autocomplete="off" class="layui-input" value="5000" readonly>
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">百家乐洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[baccarat]" lay-verify="title" autocomplete="off" value="{{$user['fee']['baccarat']}}" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的百家乐洗码率(%)。默认:{{$user['fee']['baccarat']}}%</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">龙虎洗码率：</label>
        <div class="layui-input-inline">
            <input type="number" name="fee[dragonTiger]" lay-verify="title" value="{{$user['fee']['dragonTiger']}}" autocomplete="off" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的龙虎洗码率(%)。默认:{{$user['fee']['dragonTiger']}}%</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">牛牛洗码率：</label>
        <div class="layui-input-inline">
        <input type="number" name="fee[niuniu]" lay-verify="title" autocomplete="off" value="{{$user['fee']['niuniu']}}" class="layui-input">
        </div>
    <div class="layui-form-mid layui-word-aux">小于或等于所属代理的牛牛洗码率(%)。默认:{{$user['fee']['niuniu']}}%</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">三公洗码率：</label>
        <div class="layui-input-inline">
        <input type="number" name="fee[sangong]" lay-verify="title" autocomplete="off" value="{{$user['fee']['sangong']}}" class="layui-input">
        </div>
    <div class="layui-form-mid layui-word-aux">小于或等于所属代理的三公洗码率(%)。默认:{{$user['fee']['sangong']}}%</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">A89洗码率：</label>
        <div class="layui-input-inline">
        <input type="number" name="fee[A89]" lay-verify="title" autocomplete="off" value="{{$user['fee']['A89']}}" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的A89洗码率(%)。默认:{{$user['fee']['A89']}}%</div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">占股率：</label>
        <div class="layui-input-inline">
            <input type="number" name="proportion" lay-verify="title" autocomplete="off" value="0" class="layui-input">
        </div>
        <div class="layui-form-mid layui-word-aux">小于或等于所属代理的占股率(%)。默认:0</div>
    </div>
    @if($user['baccarat']==1 || $user['dragon_tiger']==1 || $user['niuniu']==1 || $user['sangong']==1 || $user['A89']==1)
    <div class="layui-form-item">
        <label class="layui-form-label">抽水权限：</label>
        <div class="layui-input-block">
            @if($user['baccarat']==1)
                <input type="checkbox" id="baccarat" name="baccarat" lay-skin="primary" title="百家乐">
            @endif
            @if($user['dragon_tiger']==1)
                <input type="checkbox" id="dragon_tiger" name="dragon_tiger" lay-skin="primary" title="龙虎">
            @endif
            @if($user['niuniu']==1)
                <input type="checkbox" id="niuniu" name="niuniu" lay-skin="primary" title="牛牛">
            @endif
            @if($user['sangong']==1)
                <input type="checkbox" id="sangong" name="sangong" lay-skin="primary" title="三公">
            @endif
            @if($user['A89']==1)
                <input type="checkbox" id="A89" name="A89" lay-skin="primary" title="A89">
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
                    <input type="number" name="bjlbets_fee[player]" lay-verify="bplayer" placeholder="" data-v="{{$user['bjlbets_fee']['player']}}" value="{{$user['bjlbets_fee']['player']}}" autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[player]" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['bjlbets_fee']['player']}}" autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">闲对：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[playerPair]" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="bplayerPair" placeholder="" data-v="{{$user['bjlbets_fee']['playerPair']}}" value="{{$user['bjlbets_fee']['playerPair']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[playerPair]" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['bjlbets_fee']['playerPair']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">和：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[tie]" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="btie" placeholder="" data-v="{{$user['bjlbets_fee']['tie']}}" value="{{$user['bjlbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[tie]" placeholder="" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" value="{{$user['bjlbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">庄：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[banker]" lay-verify="bbanker" placeholder="" data-v="{{$user['bjlbets_fee']['banker']}}" value="{{$user['bjlbets_fee']['banker']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[banker]" placeholder="" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" value="{{$user['bjlbets_fee']['banker']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">庄对：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['baccarat']==1)
                    <input type="number" name="bjlbets_fee[bankerPair]" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" lay-verify="bbankerPair" placeholder="" data-v="{{$user['bjlbets_fee']['bankerPair']}}" value="{{$user['bjlbets_fee']['bankerPair']}}"  autocomplete="off" class="layui-input">
                @else
                    <input type="number" name="bjlbets_fee[bankerPair]" readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['bjlbets_fee']['bankerPair']}}"  autocomplete="off" class="layui-input">
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
                    <input type="number" name="lhbets_fee[dragon]" lay-verify='dragon' placeholder="" data-v="{{$user['lhbets_fee']['dragon']}}" value="{{$user['lhbets_fee']['dragon']}}"autocomplete="off" class="layui-input">
                @else
                    <input  readonly type="number" name="lhbets_fee[dragon]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['lhbets_fee']['dragon']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">和：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['dragon_tiger']==1)
                    <input type="number" name="lhbets_fee[tie]" lay-verify="ltie" placeholder="" data-v="{{$user['lhbets_fee']['tie']}}" value="{{$user['lhbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="lhbets_fee[tie]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['lhbets_fee']['tie']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">虎：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['dragon_tiger']==1)
                    <input type="number" name="lhbets_fee[tiger]" lay-verify='tiger' placeholder="" data-v="{{$user['lhbets_fee']['tiger']}}" value="{{$user['lhbets_fee']['tiger']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="lhbets_fee[tiger]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['lhbets_fee']['tiger']}}"  autocomplete="off" class="layui-input">
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
                    <input type="number" name="nnbets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$user['nnbets_fee']['Equal']}}" value="{{$user['nnbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="nnbets_fee[Equal]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['nnbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">翻倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[Double]" lay-verify='double' placeholder="" data-v="{{$user['nnbets_fee']['Double']}}" value="{{$user['nnbets_fee']['Double']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="nnbets_fee[Double]" placeholder="" value="{{$user['nnbets_fee']['Double']}}" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">超倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['niuniu']==1)
                    <input type="number" name="nnbets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$user['nnbets_fee']['SuperDouble']}}" value="{{$user['nnbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" type="number" name="nnbets_fee[SuperDouble]" placeholder="" value="{{$user['nnbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
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
                    <input type="number" name="sgbets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$user['sgbets_fee']['Equal']}}" value="{{$user['sgbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="sgbets_fee[Equal]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['sgbets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">翻倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['sangong']==1)
                    <input type="number" name="sgbets_fee[Double]" lay-verify='double' placeholder="" data-v="{{$user['sgbets_fee']['Double']}}" value="{{$user['sgbets_fee']['Double']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="sgbets_fee[Double]" placeholder="" value="{{$user['sgbets_fee']['Double']}}" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">超倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['sangong']==1)
                    <input type="number" name="sgbets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$user['sgbets_fee']['SuperDouble']}}" value="{{$user['sgbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" type="number" name="sgbets_fee[SuperDouble]" placeholder="" value="{{$user['sgbets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
        </div>
    </div>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 50px;">
        <legend>A89游戏赔率</legend>
    </fieldset>
    <div class="layui-form-item">
        <div class="layui-inline">
            <label class="layui-form-label">平倍：</label>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['A89']==1)
                    <input type="number" name="a89bets_fee[Equal]" lay-verify="equal" placeholder="" data-v="{{$user['a89bets_fee']['Equal']}}" value="{{$user['a89bets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @else
                    <input readonly type="number" name="a89bets_fee[Equal]" style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" placeholder="" value="{{$user['a89bets_fee']['Equal']}}"autocomplete="off" class="layui-input">
                @endif
            </div>
            <div class="layui-form-mid">超倍：</div>
            <div class="layui-input-inline" style="width: 100px;">
                @if($user['A89']==1)
                    <input type="number" name="a89bets_fee[SuperDouble]" lay-verify="superDouble" placeholder="" data-v="{{$user['a89bets_fee']['SuperDouble']}}" value="{{$user['a89bets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @else
                    <input readonly style="border: 1px solid #DDD;background-color: #F5F5F5;color: #ACA899;" type="number" name="a89bets_fee[SuperDouble]" placeholder="" value="{{$user['a89bets_fee']['SuperDouble']}}"  autocomplete="off" class="layui-input">
                @endif
            </div>
        </div>
    </div>
    <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">备注：</label>
        <div class="layui-input-inline">
            <textarea name="remark" id="remark" placeholder="请输入备注" class="layui-textarea">姓名：
电话：
备注：
            </textarea>
        </div>
    </div>
    @if($user['is_allow']==1)
        <div class="layui-form-item">
            <div class="layui-input-block">
                <input type="checkbox" name="is_allow" title="是否允许其直属会员在线充值">
            </div>
        </div>
    @endif
    @if($user['is_allow_draw']==1)
        <div class="layui-form-item">
            <div class="layui-input-block">
                <input type="checkbox" name="is_allow_draw" title="是否允许其直属会员在线提现">
            </div>
        </div>
    @endif
    @if($user['is_allow_password']==1)
        <div class="layui-form-item">
            <div class="layui-input-block">
                <input type="checkbox" name="is_allow_password" title="限制代理提现和修改密码">
            </div>
        </div>
    @endif
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
            var form = layui.form,
                $ = layui.jquery,
                laydate = layui.laydate,
                layer = layui.layer
            ;
            form.render();
            form.verify({
                username:function(value){
                    var reg = new RegExp('^[0-9]{6}$');
                    if (!reg.test(value)){
                        return '格式错误';
                    }
                    if(value.length!=6){
                        return '请输入6位，并且大于100000，小于1000000'
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
            $('input[name="username"]').blur(function () {
                var username = $(this).val();
                if(username.length==0){
                    layer.msg('账号不能为空',{shift: 6,icon:5});
                }else{
                    $.ajax({
                        headers:{
                            'X-CSRF-TOKEN':$('input[name="_token"]').val()
                        },
                        url:"{{url('/admin/agentList/accountUnique')}}",
                        type:"post",
                        data:{
                            "account":username
                        },
                        dataType:"json",
                        success:function (res) {
                            if(res.status==0){
                                layer.msg(res.msg,{shift:6,icon:5});
                            }
                        }
                    });
                }
            });
            $("#account").click(function(){
                //console.log(Math.random().toString().slice(-6));
                //清空数据
                $("input[name='username']").val('');
                $("input[name='username']").val(Math.floor(Math.random() * (999999-100000)) + 100000);
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
                            var topWindow = $(window.parent.document);
                            var ul = topWindow.find('#nav').children(':first').children();
                            var count;
                            for(var x=0;x<ul.length;x++){
                                if(4==$(ul[x]).attr('lay-id')){
                                    count=x;
                                    break;
                                }
                            }
                            $(ul[count]).remove();
                            var iframe = topWindow.find('#nav').children(':last').children();
                            $(iframe[count]).remove();
                            var isActive = topWindow.find('#nav').children(':first').children('li[lay-id="2"]');
                            if (isActive.length>0){
                                var a;
                                var arr = topWindow.find('#nav').children(':first').children();
                                for (var i=0;i<arr.length;i++){
                                    var layId = $(arr[i]).attr('lay-id');
                                    if(layId==2){
                                        a = i
                                        break;
                                    }
                                }
                                //获取到当前选中的tab选项卡
                                var index = topWindow.find('#nav').children(':first').children('li[class="layui-this"]');
                                index.removeClass('layui-this');
                                isActive.addClass('layui-this');
                                var indexHtml = topWindow.find('#nav').children(':last').children('div[class="layui-tab-item layui-show"]');
                                indexHtml.removeClass('layui-show');
                                //获取iframe数组
                                var iframe = topWindow.find('#nav').children(':last').children();
                                $(iframe[a]).addClass('layui-show')
                            }else{
                                var id = 2;
                                var url = "{{url('/admin/agentList')}}";
                                var title = '代理列表';
                                var tabUL = topWindow.find('#nav').children(':first');
                                var str = '<li lay-id="'+id+'" class="layui-this">'+title+'<i class="layui-icon layui-unselect layui-tab-close" onclick="tabClose(this)">ဆ</i></li>';
                                tabUL.append(str);
                                var indexHtml = topWindow.find('#nav').children(':last').children('div[class="layui-tab-item layui-show"]');
                                indexHtml.removeClass('layui-show');
                                var tabDiv = topWindow.find('#nav').children(':last');
                                var str1 = '<div class="layui-tab-item layui-show"><iframe frameborder="0" style="width: 100%;height: calc(100vh - 157px)" name="'+title+'" src="'+url+'"></iframe></div>';
                                tabDiv.append(str1)
                            }
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