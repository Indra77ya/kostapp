@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h3 class="card-title mb-1">Selamat Datang, {{ Auth::user()->name }}!</h3>
                        <div class="text-secondary">
                            Anda login sebagai: <span class="badge bg-primary text-white ms-1">{{ Auth::user()->getRoleNames()->first() }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-info-lt p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bolt me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l-10 12h7l-1 7l10 -12h-7l1 -7z" /></svg>
                            Sistem Real-time Terintegrasi (Livewire & Laravel Reverb)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        @livewire('dashboard-stats')
    </div>
</div>
@endsection
