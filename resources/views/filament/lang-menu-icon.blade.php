<div>
    <x-filament::dropdown maxHeight="250px" placement="left-start" teleport="true">
        <x-slot name="trigger">
            <div class="p-2- flex items-center justify-start gap-2">
                <x-filament::icon icon="heroicon-c-language" class="mx-1- h-5 w-5 text-black dark:text-gray-400" />
            </div>
        </x-slot>

        <x-filament::dropdown.header class="font-semibold" color="gray" icon="heroicon-c-language">
            {{__('backend.select_language')}}
        </x-filament::dropdown.header>

        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item :color="(app()->getLocale() === 'en') ? 'primary' : null" icon="{{(app()->getLocale() === 'en') ? 'heroicon-m-check' : null}}" :href="url('lang/en')"
                tag="a">
                English
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item :color="(app()->getLocale() === 'ar') ? 'primary' : null" icon="{{(app()->getLocale() === 'ar') ? 'heroicon-m-check' : null}}" :href="url('lang/ar')"
                tag="a">
                عربي
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
