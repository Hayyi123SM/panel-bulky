<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3) // Gunakan tata letak 3 kolom untuk form utama
                    ->schema([

                        // 1. Informasi Gambar (Column Span 2)
                        Section::make('Gambar Banner')
                            ->description('Unggah gambar utama banner dan atur aktivitasnya.')
                            ->icon('heroicon-o-photo')
                            ->columnSpan(2) // Mengambil 2 dari 3 kolom
                            ->schema([
                                Forms\Components\FileUpload::make('path')
                                    ->label('Gambar')
                                    ->required()
                                    ->image()
                                    ->imageEditor()
                                    ->openable()
                                    ->disk('public')
                                    ->directory('banners')
                                    ->getUploadedFileNameForStorageUsing(
                                        function (TemporaryUploadedFile $file): string {
                                            $hash = hash('sha1', $file->getClientOriginalName() . time());
                                            $extension = $file->getClientOriginalExtension();
                                            return $hash . '.' . $extension;
                                        }
                                    )
                                    ->helperText('Rekomendasi: 1248 x 324 px. Ukuran file maksimal 2MB.'),

                                Forms\Components\Hidden::make('order')
                                    ->default(0),

                                Toggle::make('is_active')
                                    ->label('Aktifkan Banner')
                                    ->required()
                                    ->inline(false)
                                    ->default(true),
                            ]),

                        Section::make('Tujuan Banner')
                            ->description('Tentukan halaman atau jenis produk yang dituju banner.')
                            ->icon('heroicon-o-link')
                            ->columnSpan(1)
                            ->schema([
                                Select::make('page')
                                    ->label('Halaman Tujuan')
                                    ->required()
                                    ->live()
                                    ->options([
                                        'home' => 'Beranda (Home)',
                                        'product' => 'Halaman Produk',
                                    ]),

                                Select::make('product_type')
                                    ->label('Tipe Produk')
                                    ->hidden(fn(Forms\Get $get): bool => $get('page') !== 'product')
                                    ->requiredIf('page', 'product')
                                    ->placeholder('Pilih tipe produk yang relevan')
                                    ->options([
                                        'palet' => 'Palet',
                                        'truck_load' => 'Truck Load',
                                        'container' => 'Container',
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // 1. Gambar (Thumbnail)
                ImageColumn::make('path')
                    ->label('Pratinjau')
                    ->square() // Membuat gambar thumbnail lebih rapi (persegi)
                    ->height(40)
                    ->width(40)
                    ->defaultImageUrl(asset('images/default-banner.png')) // Opsional: Default jika path kosong
                    ->toggleable(isToggledHiddenByDefault: false),

                // 2. Status Aktivitas (Paling Penting & Visual)
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn($state) => $state ? 'Banner sedang aktif' : 'Banner tidak aktif'), // Tambahkan tooltip

                // 3. Halaman Tujuan (Visual dengan Badge)
                TextColumn::make('page')
                    ->label('Halaman Tujuan')
                    ->badge() // Tampilkan sebagai badge
                    ->color(fn(string $state): string => match ($state) {
                        'home' => 'primary',
                        'product' => 'info',
                        'promo' => 'success',
                        'external' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn(string $state) => Str::title($state)) // Format string 'home' menjadi 'Home'
                    ->sortable()
                    ->searchable(),

                // 4. Tipe Produk (Kondisional)
                TextColumn::make('product_type')
                    ->label('Tipe Produk')
                    ->badge()
                    ->placeholder('Global/Tidak Spesifik') // Teks jika NULL
                    ->color('secondary')
                    ->toggleable(isToggledHiddenByDefault: false), // Penting agar terlihat

                // 5. Metadata (Aktivitas dan Urutan)
                TextColumn::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan default

                // 6. Timestamp (Waktu Penting)
                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y, H:i') // Format tanggal/waktu yang lebih ringkas
                    ->since() // Tampilkan "5 jam yang lalu"
                    ->sortable()
                    ->color('success'),
            ])
            ->defaultSort('updated_at', 'desc') // Urutkan berdasarkan yang terakhir diubah
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                Tables\Filters\SelectFilter::make('page')
                    ->label('Filter Halaman')
                    // Gunakan opsi dari form untuk konsistensi
                    ->options([
                        'home' => 'Beranda (Home)',
                        'product' => 'Halaman Produk',
                        // Tambahkan opsi lain jika ada: 'promo', 'external', dll.
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
