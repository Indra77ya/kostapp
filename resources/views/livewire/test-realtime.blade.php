<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Test Real-time Features</h3>
    </div>
    <div class="card-body">
        <p>Gunakan tombol di bawah ini untuk memicu event real-time:</p>
        <div class="btn-list">
            <button wire:click="triggerUpdate" class="btn btn-primary">
                Trigger Update Stats
            </button>
            <button wire:click="addRoom" class="btn btn-success">
                Tambah Kamar (Real-time)
            </button>
        </div>
    </div>
</div>
