@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page_title', '')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Sistem
                </div>
                <h2 class="page-title">
                    Pengaturan Sistem
                </h2>
            </div>
        </div>
    </div>

    @livewire('system-settings')
</div>
@endsection
