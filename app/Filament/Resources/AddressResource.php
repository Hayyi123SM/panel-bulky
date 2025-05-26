<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Filament\Resources\AddressResource\RelationManagers;
use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Alamat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->uuid()
                    ->exists('users', 'id'),
                TextInput::make('label')
                    ->placeholder('Ex: Home, Office')
                    ->required()
                    ->string(),
                TextInput::make('name')
                    ->placeholder('Enter receiver name')
                    ->label('Nama')
                    ->required()
                    ->string(),
                TextInput::make('phone_number')
                    ->placeholder('Enter receiver phone number')
                    ->label('Nomor Ponsel')
                    ->required()
                    ->string(),
                TextInput::make('address')
                    ->placeholder('Enter receiver address')
                    ->label('Alamat')
                    ->required()
                    ->string(),

                Forms\Components\Select::make('province_id')
                    ->label('Provinsi')
                    ->options(Province::all()->pluck('name', 'id'))
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->uuid()
                    ->exists('provinces', 'id')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set){
                        $set('city_id', null);
                        $set('district_id', null);
                        $set('sub_district_id', null);
                    }),

                Forms\Components\Select::make('city_id')
                    ->label('Kota/Kabupaten')
                    ->options(function (Forms\Get $get){
                        if(!empty($get('province_id'))){
                            return City::whereProvinceId($get('province_id'))->pluck('name', 'id');
                        }

                        return [];
                    })
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->uuid()
                    ->exists('cities', 'id')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set){
                        $set('district_id', null);
                        $set('sub_district_id', null);
                    }),

                Forms\Components\Select::make('district_id')
                    ->label('Kecamatan')
                    ->options(function (Forms\Get $get){
                        if(!empty($get('city_id'))){
                            return District::whereCityId($get('city_id'))->pluck('name', 'id');
                        }

                        return [];
                    })
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->uuid()
                    ->exists('districts', 'id')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set){
                        $set('sub_district_id', null);
                    }),

                Forms\Components\Select::make('sub_district_id')
                    ->label('Kelurahan / Kode Pos')
                    ->options(function (Forms\Get $get){
                        if(!empty($get('district_id'))){
                            return SubDistrict::whereDistrictId($get('district_id'))->get()->pluck('formatted_label', 'id');
                        }

                        return [];
                    })
                    ->native(false)
                    ->searchable()
                    ->required()
                    ->uuid()
                    ->exists('sub_districts', 'id')
                    ->live(),
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable(),
                Tables\Columns\TextColumn::make('label'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Nomor Ponsel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->description(fn($record) => $record->formatted_area),
            ])
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
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'view' => Pages\ViewAddress::route('/{record}'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
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
