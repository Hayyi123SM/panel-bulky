<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubDistrictResource\Pages;
use App\Filament\Resources\SubDistrictResource\RelationManagers;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubDistrictResource extends Resource
{
    protected static ?string $model = SubDistrict::class;

    protected static ?string $navigationGroup = 'Location Management';
    protected static ?int $navigationSort = 13;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('province_id')
                    ->label('Province')
                    ->relationship('district.city.province', 'name')
                    ->required()
                    ->preload()
                    ->live()
                    ->searchable()
                    ->afterStateUpdated(function(Forms\Set $set) {
                        $set('city_id', null);
                        $set('district_id', null);
                    }),
                Forms\Components\Select::make('city_id')
                    ->label('City')
                    ->relationship('district.city', 'name')
                    ->required()
                    ->options(function (Forms\Get $get){
                        if (!empty($get('province_id'))){
                            return City::where('province_id', $get('province_id'))->pluck('name', 'id');
                        }

                        return [];
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function(Forms\Set $set) {
                        $set('district_id', null);
                    }),
                Forms\Components\Select::make('district_id')
                    ->relationship('district', 'name')
                    ->required()
                    ->options(function (Forms\Get $get){
                        if (!empty($get('city_id'))){
                            return District::where('city_id', $get('city_id'))->pluck('name', 'id');
                        }

                        return [];
                    })
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->string()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('postal_code')
                    ->required()
                    ->maxLength(255)
                    ->length(5),
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('district.city.province.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district.city.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Sub District')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSubDistricts::route('/'),
            'create' => Pages\CreateSubDistrict::route('/create'),
            'edit' => Pages\EditSubDistrict::route('/{record}/edit'),
        ];
    }
}
