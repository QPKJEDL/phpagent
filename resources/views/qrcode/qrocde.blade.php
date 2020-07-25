@section('title', '角色列表')
@section('header')
@endsection
@section('table')
    <input id="id" type="hidden" value="{{$id}}">
    <div id="qrcode"></div>
@endsection
@section('js')
    <script>
        var id = document.getElementById('id').value
        var str = '/admin/userRegister/'+id;
        new QRCode(document.getElementById("qrcode"), window.location.host+str);  // 设置要生成二维码的链接
    </script>
@endsection
@extends('common.list')