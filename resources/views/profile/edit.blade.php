@extends('layouts.app')

@section('title', 'Pengaturan Profil')
@section('page_title', 'Pengaturan Profil')

@section('content')
<div class="row row-cards">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
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

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Update Password</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
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
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Ganti Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
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

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="card">
            @csrf
            @method('PATCH')
            <div class="card-header">
                <h3 class="card-title">Informasi Profil</h3>
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
                            <small class="form-hint">Email tidak dapat diubah.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon (WhatsApp)</label>
                            <input type="tel" id="phone" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}">
                            <input type="hidden" name="phone_number" id="phone_full" value="{{ old('phone_number', $user->phone_number) }}">
                            @error('phone_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
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
                    <div class="hr-text">Informasi Pemilik (Owner)</div>
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
            <div class="card-footer text-end">
                <button type="submit" id="submit-profile" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .iti { width: 100%; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.querySelector("#phone");
        const phoneFullInput = document.querySelector("#phone_full");

        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "id",
            separateDialCode: true,
        });

        const updateFullNumber = () => {
            if (iti && typeof iti.getNumber === 'function') {
                phoneFullInput.value = iti.getNumber();
            }
        };

        phoneInput.addEventListener('input', updateFullNumber);
        phoneInput.addEventListener('countrychange', updateFullNumber);

        phoneInput.addEventListener('input', function() {
            let value = phoneInput.value;
            if (value.startsWith('0')) {
                phoneInput.value = value.replace(/^0+/, '');
                updateFullNumber();
            }
        });

        if (phoneInput.value) {
            iti.setNumber(phoneInput.value);
            updateFullNumber();
        }

        const profileForm = document.querySelector('form[action*="profile"]');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                updateFullNumber();
            });
        }
    });
</script>
@endpush
