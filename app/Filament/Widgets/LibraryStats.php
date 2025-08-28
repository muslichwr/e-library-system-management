<?php

namespace App\Filament\Widgets;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LibraryStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Hanya tampilkan untuk librarian dan super_admin
        if (
            !Auth::user() ||
            !(Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin'))
        ) {
            return [];
        }

        // Hitung statistik
        $totalBooks = Buku::count();
        $availableBooks = Buku::where('stock', '>', 0)->count();
        $activeLoans = Peminjaman::whereIn('status', ['dipinjam', 'terlambat'])->count();
        $lateLoans = Peminjaman::where('status', 'terlambat')->count();
        $totalMembers = User::whereHas('roles', function ($query) {
            $query->where('name', 'borrower');
        })->count();
        $pendingApprovals = Peminjaman::where('status', 'pending')->count();

        return [
            Stat::make('Total Koleksi', $totalBooks)
                ->description('Buku, Majalah, Film')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('primary'),

            Stat::make('Tersedia untuk Pinjam', $availableBooks)
                ->description('Stock mencukupi')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Peminjaman Aktif', $activeLoans)
                ->description('Sedang dipinjam')
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('warning'),

            Stat::make('Terlambat', $lateLoans)
                ->description('Perlu tindakan')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('Menunggu Persetujuan', $pendingApprovals)
                ->description('Perlu validasi')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray'),

            Stat::make('Total Anggota', $totalMembers)
                ->description('Peminjam aktif')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
        ];
    }
}
