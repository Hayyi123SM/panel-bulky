<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Services\Deliveree\Deliveree;
use App\Services\WMS\Contracts\ProductDropdownServiceInterface;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;

class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $label = 'Produk';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        $isEdit = $form->getOperation() === 'edit';
        return $form
            ->schema([
                Forms\Components\Section::make('Foto Produk')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Gambar Produk')
                            ->required()
                            ->image()
                            ->multiple()
                            ->openable()
                            ->reorderable()
                            ->minFiles(1)
                            ->maxFiles(10)
                            ->directory('products')
                            ->panelLayout('grid')
                    ]),
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        // Dropdown template hanya saat create
                        ...(!$isEdit ? [
                            Forms\Components\Select::make('product_template_id')
                                ->label('Template Produk')
                                ->options(fn() => app(ProductDropdownServiceInterface::class)->getDropdownOptions())
                                ->nullable()
                                ->native(false)
                                ->searchable()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $service = app(ProductDropdownServiceInterface::class);
                                        $detail = $service->getDropdownDetail($state);
                                        if (!empty($detail)) {
                                            $set('name_trans', $detail['name_document'] ?? '');
                                            $set('id_pallet', $detail['id'] ?? '');
                                            $set('price_before_discount', $detail['old_price'] ?? '');
                                            // $set('price', $detail['new_price'] ?? '');
                                            $set('length_cm', $detail['dimension']['length'] ?? '');
                                            $set('width_cm', $detail['dimension']['width'] ?? '');
                                            $set('height_cm', $detail['dimension']['height'] ?? '');
                                            $set('weight_kg', $detail['dimension']['weight'] ?? '');
                                            $set('total_quantity', $detail['qty'] ?? '');
                                            $set('packaging_type', $detail['packaging_type'] ?? 'palet');
                                            $set('pdf_input_mode', 'url');
                                            $pdfUrl = $detail['pdf_url'] ?? '';
                                            $set('pdf_url', $pdfUrl);
                                            $url = trim($pdfUrl);
                                            if (preg_match('/(\\.pdf|\/pdf)$/i', $url)) {
                                                try {
                                                    $response = \Illuminate\Support\Facades\Http::timeout(5)->head($url);
                                                    if ($response->failed() || $response->status() !== 200) {
                                                        $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ File PDF tidak ditemukan atau tidak dapat diakses.<br><small>Pastikan link benar dan file dapat diakses publik (status 200).</small></span>');
                                                        return;
                                                    }
                                                    $contentType = strtolower($response->header('Content-Type', ''));
                                                    $contentLength = (int) $response->header('Content-Length', 0);
                                                    if (!str_contains($contentType, 'application/pdf')) {
                                                        $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ Link tidak mengarah ke file PDF asli.<br><small>Content-Type: ' . e($contentType) . '</small></span>');
                                                        return;
                                                    }
                                                    if ($contentLength <= 0) {
                                                        $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ File PDF kosong atau tidak ditemukan.<br><small>Ukuran file 0 byte.</small></span>');
                                                        return;
                                                    }
                                                    $set('pdf_status_feedback', '<span style="color:#16a34a;font-weight:bold;">✔️ Link PDF valid</span> — <a href="' . e($url) . '" target="_blank" style="color: #2563eb; text-decoration:underline;">Klik untuk buka PDF</a>');
                                                } catch (\Throwable $e) {
                                                    $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ Terjadi kesalahan saat memeriksa link PDF.<br><small>Periksa kembali URL atau koneksi internet Anda.</small></span>');
                                                }
                                            } else if (!empty($url)) {
                                                $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ URL harus diakhiri .pdf</span>');
                                            } else {
                                                $set('pdf_status_feedback', 'Belum ada file PDF.');
                                            }
                                        }
                                    } else {
                                        $set('name_trans', '');
                                        $set('id_pallet', '');
                                        $set('price_before_discount', '');
                                        $set('price', '');
                                        $set('total_quantity', '');
                                        $set('packaging_type', 'palet');
                                        $set('length_cm', '');
                                        $set('width_cm', '');
                                        $set('height_cm', '');
                                        $set('weight_kg', '');
                                        $set('pdf_input_mode', 'upload');
                                        $set('pdf_url', '');
                                        $set('pdf_status_feedback', 'Belum ada file PDF.');
                                    }
                                })
                                ->helperText('Pilih template produk untuk autofill data (opsional)'),
                        ] : []),
                        Forms\Components\TextInput::make('name_trans')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama Produk'),
                        Forms\Components\TextInput::make('id_pallet')
                            ->label('ID Pallet')
                            ->maxLength(255)
                            ->placeholder('Product Pallet')
                            ->disabled($isEdit)
                            ->readonly(fn($get) => !$isEdit && !empty($get('product_template_id')))
                            ->extraAttributes(fn($get) => !$isEdit && !empty($get('product_template_id')) ? [
                                'style' => 'background-color:#f3f4f6;pointer-events:none;color:#6b7280;',
                                'tabindex' => '-1',
                            ] : [])
                            ->helperText(fn($get) => !$isEdit && !empty($get('product_template_id')) ? 'Field ini diisi otomatis dari template dan tidak bisa diubah.' : ($isEdit ? 'Tidak dapat diubah saat edit.' : '')),
                        Forms\Components\TextInput::make('price_before_discount')
                            ->label('Harga Sebelum Diskon')
                            ->required()
                            ->prefix('Rp ')
                            ->placeholder('Harga Sebelum Diskon')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 0)
                            JS))
                            ->dehydrateStateUsing(fn(string $state) => str($state)->remove('.')->toInteger()),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga Produk')
                            ->required()
                            ->prefix('Rp ')
                            ->placeholder('Harga Produk')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 0)
                            JS))
                            ->dehydrateStateUsing(fn(string $state) => str($state)->remove('.')->toInteger()),
                        Forms\Components\TextInput::make('total_quantity')
                            ->label('Total Quantity')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Product Quantity'),
                        Forms\Components\Radio::make('packaging_type')
                            ->label('Tipe Pengemasan')
                            ->options([
                                'palet' => 'Palet',
                                'truck_load' => 'Truck Load',
                                'container' => 'Kontainer',
                            ])
                            ->reactive()
                            ->required(),
                    ]),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        // Pada edit, hanya FileUpload PDF
                        ...($isEdit ? [
                            FileUpload::make('pdf_file')
                                ->label('File PDF')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(1000)
                                ->required()
                        ] : [
                            Forms\Components\Radio::make('pdf_input_mode')
                                ->label('Mode Input PDF')
                                ->options([
                                    'upload' => 'Upload File Lokal',
                                    'url' => 'URL Eksternal',
                                ])
                                ->default('upload')
                                ->reactive()
                                ->helperText('Pilih cara input file PDF. Akan diisi otomatis jika memilih template produk.'),
                            // ->disabled(fn($get) => !empty($get('product_template_id'))),
                            FileUpload::make('pdf_file')
                                ->label('File PDF')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(1000)
                                ->required(fn($get) => $get('pdf_input_mode') === 'upload')
                                ->visible(fn($get) => $get('pdf_input_mode') === 'upload'),
                            // ->disabled(fn($get) => !empty($get('product_template_id'))),
                            Forms\Components\TextInput::make('pdf_url')
                                ->label('PDF URL')
                                ->url()
                                ->placeholder('https://example.com/document.pdf')
                                ->required(fn($get) => $get('pdf_input_mode') === 'url')
                                ->visible(fn($get) => $get('pdf_input_mode') === 'url')
                                // ->readonly(fn($get) => !empty($get('product_template_id')))
                                ->extraAttributes(fn($get) => !empty($get('product_template_id')) ? [
                                    'style' => 'background-color:#f3f4f6;pointer-events:none;color:#6b7280;',
                                    'tabindex' => '-1',
                                ] : [])
                                ->reactive()
                                ->debounce(750)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $url = trim($state ?? '');
                                    if (preg_match('/(\\.pdf|\/pdf)$/i', $url)) {
                                        try {
                                            $response = Http::timeout(5)->head($url);
                                            if ($response->failed() || $response->status() !== 200) {
                                                $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ File PDF tidak ditemukan atau tidak dapat diakses.<br><small>Pastikan link benar dan file dapat diakses publik (status 200).</small></span>');
                                                return;
                                            }
                                            $contentType = strtolower($response->header('Content-Type', ''));
                                            $contentLength = (int) $response->header('Content-Length', 0);
                                            if (!str_contains($contentType, 'application/pdf')) {
                                                $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ Link tidak mengarah ke file PDF asli.<br><small>Content-Type: ' . e($contentType) . '</small></span>');
                                                return;
                                            }
                                            if ($contentLength <= 0) {
                                                $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ File PDF kosong atau tidak ditemukan.<br><small>Ukuran file 0 byte.</small></span>');
                                                return;
                                            }
                                            $set('pdf_status_feedback', '<span style="color:#16a34a;font-weight:bold;">✔️ Link PDF valid</span> — <a href="' . e($url) . '" target="_blank" style="color: #2563eb; text-decoration:underline;">Klik untuk buka PDF</a>');
                                        } catch (\Throwable $e) {
                                            $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ Terjadi kesalahan saat memeriksa link PDF.<br><small>Periksa kembali URL atau koneksi internet Anda.</small></span>');
                                        }
                                    } else if (!empty($url)) {
                                        $set('pdf_status_feedback', '<span style="color:#dc2626;font-weight:bold;">❌ URL harus diakhiri .pdf</span>');
                                    } else {
                                        $set('pdf_status_feedback', 'Belum ada file PDF.');
                                    }
                                })
                                ->rule(function ($get) {
                                    if ($get('pdf_input_mode') !== 'url') return null;
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if (empty($value)) return;
                                        $url = trim($value);
                                        if (!preg_match('/(\\.pdf|\/pdf)$/i', $url)) {
                                            $fail('URL harus mengarah ke file PDF (.pdf atau /pdf).');
                                            return;
                                        }
                                        try {
                                            $response = Http::timeout(5)->head($url);
                                            if ($response->failed() || $response->status() !== 200) {
                                                $fail('URL PDF tidak ditemukan atau tidak dapat diakses (status code bukan 200).');
                                                return;
                                            }
                                            $contentType = strtolower($response->header('Content-Type', ''));
                                            $contentLength = (int) $response->header('Content-Length', 0);
                                            if (!str_contains($contentType, 'application/pdf')) {
                                                $fail('URL tidak mengarah ke file PDF asli (Content-Type salah: ' . $contentType . ').');
                                                return;
                                            }
                                            if ($contentLength <= 0) {
                                                $fail('File PDF tidak ditemukan atau kosong (Content-Length 0).');
                                                return;
                                            }
                                        } catch (\Throwable $e) {
                                            $fail('URL PDF tidak valid atau tidak dapat diakses.');
                                        }
                                    };
                                }),
                            Forms\Components\Placeholder::make('pdf_status_feedback')
                                ->label('Status PDF')
                                ->content(fn($get) => new HtmlString($get('pdf_status_feedback') ?? 'Belum ada file PDF.'))
                                ->visible(fn($get) => $get('pdf_input_mode') === 'url' && !empty($get('pdf_url')))
                                ->columnSpanFull()
                                ->helperText('Status validasi PDF akan muncul di sini.'),
                        ]),
                        Forms\Components\Select::make('truck_load_vehicle_type_id')
                            ->label('Jenis Kendaraan Truck Load')
                            ->options([
                                2725 => 'CDD Long Liquid8',
                                2704 => 'Fuso Box Liquid8 (8 Tons)',
                                2726 => 'Fuso Pickup Liquid8 (8 Ton)',
                                2727 => 'Fuso Box Liquid8 (10 Ton)',
                                2728 => 'Fuso Pickup Liquid8 (10 Ton)',
                                2723 => 'CDD Box Liquid8',
                                2705 => 'Tronton Wing Box Liquid8',
                                2703 => 'CDE Box Liquid8',
                                2722 => 'CDE Bak Liquid8',
                                2724 => 'CDD Bak Liquid8',
                                2702 => 'Small Pickup Liquid8',
                                2701 => 'Van Liquid8',
                                2719 => 'Mobil Liquid8',
                                2721 => 'Box Kecil Liquid8',
                                2720 => 'Mobil XL Liquid8',
                            ])
                            ->native(false)
                            ->searchable()
                            ->visible(fn($get) => $get('packaging_type') === 'truck_load')
                            ->required(fn($get) => $get('packaging_type') === 'truck_load'),
                        TiptapEditor::make('description_trans')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpanFull()
                            ->profile('product')
                            ->placeholder('Enter Product Description')
                            ->disableBubbleMenus()
                            ->disableFloatingMenus()
                            ->extraInputAttributes(['style' => 'min-height: 300px;']),
                    ]),

                Forms\Components\Section::make('Status dan Kategori')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Diaktifkan')
                            ->default(true),
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('product_category_id')
                            ->label('Kategori')
                            ->relationship('productCategory', 'name_trans')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('brands')
                            ->label('Merk')
                            ->relationship('brands', 'name')
                            ->multiple()
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('product_condition_id')
                            ->label('Kondisi Produk')
                            ->relationship('productCondition', 'title')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('product_status_id')
                            ->label('Status Produk')
                            ->relationship('productStatus', 'status')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('status_package_id')
                            ->label('Kondisi Packaging')
                            ->relationship('statusPackage', 'status')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        // Note: value stored as integer 1..100 representing percent
                        Forms\Components\TextInput::make('note_discrepancy')
                            ->label('Catatan perbedaan (%)')
                            ->type('number')
                            ->minValue(1)
                            ->maxValue(100)
                            ->step(1)
                            ->required()
                            ->default(0)
                            ->helperText('Masukkan persentase (1 - 100). Nilai ini akan ditampilkan sebagai persen.')
                            ->suffix('%'),
                        //                        Forms\Components\Select::make('vehicle_type_id')
                        //                            ->label('Jenis Kendaraan')
                        //                            ->options(function (){
                        //                                $vehicles = Deliveree::getVehicleTypes();
                        //                                if (collect($vehicles)->has('error')) {
                        //                                    Notification::make('error_deliveree')
                        //                                        ->title($vehicles['error'])
                        //                                        ->danger()
                        //                                        ->send();
                        //
                        //                                    return [];
                        //                                } else {
                        //                                    return collect($vehicles['data'])
                        //                                        ->when(app()->environment('production'), function ($collection) {
                        //                                            return $collection->filter(function ($item) {
                        //                                                return str_contains($item['name'], 'Liquid8');
                        //                                            });
                        //                                        })
                        //                                        ->mapWithKeys(function ($item) {
                        //                                            return [
                        //                                                $item['id'] => "<span>{$item['name']}</span><br>{$item['cargo_length']} x {$item['cargo_height']} x {$item['cargo_width']}"
                        //                                            ];
                        //                                        });
                        //                                }
                        //                            })
                        //                            ->allowHtml()
                        //                            ->native(false)
                    ]),

                Forms\Components\Section::make('Dimensi & Berat')
                    ->schema([
                        Forms\Components\TextInput::make('length_cm')
                            ->label('Panjang')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Panjang dalam cm')
                            ->reactive(),

                        Forms\Components\TextInput::make('width_cm')
                            ->label('Lebar')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Lebar dalam cm')
                            ->reactive(),

                        Forms\Components\TextInput::make('height_cm')
                            ->label('Tinggi')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Tinggi dalam cm')
                            ->reactive(),

                        Forms\Components\TextInput::make('weight_kg')
                            ->label('Berat')
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Berat dalam kg')
                            ->reactive(),

                        Forms\Components\Placeholder::make('volume_m3')
                            ->label('Volume (m³)')
                            ->content(fn($get) => ($get('length_cm') && $get('width_cm') && $get('height_cm')) ? number_format((($get('length_cm') * $get('width_cm') * $get('height_cm')) / 1000000), 6, ',', '.') . ' m³' : '-')
                            ->columnSpanFull()
                            ->helperText('Volume dihitung otomatis dari panjang × lebar × tinggi (cm).'),
                    ])->columns(2),
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Gambar')
                    ->searchable()
                    ->rounded(),

                Tables\Columns\TextColumn::make('name_trans')
                    ->label('Nama Produk')
                    ->searchable(query: function ($query, $search) {
                        $query->whereRaw('LOWER(name_trans) LIKE ?', ['%' . strtolower($search) . '%']);
                    })
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('productCategory.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('productStatus.status')
                    ->label('Status')
                    ->colors([
                        'danger' => fn($state) => strtolower($state) === 'unavailable' || strtolower($state) === 'inactive',
                        'warning' => fn($state) => strtolower($state) === 'pending',
                        'success' => fn($state) => strtolower($state) === 'available' || strtolower($state) === 'active',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('statusPackage.status')
                    ->label('Status Paket')
                    ->colors([
                        'danger' => fn($state) => strtolower($state) === 'unavailable' || strtolower($state) === 'inactive',
                        'warning' => fn($state) => strtolower($state) === 'pending',
                        'success' => fn($state) => strtolower($state) === 'available' || strtolower($state) === 'active',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('note_discrepancy')
                    ->label('Catatan perbedaan')
                    ->formatStateUsing(fn($state) => ($state ?? 0) . '%')
                    ->colors([
                        'danger' => fn($state) => $state !== null && $state <= 30,
                        'warning' => fn($state) => $state !== null && $state > 30 && $state < 70,
                        'success' => fn($state) => $state !== null && $state >= 70,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Produk')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Berat')
                    ->formatStateUsing(fn($state) => $state !== null ? ((is_numeric($state) && floor($state) == $state) ? (int) $state : number_format($state, 2, ',', '.')) . ' kg' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('volume_m3')
                    ->label('Volume')
                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 6, ',', '.') . ' m³' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Quantity')
                    ->suffix(' item')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Diaktifkan')
                    ->onIcon('heroicon-o-check')
                    ->onColor('success')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('sold_out')
                    ->label('Terjual')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_new')
                    ->label('Produk Baru')
                    ->onIcon('heroicon-o-check')
                    ->onColor('success')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
