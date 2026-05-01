@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p>Anda login sebagai: <strong>{{ Auth::user()->getRoleNames()->first() }}</strong></p>
                <div class="alert alert-info">
                    Sistem ini sudah terintegrasi dengan <strong>Laravel Reverb</strong> dan <strong>Livewire</strong> untuk fitur real-time.
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        @livewire('dashboard-stats')
    </div>

    <div class="col-12">
        @livewire('test-realtime')
    </div>
</div>
@endsection
