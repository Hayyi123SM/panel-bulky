<?php

namespace App\Filament\Resources\ReviewResource\Actions;

use App\Models\Review;
use Filament\Actions\Action;

class ApproveAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'approve_action';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->label('Setujui Ulasan');
        $this->color('success');
        $this->icon('heroicon-o-check-circle');

        $this->visible(function (Review $record) {
            return !$record->approved;
        });

        $this->requiresConfirmation();

        $this->action(function (Review $record){
            $record->update([
                'approved' => true,
            ]);
            $this->successNotificationTitle('Review berhasil disetujui');
            $this->success();
        });
    }
}
