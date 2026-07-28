<div>
    <x-filament::dropdown maxHeight="250px" placement="left-start" teleport="true">
        <x-slot name="trigger">
            <div class="p-2 flex items-center justify-start gap-2">
                <x-filament::icon icon="heroicon-o-language" class="mx-1 h-5 w-5 text-gray-500 dark:text-gray-400" />
                {{__('backend.select_language')}}
            </div>
        </x-slot>

        <x-filament::dropdown.header class="font-semibold" color="gray" icon="heroicon-c-language">
            {{__('backend.select_language')}}
        </x-filament::dropdown.header>

        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item :color="(app()->getLocale() === 'en') ? 'primary' : null" icon="heroicon-m-chevron-right" :href="url('lang/en')"
                tag="a">
                English
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item :color="(app()->getLocale() === 'ar') ? 'primary' : null" icon="heroicon-m-chevron-right" :href="url('lang/ar')"
                tag="a">
                عربي
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
