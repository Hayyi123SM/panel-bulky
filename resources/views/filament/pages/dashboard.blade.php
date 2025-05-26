<x-filament-panels::page>
    <x-filament::tabs label="Dashboard tabs">
        <x-filament::tabs.item
            :active="$activeTab === 'tab1'"
            wire:click="$set('activeTab', 'tab1')"
        >
            Dashboard 1
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$activeTab === 'tab2'"
            wire:click="$set('activeTab', 'tab2')"
        >
            Dashboard 2
        </x-filament::tabs.item>
    </x-filament::tabs>

    @if($this->activeTab == 'tab1')
        <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :data="
            [
                ...(property_exists($this, 'filters') ? ['filters' => $this->filters] : []),
                ...$this->getWidgetsTab1(),
            ]
        "
            :widgets="$this->getVisibleWidgetsTab1()"
        />
    @endif

    @if($this->activeTab == 'tab2')
        <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :data="
            [
                ...(property_exists($this, 'filters') ? ['filters' => $this->filters] : []),
                ...$this->getWidgetsTab2(),
            ]
        "
            :widgets="$this->getVisibleWidgetsTab2()"
        />
    @endif
</x-filament-panels::page>
