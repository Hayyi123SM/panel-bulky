<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductStatus;
use Illuminate\Console\Command;

class GenerateTranslationContentCommand extends Command
{
    protected $signature = 'app:generate-translation-content';

    protected $description = 'Command description';

    public function handle(): void
    {
        $this->generateCategory();
        $this->generateCondition();
        $this->generateStatus();
        $this->generatePage();
        $this->generateProduct();
    }

    private function generateCategory(): void
    {
        ProductCategory::whereNull('name_trans')->get()->each(function ($category) {
            $category->update(['name_trans' => [
                'en' => $category->name,
                'id' => $category->name,
            ]]);
        });
    }

    public function generateCondition(): void
    {
        ProductCondition::whereNull('title_trans')->get()->each(function ($condition) {
            $condition->update(['title_trans' => [
                'id' => $condition->title,
                'en' => $condition->title,
            ]]);
        });
    }

    public function generateStatus(): void
    {
        ProductStatus::whereNull('status_trans')->get()->each(function ($status) {
            $status->update(['status_trans' => [
                'id' => $status->status,
                'en' => $status->status,
            ]]);
        });
    }

    public function generatePage(): void
    {
        Page::whereNull('title_trans')->orWhereNull('content_trans')->get()->each(function ($page) {
            $page->update([
                'title_trans' => [
                    'id' => $page->title,
                    'en' => $page->title,
                ],
                'content_trans' => [
                    'id' => $page->content,
                    'en' => $page->content,
                ]
            ]);
        });
    }

    public function generateProduct(): void
    {
        $products =Product::whereNameTrans(null)->get();
        dd($products);

        Product::whereNull('name_trans')->get()->each(function ($product) {
            $product->update([
                'name_trans' => [
                    'id' => $product->name,
                    'en' => $product->name,
                ],
                'description_trans' => [
                    'id' => $product->description,
                    'en' => $product->description,
                ]
            ]);
        });
    }
}
