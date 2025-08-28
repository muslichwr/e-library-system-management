<?php

namespace App\Observers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;

class PeminjamanObserver
{
    /**
     * Handle the Peminjaman "updated" event.
     */
    public function updated(Peminjaman $peminjaman): void
    {
        // Cek jika status berubah ke 'hilang'
        if ($peminjaman->isDirty('status') && $peminjaman->status === 'hilang') {
            // Kurangi stock buku secara permanen
            DB::transaction(function () use ($peminjaman) {
                $buku = Buku::lockForUpdate()->find($peminjaman->buku_id);
                if ($buku) {
                    $buku->decrement('stock');
                }
            });
        }
        
        // Cek jika status berubah dari 'terlambat' ke 'dipinjam' (mungkin dikembalikan setelah terlambat)
        if ($peminjaman->isDirty('status') && 
            $peminjaman->getOriginal('status') === 'terlambat' && 
            $peminjaman->status === 'dikembalikan') {
            
            // Tidak perlu mengubah stock karena sudah dikembalikan
            // Bisa tambahkan logika lain jika diperlukan
        }
    }
    
    /**
     * Handle the Peminjaman "created" event.
     */
    public function created(Peminjaman $peminjaman): void
    {
        // Saat peminjaman dibuat dengan status pending, tidak mengurangi stock
    }
    
    /**
     * Handle the Peminjaman "deleted" event.
     */
    public function deleted(Peminjaman $peminjaman): void
    {
        // Jika peminjaman dihapus, kembalikan stock jika status bukan 'hilang'
        if ($peminjaman->status !== 'hilang') {
            $buku = Buku::find($peminjaman->buku_id);
            if ($buku) {
                $buku->increment('stock');
            }
        }
    }
}
