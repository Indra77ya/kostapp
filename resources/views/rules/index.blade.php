@extends('layouts.app')

@section('title', 'Manajemen Peraturan')
@section('page_title', '')

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@endpush

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Master Data
                </div>
                <h2 class="page-title">
                    Manajemen Peraturan
                </h2>
            </div>
        </div>
    </div>

    @livewire('rule-manager')
</div>
@endsection
