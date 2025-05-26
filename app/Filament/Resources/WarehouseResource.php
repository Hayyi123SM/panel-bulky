<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\Warehouse;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $slug = 'warehouses';

    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $label = 'Gudang';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                TextInput::make('address')
                    ->required(),

                Select::make('province_id')
                    ->label('Province')
                    ->dehydrated(false)
                    ->options(Province::all()->pluck('name', 'id'))
                    ->searchable()
                    ->getOptionLabelUsing(fn ($value): ?string => Province::find($value)?->name)
                    ->preload()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set){
                        $set('city_id', null);
                        $set('district_id', null);
                        $set('sub_district_id', null);
                    }),

                Select::make('city_id')
                    ->label('City')
                    ->dehydrated(false)
                    ->options(function ($record, Get $get){
                        $province = $get('province_id');
                        if(!empty($province)){
                            return City::whereProvinceId($province)->pluck('name', 'id');
                        }

                        return [];
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => City::find($value)?->name)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set){
                        $set('district_id', null);
                        $set('sub_district_id', null);
                    }),

                Select::make('district_id')
                    ->label('District')
                    ->dehydrated(false)
                    ->options(function (Get $get){
                        $city = $get('city_id');
                        if(!empty($city)){
                            return District::whereCityId($city)->pluck('name', 'id');
                        }

                        return [];
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => District::find($value)?->name)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set){
                        $set('sub_district_id', null);
                    }),

                Select::make('sub_district_id')
                    ->label('Sub District')
                    ->relationship('subDistrict', 'name')
                    ->options(function (Get $get){
                        $district = $get('district_id');
                        if(!empty($district)){
                            return SubDistrict::whereDistrictId($district)
                                ->get()
                                ->mapWithKeys(function ($subDistrict) {
                                    return [$subDistrict->id => "{$subDistrict->name} - {$subDistrict->postal_code}"];
                                });
                        }
                        return [];
                    })
                    ->searchable()
                    ->preload(),

                TextInput::make('contact_info')
                    ->label('Contact Info / Phone Number'),

                TextInput::make('latitude')
                    ->required(),
                TextInput::make('longitude')
                    ->required(),
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address'),

                TextColumn::make('subDistrict.name')
                    ->label('Sub District')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_info'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->subDistrict) {
            $details['SubDistrict'] = $record->subDistrict->name;
        }

        return $details;
    }
}
