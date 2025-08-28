<?php

namespace App\Filament\Widgets;

use App\Models\Peminjaman;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MonthlyLoansChart extends ChartWidget
{
    protected static ?string $heading = 'Peminjaman Bulanan';
    protected static ?int $sort = 2;
    protected static string $color = 'primary';

    protected function getData(): array
    {
        // Hanya tampilkan untuk librarian dan super_admin
        if (!Auth::user() || 
            !(Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin'))) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $data = Peminjaman::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $months = [];
        $counts = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
            $counts[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Peminjaman',
                    'data' => $counts,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
