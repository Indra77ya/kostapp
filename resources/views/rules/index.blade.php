@extends('layouts.app')

@section('page_title', '')

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
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
