<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\SubDistrict;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->string(),
                    Forms\Components\TextInput::make('username')
                        ->required()
                        ->string()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('phone_number')
                        ->label('No. Telepon')
                        ->required()
                        ->string(),
                    Forms\Components\TextInput::make('email')
                        ->required()
                        ->email()
                        ->unique(ignoreRecord: true)
                ]),
//            Forms\Components\Section::make('Address Information')
//                ->schema([
//                    Forms\Components\TextInput::make('address')
//                        ->label('Alamat')
//                        ->required()
//                        ->string(),
//                    Forms\Components\Select::make('province_id')
//                        ->label('Provinsi')
//                        ->options(Province::orderBy('name')->get()->pluck('name', 'id'))
//                        ->required()
//                        ->native(false)
//                        ->dehydrated(false)
//                        ->afterStateUpdated(function (Forms\Set $set){
//                            $set('city_id', null);
//                            $set('district_id', null);
//                            $set('sub_district_id', null);
//                        })
//                        ->live(),
//                    Forms\Components\Select::make('city_id')
//                        ->label('Kota/Kabupaten')
//                        ->options(function (Forms\Get $get){
//                            if(!empty($get('province_id'))){
//                                return City::whereProvinceId($get('province_id'))
//                                    ->orderBy('name')
//                                    ->pluck('name', 'id');
//                            }
//
//                            return [];
//                        })
//                        ->required()
//                        ->native(false)
//                        ->dehydrated(false)
//                        ->afterStateUpdated(function (Forms\Set $set){
//                            $set('district_id', null);
//                            $set('sub_district_id', null);
//                        })
//                        ->live(),
//                    Forms\Components\Select::make('district_id')
//                        ->label('Kecamatan')
//                        ->options(function (Forms\Get $get){
//                            if(!empty($get('city_id'))){
//                                return District::whereCityId($get('city_id'))
//                                    ->orderBy('name')
//                                    ->get()
//                                    ->pluck('name', 'id');
//                            }
//
//                            return [];
//                        })
//                        ->required()
//                        ->native(false)
//                        ->dehydrated(false)
//                        ->afterStateUpdated(function (Forms\Set $set){
//                            $set('sub_district_id', null);
//                        })
//                        ->live(),
//                    Forms\Components\Select::make('sub_district_id')
//                        ->label('Kelurahan / Kode Pos')
//                        ->options(function (Forms\Get $get){
//                            if(!empty($get('district_id'))){
//                                return SubDistrict::whereDistrictId($get('district_id'))
//                                    ->orderBy('name')
//                                    ->get()
//                                    ->mapWithKeys(function ($subDistrict) {
//                                        return [$subDistrict['id'] => $subDistrict['name'] . ' - ' . $subDistrict['postal_code']];
//                                    });
//                            }
//
//                            return [];
//                        })
//                        ->required()
//                        ->native(false)
//                ])
        ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('No. Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_district.postal_code')
                    ->label('Kode Pos')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
//            RelationManagers\OrdersRelationManager::make()
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_admin', false)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
