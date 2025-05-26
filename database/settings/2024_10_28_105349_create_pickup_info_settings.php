<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('pickup_info.address', 'Jl. Cilodong Raya No.89, Cilodong, Kec. Cilodong, Kota Depok, Jawa Barat 40114');
        $this->migrator->add('pickup_info.operational_hours', 'Senin - Sabtu, 09.00 - 18.00 WIB');
        $this->migrator->add('pickup_info.whatsapp_number', '62811858834');
    }
};
