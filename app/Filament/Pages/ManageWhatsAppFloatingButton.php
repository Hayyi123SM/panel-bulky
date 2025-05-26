<?php

namespace App\Filament\Pages;

use App\Settings\WhatsAppFloatingSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageWhatsAppFloatingButton extends SettingsPage
{
    protected static string $settings = WhatsAppFloatingSettings::class;

    protected static ?string $title = 'WhatsApp Floating Button';
    protected static ?string $navigationGroup = 'Pengaturan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Nomor WhatsApp')
                            ->required()
                            ->string()
                            ->placeholder('Masukan nomor WhatsApp Anda')
                            ->prefixIcon('heroicon-o-device-phone-mobile')
                            ->helperText('Agar dapat berfungsi, nomor harus diawali dengan 62'),
                        Forms\Components\Textarea::make('message')
                            ->label('Pesan Awal')
                            ->required()
                            ->string()
                            ->placeholder('Masukan pesan awal yang akan ditampilkan'),
                    ])
            ]);
    }
}
