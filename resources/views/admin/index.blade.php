@extends('admin.layouts.master')

@section('title', 'Dashboard')

@section('content')

    @livewire('admin-dashboard')

@endsection
@section('page-title')
    <ol class="breadcrumb m-0 float-end">
        <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
@endsection
