<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use SoftDeletes, HasUuids, HasTranslations;

    public array $translatable = ['title_trans', 'content_trans'];

    protected $fillable = [
        'title',
        'title_trans',
        'slug',
        'content',
        'content_trans',
    ];
}
