@extends('errors.layout')
@section('title', 'Expired')
@section('code', '419')
@section('heading', 'পেজটি অনেকক্ষণ খোলা ছিল')
@section('message', 'নিরাপত্তার জন্য সেশন শেষ হয়ে গেছে। পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।')
@section('secondary')<a class="g" href="javascript:history.back()">ফিরে যান</a>@endsection
