@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page_title', 'Pengaturan')

@section('content')
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills" id="settings-tabs">
                    <a href="{{ route('profile.edit') }}" class="nav-link text-start py-3 px-4 border-0 rounded-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                        Profil Koperasi
                    </a>
                    <a href="{{ route('profile.edit') }}#security" class="nav-link text-start py-3 px-4 border-0 rounded-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 11m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" /><path d="M12 16m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                        Keamanan
                    </a>
                    <a href="{{ route('settings') }}" class="nav-link active text-start py-3 px-4 border-0 rounded-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-settings me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                        Sistem
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        @livewire('system-settings')
    </div>
</div>
@endsection

@push('styles')
<style>
    #settings-tabs .nav-link {
        color: #626976;
        font-weight: 500;
        transition: all 0.2s;
    }
    #settings-tabs .nav-link:hover {
        background-color: rgba(32, 107, 196, 0.05);
        color: #206bc4;
    }
    #settings-tabs .nav-link.active {
        background-color: #206bc4;
        color: #ffffff;
    }
</style>
@endpush
