<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeminjamanResource\Pages;
use App\Filament\Resources\PeminjamanResource\RelationManagers;
use App\Models\Peminjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Riwayat Pinjaman';
    protected static ?string $pluralLabel = 'Peminjaman';
    protected static ?string $modelLabel = 'Peminjaman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Peminjam')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn() => Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin')),

                Forms\Components\Select::make('buku_id')
                    ->label('Buku')
                    ->relationship('buku', 'judul')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_pinjam')
                    ->label('Tanggal Pinjam')
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_kembali')
                    ->label('Tanggal Kembali'),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'dipinjam' => 'Dipinjam',
                        'dikembalikan' => 'Dikembalikan',
                        'terlambat' => 'Terlambat',
                        'ditolak' => 'Ditolak',
                        'hilang' => 'Hilang',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $record, Forms\Set $set) {
                        if ($state === 'dipinjam' && !$record->tanggal_pinjam) {
                            $set('tanggal_pinjam', now()->format('Y-m-d'));
                        }

                        if ($state === 'dikembalikan' && !$record->tanggal_kembali) {
                            $set('tanggal_kembali', now()->format('Y-m-d'));
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peminjam')
                    ->searchable()
                    ->visible(fn() => Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin')),

                Tables\Columns\TextColumn::make('buku.judul')
                    ->label('Judul Buku')
                    ->searchable(),

                Tables\Columns\TextColumn::make('buku.kode_buku')
                    ->label('Kode Buku')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d-m-Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_kembali')
                    ->label('Tgl Kembali')
                    ->date('d-m-Y')
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'dipinjam' => 'warning',
                        'dikembalikan' => 'success',
                        'terlambat' => 'danger',
                        'ditolak' => 'danger',
                        'hilang' => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Persetujuan',
                        'dipinjam' => 'Dipinjam',
                        'dikembalikan' => 'Dikembalikan',
                        'terlambat' => 'Terlambat',
                        'ditolak' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('tanggal_pinjam')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn(Builder $query, $date) => $query->whereDate('tanggal_pinjam', '>=', $date))
                            ->when($data['sampai_tanggal'], fn(Builder $query, $date) => $query->whereDate('tanggal_pinjam', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Action Approve
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Peminjaman $record) => $record->status === 'pending' &&
                        (Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin')))
                    ->requiresConfirmation()
                    ->action(function (Peminjaman $record) {
                        $buku = $record->buku;

                        // Cek stock buku
                        if ($buku->stock <= 0) {
                            Notification::make()
                                ->title('Gagal Menyetujui')
                                ->body('Stock buku ini sudah habis.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Kurangi stock dan update status
                        $buku->decrement('stock');
                        $record->update([
                            'status' => 'dipinjam',
                            'tanggal_pinjam' => now()->format('Y-m-d'),
                        ]);

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Peminjaman telah disetujui.')
                            ->success()
                            ->send();
                    }),

                // Action Reject
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Peminjaman $record) => $record->status === 'pending' &&
                        (Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin')))
                    ->requiresConfirmation()
                    ->action(function (Peminjaman $record) {
                        $record->update(['status' => 'ditolak']);

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Peminjaman telah ditolak.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('laporkanHilang')
                    ->label('Hilang')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(
                        fn(Peminjaman $record) => ($record->status === 'terlambat' || $record->status === 'dipinjam') &&
                            (Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin'))
                    )
                    ->action(function (Peminjaman $record, array $data) {
                        $record->update([
                            'status' => 'hilang',
                        ]);

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Buku telah dilaporkan hilang dan stock dikurangi.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin')),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => Auth::user()->hasRole('librarian') || Auth::user()->hasRole('super_admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!Auth::check()) {
            return $query;
        }

        if (Auth::user()->hasRole('borrower')) {
            return $query->where('user_id', Auth::id());
        }

        return $query;
    }

    // public static function canCreate(): bool
    // {
    //     // Hanya librarian yang bisa membuat peminjaman langsung dari halaman peminjaman
    //     return (Auth::user()?->hasRole('librarian') || Auth::user()?->hasRole('super_admin')) ?? false;
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeminjamen::route('/'),
            'create' => Pages\CreatePeminjaman::route('/create'),
            'edit' => Pages\EditPeminjaman::route('/{record}/edit'),
        ];
    }
}
