<?php

namespace App\Models;

use App\Enums\CouponDiscountTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Coupon extends Model
{
    use SoftDeletes, HasUuids, LogsActivity;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'expiry_date',
        'minimum_purchase',
        'usage_limit',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'discount_type' => CouponDiscountTypeEnum::class,
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_user');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'coupon_category', 'coupon_id', 'product_category_id');
    }


    /**
     * Generate a unique coupon code
     *
     * @param int $length The length of the code to generate
     * @return string
     */
    public static function generateCode(int $length = 12): string
    {
        do {
            $code = Str::upper(Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Kupon')
            ->logOnly([
                'code',
                'discount_type',
                'discount_value',
                'expiry_date',
                'minimum_purchase',
                'usage_limit',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Kupon has been {$eventName}");
    }
}
