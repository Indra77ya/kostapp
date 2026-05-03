@extends('layouts.app')

@section('title', 'Manajemen Lokasi')
@section('page_title', 'Manajemen Lokasi')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        @livewire('location-manager')
    </div>
</div>
@endsection
