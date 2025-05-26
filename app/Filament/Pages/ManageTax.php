<?php

namespace App\Filament\Pages;

use App\Settings\PpnSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageTax extends SettingsPage
{
    protected static ?string $navigationLabel = 'PPN';

    protected static string $settings = PpnSettings::class;

    protected static ?string $navigationGroup = 'Pengaturan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rate')
                    ->numeric()
                    ->maxValue(100)
                    ->minValue(0)
                    ->required()
                    ->suffix('%'),
                Forms\Components\Toggle::make('enabled')
            ])->columns(1)->inlineLabel();
    }
}
