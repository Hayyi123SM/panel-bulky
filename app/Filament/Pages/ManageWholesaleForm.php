<?php

namespace App\Filament\Pages;

use App\Settings\WholesaleFormSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageWholesaleForm extends SettingsPage
{
    protected static string $settings = WholesaleFormSetting::class;

    protected static ?string $title = 'Formulir Partai Besar';
    protected static ?string $navigationGroup = 'Pengaturan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TagsInput::make('emails')
                            ->label('Daftar Email')
                            ->placeholder('Email baru')
                            ->helperText('Masukkan email yang akan menerima pesan saat formulir dikirim.')
                            ->required()
                            ->nestedRecursiveRules([
                                'email',
                            ])
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('budgets')
                            ->label('Anggaran')
                            ->placeholder('Anggaran baru')
                            ->helperText('Daftar anggaran yang akan ditampilkan di formulir. Gunakan format rupiah (contoh: Rp 100.000.000).')
                            ->required()
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
            ]);
    }
}
