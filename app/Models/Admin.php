<?php

namespace App\Models;

use App\Observers\AdminObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(AdminObserver::class)]
class Admin extends Authenticatable implements FilamentUser
{
    use SoftDeletes, HasUuids, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        // 'api_key',
        // 'is_dev',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        // 'api_key',
        // 'is_dev',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed'
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Menejement Admin')
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Menejement Admin has been {$eventName}");
    }

    public function createApiKey(): string
    {
        do {
            $key = bin2hex(random_bytes(24));
        } while (self::where('api_key', $key)->exists());

        return $key;
    }
}
