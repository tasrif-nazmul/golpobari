@extends('errors.layout')
@section('title', 'Not found')
@section('code', '404')
@section('heading', 'এই ঠিকানায় কোনো গল্প নেই')
@section('message', 'লিংকটা হয়তো ভুল, অথবা পেজটি সরিয়ে নেওয়া হয়েছে। বানানটা আরেকবার দেখে নিন।')
@section('secondary')<a class="g" href="{{ url('/templates') }}">টেমপ্লেট দেখুন</a>@endsection
