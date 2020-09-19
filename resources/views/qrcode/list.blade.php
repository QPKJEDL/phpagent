@section('title', '角色列表')
@section('header')
@endsection
@section('table')
    <div id="qrcode"></div>
@endsection
@section('js')
    <script>
        var str = "{{$url}}";
        new QRCode(document.getElementById("qrcode"), str);  // 设置要生成二维码的链接
    </script>
@endsection
@extends('common.list')