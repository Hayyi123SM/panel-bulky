<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeHeroResource\Pages\CreateHomeHero;
use App\Filament\Resources\HomeHeroResource\Pages\EditHomeHero;
use App\Filament\Resources\HomeHeroResource\Pages\ListHomeHeroes;
use App\Models\HomeHero;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HomeHeroResource extends Resource
{
    protected static ?string $model = HomeHero::class;

    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Hero Beranda';
    protected static ?string $modelLabel = 'Hero Beranda';
    protected static ?string $pluralModelLabel = 'Hero Beranda';
    protected static ?int $navigationSort = 8;
    // protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('subtitle')
                    ->label('Subtitle')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image_path')
                    ->label('Gambar')
                    ->required()
                    ->image()
                    ->imageEditor()
                    ->openable()
                    ->downloadable()
                    ->disk('public')
                    ->directory('heroes/home')
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                        $hash = hash('sha1', $file->getClientOriginalName() . time());
                        $extension = $file->getClientOriginalExtension();

                        return $hash . '.' . $extension;
                    })
                    ->helperText('Rekomendasi ukuran: 1200 x 700 px, maksimal 2MB.')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktifkan Hero Beranda')
                    ->default(false)
                    ->inline(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->disk('public')
                    ->height(48)
                    ->width(80),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->limit(70)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(HomeHero $record): bool => ! $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (HomeHero $record): void {
                        $record->update(['is_active' => true]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomeHeroes::route('/'),
            'create' => CreateHomeHero::route('/create'),
            'edit' => EditHomeHero::route('/{record}/edit'),
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
