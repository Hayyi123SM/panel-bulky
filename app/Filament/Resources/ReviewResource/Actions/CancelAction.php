<?php

namespace App\Filament\Resources\ReviewResource\Actions;

use App\Models\Review;
use Filament\Actions\Action;

class CancelAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'cancel_action';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->color('danger');
        $this->label('Batalkan');
        $this->requiresConfirmation();

        $this->visible(function (Review $record) {
            return $record->approved;
        });

        $this->action(function (Review $record){
            $record->approved = false;
            $record->save();

            $this->successNotificationTitle('Ulasan berhasil dibatalkan.');
            $this->success();
        });
    }
}
