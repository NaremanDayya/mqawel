<div x-data="{ open: false }" class="fixed bottom-6 z-50 rtl:left-6 ltr:right-6">
    {{-- Chat panel --}}
    <div
        x-show="open"
        x-transition
        x-cloak
        x-on:click.outside="open = false"
        class="absolute bottom-20 flex h-[28rem] w-80 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl rtl:left-0 ltr:right-0 dark:border-white/10 dark:bg-gray-900 sm:w-96"
    >
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5 text-primary-600" />
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ __('backend.ai_assistant') }}</span>
            </div>
            <div class="flex items-center gap-1">
                @if (count($messages))
                    <button
                        type="button"
                        wire:click="clearConversation"
                        class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                        title="{{ __('backend.ai_assistant_clear') }}"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                    </button>
                @endif
                <button type="button" x-on:click="open = false" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div class="flex flex-1 flex-col gap-2 overflow-y-auto p-3" x-ref="scroller" x-init="$watch('open', value => value && $nextTick(() => $refs.scroller.scrollTop = $refs.scroller.scrollHeight))">
            @forelse ($messages as $message)
                <div @class([
                    'max-w-[85%] rounded-xl px-3 py-2 text-sm',
                    'whitespace-pre-line' => $message['role'] === 'user',
                    'prose prose-sm max-w-none dark:prose-invert prose-p:my-1 prose-ul:my-1 prose-ol:my-1' => $message['role'] === 'assistant',
                    'self-end bg-primary-600 text-white' => $message['role'] === 'user',
                    'self-start bg-gray-100 text-gray-800 dark:bg-white/5 dark:text-gray-100' => $message['role'] === 'assistant',
                ])>
                    @if ($message['role'] === 'assistant')
                        {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                    @else
                        {{ $message['content'] }}
                    @endif
                </div>
            @empty
                <p class="m-auto max-w-[85%] text-center text-sm text-gray-400">{{ __('backend.ai_assistant_empty_state') }}</p>
            @endforelse

            <div wire:loading wire:target="sendMessage" class="self-start rounded-xl bg-gray-100 px-3 py-2 text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">
                {{ __('backend.ai_assistant_thinking') }}
            </div>
        </div>

        <form wire:submit="sendMessage" class="flex items-end gap-2 border-t border-gray-200 p-2 dark:border-white/10">
            <textarea
                wire:model="input"
                rows="1"
                placeholder="{{ __('backend.ai_assistant_placeholder') }}"
                class="fi-input block w-full resize-none rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
                x-on:keydown.enter.prevent="if (!$event.shiftKey) $wire.sendMessage()"
            ></textarea>

            <x-filament::icon-button icon="heroicon-o-paper-airplane" type="submit" wire:loading.attr="disabled" wire:target="sendMessage" :label="__('backend.ai_assistant_send')" />
        </form>
    </div>

    {{-- Floating launcher --}}
    <button
        type="button"
        x-on:click="open = !open"
        class="flex h-14 w-14 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg transition hover:bg-primary-500"
    >
        <img x-show="!open" src="{{ asset('images/chatbot-icon.svg') }}" alt="" class="h-7 w-7" />
        <x-filament::icon x-show="open" x-cloak icon="heroicon-o-x-mark" class="h-6 w-6" />
    </button>
</div>
