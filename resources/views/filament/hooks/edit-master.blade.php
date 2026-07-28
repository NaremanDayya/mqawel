<x-filament::dropdown.list>
    <x-filament::dropdown.list.item
        icon="heroicon-o-user"
        :href="url('master/masters/'.Auth::user()->id.'/edit')"
        tag="a"
    >
        {{ __('backend.profile') }}
    </x-filament::dropdown.list.item>
</x-filament::dropdown.list>
