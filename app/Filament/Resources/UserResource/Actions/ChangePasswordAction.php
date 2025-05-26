<?php

namespace App\Filament\Resources\UserResource\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Support\Colors\Color;
use Illuminate\Validation\Rules\Password;
use Rawilk\FilamentPasswordInput\Password as PasswordInput;

class ChangePasswordAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'change-password';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Change Password');
        $this->color(Color::Orange);
        $this->icon('heroicon-o-key');
        $this->successNotificationTitle('User password successfully changed');

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

        $this->action(function (User $record, array $data){
            $record->password = $data['password'];
            $record->update();
            $this->success();
        });
    }
}
