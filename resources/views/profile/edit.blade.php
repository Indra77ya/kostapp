@extends('layouts.app')

@section('title', 'Pengaturan Profil')
@section('page_title', 'Pengaturan Profil')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <!-- Profile Info Card -->
        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                    </div>
                    <div>Profil berhasil diperbarui.</div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm border-0 mb-4">
            @csrf
            @method('PATCH')
            <div class="card-header bg-white">
                <h3 class="card-title text-primary">Informasi Profil</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-4">
                    <div class="col-auto">
                        @if($user->avatar)
                            <span class="avatar avatar-xl rounded" style="background-image: url({{ asset('storage/' . $user->avatar) }})"></span>
                        @else
                            <span class="avatar avatar-xl rounded">{{ substr($user->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <div class="col">
                        <h2 class="mb-1">{{ $user->name }}</h2>
                        <div class="text-secondary">{{ $user->getRoleNames()->first() }}</div>
                        <div class="mt-2">
                            <span class="badge bg-blue-lt">{{ $user->email }}</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label required">Nama</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <small class="form-hint text-muted">Email tidak dapat diubah.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon (WhatsApp)</label>
                            <input type="tel" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}" placeholder="Contoh: 08123456789">
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ganti Foto Profil</label>
                            <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="3">{{ old('bio', $user->bio) }}</textarea>
                        </div>
                    </div>

                    @role('owner')
                    <div class="hr-text text-muted">Informasi Pemilik (Owner)</div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Informasi Rekening Bank (untuk Pembayaran)</label>
                            <textarea name="bank_info" class="form-control" rows="3" placeholder="Contoh: BCA 123456789 a/n Nama Anda">{{ old('bank_info', $user->bank_info) }}</textarea>
                        </div>
                    </div>
                    @endrole
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </div>
        </form>

        <!-- Password Card -->
        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                    </div>
                    <div>Password berhasil diperbarui.</div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        @endif

        <form action="{{ route('profile.password.update') }}" method="POST" class="card shadow-sm border-0">
            @csrf
            @method('PUT')
            <div class="card-header bg-white">
                <h3 class="card-title text-primary">Ganti Password</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label required">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                    @error('current_password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label required">Password Baru</label>
                    <input type="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required>
                    @error('password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label required">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary px-4">Perbarui Password</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hr-text {
        margin: 2rem 0 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-format phone number (replace leading 0 with +62)
        const phoneInput = document.querySelector('input[name="phone_number"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value;
                if (value.startsWith('0')) {
                    e.target.value = '+62' + value.substring(1);
                }
            });
        }
    });
</script>
@endpush
