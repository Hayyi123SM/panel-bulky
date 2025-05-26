<?php

namespace App\Models;

use App\Models\Traits\HasUsername;
use App\Observers\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes, HasUsername, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sub_district_id',
        'address',
        'name',
        'email',
        'username',
        'profile_picture',
        'password',
        'phone_number',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean'
        ];
    }

    protected $appends = [
        'province_id', 'city_id', 'district_id'
    ];

    public function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn() => "$this->address, {$this->sub_district?->name}, {$this->sub_district?->district->name},
            {$this->sub_district?->district?->city?->name}, {$this->sub_district?->district?->city?->province?->name},
            {$this->sub_district?->postal_code}",
        );
    }

    public function provinceId(): Attribute
    {
        return Attribute::get(fn() => $this->sub_district?->district->city->province->id);
    }

    public function cityId(): Attribute
    {
        return Attribute::get(fn() => $this->sub_district?->district->city->id);
    }

    public function districtId(): Attribute
    {
        return Attribute::get(fn() => $this->sub_district?->district->id);
    }

    public function sub_district(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(Otp::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function watchLater(): HasMany
    {
        return $this->hasMany(WatchLater::class);
    }

    public function videoLikes(): HasMany
    {
        return $this->hasMany(VideoLike::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'user_id');
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user');
    }

    public function banks(): HasMany
    {
        return $this->hasMany(UserBank::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
