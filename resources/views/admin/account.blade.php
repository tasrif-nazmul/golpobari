@extends('layouts.admin')
@section('title', 'My account')
@section('content')
<x-admin.header title="My account" :sub="$admin->email.' · '.ucfirst($admin->role)" />
<form method="POST" action="{{ route('admin.account.update') }}" class="max-w-xl space-y-6">
    @csrf @method('PUT')
    <section class="a-card space-y-4 p-5">
        <x-admin.field name="name" label="Name" :value="$admin->name" required />
        <hr class="border-stone-100">
        <p class="text-sm text-stone-500">To change your password, fill in both fields below. Other signed-in devices will be signed out.</p>
        <x-admin.field name="password" label="New password" type="password" autocomplete="new-password" help="At least 12 characters with upper and lower case letters and a number." />
        <x-admin.field name="password_confirmation" label="Repeat new password" type="password" autocomplete="new-password" />
        <hr class="border-stone-100">
        <x-admin.field name="current_password" label="Current password" type="password" required autocomplete="current-password" help="Needed to save any change." />
    </section>
    <div class="flex justify-end"><button class="a-btn a-btn-primary">Save</button></div>
</form>
@endsection
