@extends('layouts.app')

@section('title', 'Pengaturan Profil')
@section('page_title', 'Pengaturan Profil')

@section('content')
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body p-0">
                <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start py-3 px-4 border-0 rounded-0" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile-content" type="button" role="tab" aria-controls="profile-content" aria-selected="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                        Informasi Profil
                    </button>
                    <button class="nav-link text-start py-3 px-4 border-0 rounded-0" id="security-tab" data-bs-toggle="pill" data-bs-target="#security-content" type="button" role="tab" aria-controls="security-content" aria-selected="false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 11m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" /><path d="M12 16m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                        Keamanan
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    @if($user->avatar)
                        <span class="avatar avatar-xl rounded" style="background-image: url({{ asset('storage/' . $user->avatar) }})"></span>
                    @else
                        <span class="avatar avatar-xl rounded">{{ substr($user->name, 0, 2) }}</span>
                    @endif
                </div>
                <h3 class="card-title mb-1">{{ $user->name }}</h3>
                <div class="text-secondary">{{ $user->getRoleNames()->first() }}</div>
                <div class="mt-3">
                    <span class="badge bg-blue-lt">{{ $user->email }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="tab-content border-0 p-0" id="settings-content">
            <!-- Profile Tab -->
            <div class="tab-pane fade show active" id="profile-content" role="tabpanel" aria-labelledby="profile-tab">
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

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm border-0">
                    @csrf
                    @method('PATCH')
                    <div class="card-header bg-white">
                        <h3 class="card-title text-primary">Informasi Profil</h3>
                    </div>
                    <div class="card-body">
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
                                    <label class="form-label">Foto Profil</label>
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
            </div>

            <!-- Security Tab -->
            <div class="tab-pane fade" id="security-content" role="tabpanel" aria-labelledby="security-tab">
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
    .hr-text {
        margin: 2rem 0 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-switch to Security tab if there are password validation errors
        @if($errors->updatePassword->any() || session('status') === 'password-updated')
            const securityTab = document.getElementById('security-tab');
            const securityContent = document.getElementById('security-content');
            const profileTab = document.getElementById('profile-tab');
            const profileContent = document.getElementById('profile-content');

            securityTab.classList.add('active');
            securityContent.classList.add('show', 'active');
            profileTab.classList.remove('active');
            profileContent.classList.remove('show', 'active');
        @endif
    });
</script>
@endpush
