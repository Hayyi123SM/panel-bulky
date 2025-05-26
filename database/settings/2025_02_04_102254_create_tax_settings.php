<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('tax.rate', 11);
        $this->migrator->add('tax.enabled', false);
    }
};
