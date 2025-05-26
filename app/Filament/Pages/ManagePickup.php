<?php

namespace App\Filament\Pages;

use App\Settings\PickupInfoSetting;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManagePickup extends SettingsPage
{
    protected static string $settings = PickupInfoSetting::class;

    protected static ?string $title = 'Informasi Pickup';
    protected static ?string $navigationGroup = 'Pengaturan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->string()
                            ->minLength(10)
                            ->hintIcon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('operational_hours')
                            ->label('Jam Operasional')
                            ->prefixIcon('heroicon-o-clock')
                            ->required()
                            ->string(),
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Nomor Whatsapp')
                            ->required()
                            ->string()
                            ->prefixIcon('heroicon-o-device-phone-mobile')
                            ->helperText('Agar dapat berfungsi, nomor harus diawali dengan 62'),
                        TableRepeater::make('open_hour')
                            ->label('Jadwal Gudang')
                            ->orderColumn('')
                            ->headers([
                                Header::make('Hari')->width('150px'),
                                Header::make('Jam Buka')->width('150px'),
                                Header::make('Jam Tutup')->width('150px'),
                                Header::make('Buka')->width('150px'),
                            ])
                            ->schema([
                                Forms\Components\Hidden::make('day'),
                                Forms\Components\TextInput::make('name')
                                    ->label('Hari')
                                    ->required()
                                    ->readOnly(),
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Jam Buka')
                                    ->disabled(fn (Forms\Get $get) => !$get('is_open'))
                                    ->native(false)
                                    ->seconds(false),
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Jam Tutup')
                                    ->disabled(fn (Forms\Get $get) => !$get('is_open'))
                                    ->native(false)
                                    ->seconds(false),
                                Forms\Components\Toggle::make('is_open')
                                    ->label('Buka')
                                    ->default(true)
                                    ->live(),
                            ])
                            ->maxItems(7)
                            ->minItems(7)
                    ])
            ]);
    }
}
