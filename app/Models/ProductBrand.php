<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Observers\ProductBrandObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy(ProductBrandObserver::class)]
class ProductBrand extends Model
{
    use SoftDeletes, HasUuids, HasSlug, LogsActivity;

    protected $fillable = [
        'wms_id',
        'name',
        'slug',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_brand_pivot', 'product_brand_id', 'product_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Merek')
            ->logOnly(['wms_id', 'name', 'slug'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Merek has been {$eventName}");
    }
}
