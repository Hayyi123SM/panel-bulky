<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('floating_whatsapp.phone_number', '');
        $this->migrator->add('floating_whatsapp.message', '');
    }
};
