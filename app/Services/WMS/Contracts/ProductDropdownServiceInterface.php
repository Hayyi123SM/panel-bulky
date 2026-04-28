<?php

namespace App\Services\WMS\Contracts;

interface ProductDropdownServiceInterface
{
    /**
     * Get dropdown options for product template selection.
     * @return array [id => label]
     */
    public function getDropdownOptions(): array;

    /**
     * Get detail data for a given template id.
     * @param int|string $id
     * @return array|null
     */
    public function getDropdownDetail($id): ?array;
}
