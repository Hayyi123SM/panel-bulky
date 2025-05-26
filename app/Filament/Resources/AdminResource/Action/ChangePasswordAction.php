<?php

namespace App\Filament\Resources\AdminResource\Action;

use App\Models\Admin;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Illuminate\Validation\Rules\Password;
use Rawilk\FilamentPasswordInput\Password as PasswordInput;

class ChangePasswordAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'change_password';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Change Password');
        $this->color(Color::Orange);
        $this->icon('heroicon-o-key');
        $this->successNotificationTitle('Admin password successfully changed');

        $this->form([
            PasswordInput::make('password')
                ->label('New Password')
                ->required()
                ->string()
                ->password()
                ->rules([
                    Password::defaults()
                ])
                ->inlineLabel(),
            PasswordInput::make('password_confirmation')
                ->label('New Password Confirmation')
                ->required()
                ->string()
                ->password()
                ->rules([
                    Password::defaults()
                ])
                ->inlineLabel(),
        ]);

        $this->action(function (Admin $record, array $data){
            $record->password = $data['password'];
            $record->update();
            $this->success();
        });
    }
}
