@extends('layouts.app')

@section('title', 'Manajemen Kamar')
@section('page_title', '')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        @livewire('room-manager')
    </div>
</div>
@endsection
