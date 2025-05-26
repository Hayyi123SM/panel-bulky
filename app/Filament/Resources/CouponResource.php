<?php

namespace App\Filament\Resources;

use App\Enums\CouponDiscountTypeEnum;
use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationGroup = 'Marketing & Promosi';
    protected static ?string $label = 'Kupon/Diskon';
    protected static ?int $navigationSort = 14;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->readOnlyOn(['edit'])
                    ->suffixAction(
                        action: Forms\Components\Actions\Action::make('generate_random')
                            ->label('Generate Random code')
                            ->icon('heroicon-o-arrow-path')
                            ->disabled(function ($record) {
                                return !is_null($record?->id);
                            })
                            ->action(function (Forms\Set $set) {
                                $set('code', function () use ($set) {
                                    do {
                                        $randomString = Str::upper(Str::random(10));
                                    } while (Coupon::where('code', $randomString)->exists());

                                    return $randomString;
                                });
                            }),
                        isInline: true
                    ),
                Forms\Components\Select::make('discount_type')
                    ->label('Jenis Diskon')
                    ->options(CouponDiscountTypeEnum::class)
                    ->selectablePlaceholder(false)
                    ->default(CouponDiscountTypeEnum::Percent)
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('discount_value')
                    ->label('Nilai Diskon')
                    ->required()
                    ->autocomplete(false)
                    ->numeric(fn(Forms\Get $get) => $get('discount_type') == CouponDiscountTypeEnum::Percent)
                    ->suffix(function (Forms\Get $get, $record){
                        if(is_null($record)){
                            if($get('discount_type') === CouponDiscountTypeEnum::Percent){
                                return '%';
                            }
                        }

                        if(!is_null($record)){
                            if($get('discount_type') === CouponDiscountTypeEnum::Percent->value){
                                return '%';
                            }
                        }

                        return null;
                    }, true)
                    ->prefix(function (Forms\Get $get, $record){
                        if (is_null($record)){
                            if($get('discount_type') === CouponDiscountTypeEnum::Amount){
                                return 'Rp ';
                            }
                        }

                        if (!is_null($record)){
                            if($get('discount_type') === CouponDiscountTypeEnum::Amount->value){
                                return 'Rp ';
                            }
                        }
                        return null;
                    }, true)
                    ->mask(function (Forms\Get $get, $record){
                        if(is_null($record)){
                            if($get('discount_type') === CouponDiscountTypeEnum::Amount){
                                return RawJs::make(<<<'JS'
                                    $money($input, ',', '.', 0)
                                JS);
                            }
                        }

                        if(!is_null($record)){
                            if($get('discount_type') === CouponDiscountTypeEnum::Amount->value){
                                return RawJs::make(<<<'JS'
                                    $money($input, ',', '.', 0)
                                JS);
                            }
                        }

                        return null;
                    })
                    ->dehydrateStateUsing(fn($state) => \str($state)->remove('.')->toInteger()),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Tanggal Kedaluarsa')
                    ->native(false),
                Forms\Components\TextInput::make('minimum_purchase')
                    ->label('Minimal Pembelian')
                    ->prefix('Rp ', true)
                    ->mask(RawJs::make(<<<'JS'
                        $money($input, ',', '.', 0)
                    JS))
                    ->dehydrateStateUsing(fn($state) => \str($state)->remove('.')->toInteger()),
                Forms\Components\TextInput::make('usage_limit')
                    ->label('Limit Pemakaian')
                    ->numeric(),
                Forms\Components\Select::make('categories')
                    ->label('Limit Kategori')
                    ->relationship('categories', 'name')
                    ->preload()
                    ->multiple()
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_value')
                    ->searchable()
                    ->numeric()
                    ->formatStateUsing(function (int $state, Coupon $record){
                        if($record->discount_type === CouponDiscountTypeEnum::Percent){
                            return $state.'%';
                        } else {
                            return 'Rp '. number_format($state, 0, ',', '.');
                        }
                    })
                    ->alignRight(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('minimum_purchase')
                    ->prefix('Rp ')
                    ->numeric(0, ',', '.')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make()
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
            RelationManagers\UsersRelationManager::make(),
            RelationManagers\ProductsRelationManager::make(),
            RelationManagers\UsagesRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
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
