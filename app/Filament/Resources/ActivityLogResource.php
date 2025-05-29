<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $label = 'Log Aktivitas';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Log')
                    ->schema([
                        TextInput::make('log_name')
                            ->label('Nama Log')
                            ->disabled(),

                        TextInput::make('description')
                            ->label('Deskripsi')
                            ->disabled(),

                        TextInput::make('event')
                            ->label('Event')
                            ->disabled(),

                        Placeholder::make('created_at')
                            ->label('Waktu')
                            ->content(fn($record) => $record?->created_at?->format('d M Y H:i:s')),
                    ])
                    ->columns(2),

                Section::make('Informasi Pengguna')
                    ->schema([
                        TextInput::make('causer_type')
                            ->label('Tipe Pengguna')
                            ->disabled()
                            ->formatStateUsing(fn($state) => class_basename($state ?? '')),

                        TextInput::make('causer.name')
                            ->label('Nama Pengguna')
                            ->disabled()
                            ->formatStateUsing(function ($record) {
                                return $record->causer?->name ?? 'System';
                            }),

                        TextInput::make('causer_id')
                            ->label('ID Pengguna')
                            ->disabled(),
                    ])
                    ->columns(3),

                Section::make('Objek Yang Diubah')
                    ->schema([
                        TextInput::make('subject_type')
                            ->label('Tipe Objek')
                            ->disabled()
                            ->formatStateUsing(fn($state) => class_basename($state ?? '')),

                        TextInput::make('subject_id')
                            ->label('ID Objek')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record?->subject_type !== null),

                Section::make('Data Lama')
                    ->schema([
                        KeyValue::make('old_data')
                            ->label('')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn($record) => !empty($record?->properties['old']))
                            ->formatStateUsing(function ($state, $record) {
                                // Akses langsung dari record
                                $oldData = $record?->properties['old'] ?? [];

                                if (!is_array($oldData)) {
                                    return [];
                                }

                                return collect($oldData)->map(function ($value, $key) {
                                    if (is_array($value)) {
                                        return json_encode($value, JSON_PRETTY_PRINT);
                                    }
                                    return $value;
                                })->toArray();
                            })
                            ->default(fn($record) => $record?->properties['old'] ?? []),

                        Placeholder::make('no_old_data')
                            ->label('')
                            ->content('Tidak ada data lama')
                            ->visible(fn($record) => empty($record?->properties['old'])),
                    ])
                    ->collapsible()
                    ->collapsed(fn($record) => empty($record?->properties['old'])),

                Section::make('Data Baru')
                    ->schema([
                        KeyValue::make('attributes_data')
                            ->label('')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn($record) => !empty($record?->properties['attributes']))
                            ->formatStateUsing(function ($state, $record) {
                                // Akses langsung dari record
                                $attributesData = $record?->properties['attributes'] ?? [];

                                if (!is_array($attributesData)) {
                                    return [];
                                }

                                return collect($attributesData)->map(function ($value, $key) {
                                    if (is_array($value)) {
                                        return json_encode($value, JSON_PRETTY_PRINT);
                                    }
                                    return $value;
                                })->toArray();
                            })
                            ->default(fn($record) => $record?->properties['attributes'] ?? []),

                        Placeholder::make('no_new_data')
                            ->label('')
                            ->content('Tidak ada data baru')
                            ->visible(fn($record) => empty($record?->properties['attributes'])),
                    ])
                    ->collapsible()
                    ->collapsed(fn($record) => empty($record?->properties['attributes'])),

                Section::make('Data Tambahan')
                    ->schema([
                        KeyValue::make('properties')
                            ->label('')
                            ->disabled()
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state, $record) {
                                if (!is_array($state)) {
                                    return [];
                                }

                                // Copy array untuk menghindari modifikasi data asli
                                $properties = $state;

                                // Remove old and attributes as they're shown separately
                                unset($properties['old']);
                                unset($properties['attributes']);

                                if (empty($properties)) {
                                    return [];
                                }

                                return collect($properties)->map(function ($value, $key) {
                                    if (is_array($value)) {
                                        return json_encode($value, JSON_PRETTY_PRINT);
                                    }
                                    return $value;
                                })->toArray();
                            }),
                    ])
                    ->visible(
                        fn($record) =>
                        $record &&
                            $record->properties &&
                            count(array_diff_key($record->properties->toArray(), ['old' => '', 'attributes' => ''])) > 0
                    )
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Log')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'default' => 'gray',
                        'auth' => 'warning',
                        default => 'primary',
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('subject_type')
                    ->label('Model')
                    ->formatStateUsing(fn($state) => class_basename($state ?? ''))
                    ->searchable(),

                TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->default('System'),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->modalWidth('7xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
