@extends('layouts.admin')
@section('title', $item->exists ? 'Edit admin' : 'New admin')
@section('content')
<x-admin.header :title="$item->exists ? $item->name : 'New admin'" :back="route('admin.admins.index')" />
<form method="POST" action="{{ $item->exists ? route('admin.admins.update', $item) : route('admin.admins.store') }}" class="max-w-2xl space-y-6" autocomplete="off">
    @csrf @if ($item->exists) @method('PUT') @endif
    <section class="a-card grid gap-4 p-5 sm:grid-cols-2">
        <x-admin.field name="name" label="Name" :value="$item->name" required />
        <x-admin.field name="email" label="Email" type="email" :value="$item->email" required />
        <x-admin.field name="role" label="Role" type="select" :value="$item->role" :options="['staff' => 'Staff: orders, payments, pages', 'owner' => 'Owner: everything, incl. settings and admins']" class="sm:col-span-2" />
        <x-admin.field name="password" :label="$item->exists ? 'New password' : 'Password'" type="password" :required="! $item->exists" autocomplete="new-password" :help="$item->exists ? 'Leave empty to keep the current one.' : 'At least 12 characters with upper and lower case letters and a number.'" />
        <x-admin.field name="password_confirmation" label="Repeat password" type="password" :required="! $item->exists" autocomplete="new-password" />
        <x-admin.field name="is_active" type="checkbox" label="Can sign in" :value="$item->is_active" class="sm:col-span-2" />
    </section>
    @include('admin.partials.form-footer', ['cancel' => route('admin.admins.index')])
</form>
@endsection
