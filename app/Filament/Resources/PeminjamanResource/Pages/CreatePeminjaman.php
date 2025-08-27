<?php

namespace App\Filament\Resources\PeminjamanResource\Pages;

use App\Filament\Resources\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;



class CreatePeminjaman extends CreateRecord
{
    protected static string $resource = PeminjamanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['tanggal_pinjam'] = now()->format('Y-m-d');
        $data['tanggal_kembali'] = now()->addDays(14)->format('Y-m-d');
        $data['status'] = 'pending';

        return $data;
    }

    public function form(Form $form): Form
    {
        $bukuId = request()->query('buku_id');

        return $form
            ->schema([
                Forms\Components\Select::make('buku_id')
                    ->label('Pilih Buku')
                    ->options(function () {
                        return Buku::where('stock', '>', 0)
                            ->pluck('judul', 'id')
                            ->toArray();
                    })
                    ->default($bukuId)
                    ->disabled($bukuId !== null)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $buku = Buku::find($state);
                        if ($buku) {
                            $set('stock_sekarang', $buku->stock);
                        }
                    }),

                Forms\Components\Placeholder::make('stock_sekarang')
                    ->label('Stock Tersedia')
                    ->content(fn($get) => $get('buku_id') ?
                        Buku::find($get('buku_id'))?->stock . ' tersedia' :
                        'Pilih buku terlebih dahulu'),

                Forms\Components\Placeholder::make('info')
                    ->content('Pastikan Anda dapat mengembalikan buku tepat waktu. Batas peminjaman adalah 14 hari.')
                    ->visible(function () {
                        return Auth::user()->role === 'borrower';
                    }),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        // Kembali ke halaman katalog buku setelah peminjaman berhasil
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Cek apakah user sudah melebihi batas peminjaman
        $activeLoans = Peminjaman::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'dipinjam', 'terlambat'])
            ->count();

        if ($activeLoans >= 3) {
            $this->notify('danger', 'Anda sudah mencapai batas peminjaman (3 buku). Silakan kembalikan buku yang sudah dipinjam terlebih dahulu.');
            $this->halt();
        }

        // Cek stock buku
        $buku = Buku::find($data['buku_id']);
        if ($buku->stock <= 0) {
            $this->notify('danger', 'Stock buku ini sudah habis.');
            $this->halt();
        }

        // Kurangi stock buku
        $buku->decrement('stock');

        return parent::handleRecordCreation($data);
    }
}
