@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page_title', 'Pengaturan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        @livewire('system-settings')
    </div>
</div>
@endsection
