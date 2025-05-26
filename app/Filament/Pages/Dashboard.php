<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Tab1\TotalSales;
use App\Filament\Widgets\Tab1\Transaction;
use App\Filament\Widgets\Tab1\TransactionByCategory;
use App\Filament\Widgets\Tab2\PallateAvailable;
use App\Filament\Widgets\Tab2\PallateStats;
use App\Filament\Widgets\Tab2\UserTransaction;
use App\Filament\Widgets\Tab2\UserTransactionTable;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public string $activeTab = 'tab1';

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    public function getWidgetsTab1(): array
    {
        return [
            Transaction::class,
            TotalSales::class,
            TransactionByCategory::class
        ];
    }

    public function getWidgetsTab2(): array
    {
        return [
            PallateAvailable::class,
            UserTransaction::class,
            PallateStats::class,
            \App\Filament\Widgets\Tab2\Transaction::class,
            UserTransactionTable::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getVisibleWidgetsTab1(): array
    {
        return $this->filterVisibleWidgets($this->getWidgetsTab1());
    }

    public function getVisibleWidgetsTab2(): array
    {
        return $this->filterVisibleWidgets($this->getWidgetsTab2());
    }

}
