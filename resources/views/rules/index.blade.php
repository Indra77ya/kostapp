@extends('layouts.app')

@section('title', 'Manajemen Peraturan')
@section('page_title', '')

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@endpush

@section('content')
    @livewire('rule-manager')
@endsection
