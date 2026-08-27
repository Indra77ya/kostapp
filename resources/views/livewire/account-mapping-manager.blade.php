<div>
    {{-- Page Header --}}
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h2 class="page-title">
                <i class="ti ti-map-search me-2 text-primary"></i> Pemetaan Akun (Account Mapping)
            </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <button wire:click="resetToDefaults" wire:confirm="Apakah Anda yakin ingin mengembalikan seluruh pemetaan akun ke standar awal?" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-refresh me-1"></i> Reset ke Default
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-check me-2 alert-icon"></i>
                </div>
                <div>
                    {{ session('success') }}
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="card-header bg-light">
            <h3 class="card-title text-body">Daftar Transaksi Otomatis Sistem & Akun Terkait</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th style="width: 25%;">Transaksi Sistem</th>
                        <th style="width: 35%;">Deskripsi Fungsi</th>
                        <th style="width: 40%;">Akun COA Terhubung</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mappingRecords as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                <div class="text-muted small"><code>{{ $item->key }}</code></div>
                            </td>
                            <td class="text-muted">
                                {{ $item->description }}
                            </td>
                            <td>
                                <select
                                    class="form-select @if(!$mappings[$item->key]) border-warning @endif"
                                    wire:change="updateMapping('{{ $item->key }}', $event.target.value)"
                                >
                                    <option value="">-- Pilih Akun COA --</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(($mappings[$item->key] ?? null) == $account->id)>
                                            [{{ $account->code }}] {{ $account->name }} ({{ strtoupper($account->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                @if(!$mappings[$item->key])
                                    <div class="text-warning small mt-1">
                                        <i class="ti ti-alert-triangle me-1"></i> Belum terhubung. Sistem akan menggunakan akun default.
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                Belum ada data pemetaan akun.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            <i class="ti ti-info-circle me-1"></i> Transaksi otomatis (pembayaran sewa, setoran deposit, refund check-out, dll) akan secara otomatis menggunakan akun COA yang dipilih di atas saat menjurnal.
        </div>
    </div>
</div>
