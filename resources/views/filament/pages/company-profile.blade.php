<x-filament-panels::page>
    @php
        $company = $this->company();
        $completion = $this->getCompletionBreakdown();
        $overall = $this->getOverallCompletion();
        $weakest = $this->getWeakestCategory();
        $featuredProjects = $this->getFeaturedProjects();
        $staffCounts = $this->getStaffCounts();
    @endphp

    {{-- Tabs --}}
    <div class="flex items-center gap-6 border-b border-gray-200 dark:border-white/10">
        @foreach ([
            'main' => ['label' => __('backend.company_data_tab'), 'count' => null],
            'files' => ['label' => __('backend.created_files'), 'count' => count($this->getCompanyFiles())],
            'log' => ['label' => __('backend.edit_history'), 'count' => null],
        ] as $key => $tab)
            <button
                type="button"
                wire:click="setActiveTab('{{ $key }}')"
                @class([
                    'relative flex items-center gap-2 pb-3 pt-1 text-sm font-medium transition',
                    'text-primary-600' => $activeTab === $key,
                    'text-gray-500 hover:text-gray-700 dark:text-gray-400' => $activeTab !== $key,
                ])
            >
                @if (filled($tab['count']))
                    <span @class([
                        'inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-xs font-semibold',
                        'bg-primary-600 text-white' => $activeTab === $key,
                        'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' => $activeTab !== $key,
                    ])>{{ $tab['count'] }}</span>
                @endif
                {{ $tab['label'] }}
                @if ($activeTab === $key)
                    <span class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-primary-600"></span>
                @endif
            </button>
        @endforeach
    </div>

    @if ($activeTab === 'main')
        <div class="grid gap-4">
            {{-- Company header --}}
            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                @if ($company->picture)
                    <img src="{{ asset('storage/'.$company->picture) }}" alt="{{ $company->name }}" class="h-14 w-14 rounded-2xl object-cover" />
                @else
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-900 text-lg font-bold text-white dark:bg-white/10">
                        {{ mb_substr($company->name, 0, 1) }}
                    </span>
                @endif
                <div class="min-w-0 flex-1">
                    <b class="block text-base">{{ $company->name }}</b>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $company->activity }}
                        @if ($company->address) — {{ $company->address }} @endif
                        @if ($company->founded_year) — {{ __('backend.since') }} {{ $company->founded_year }} @endif
                    </span>
                </div>
                @if ($company->is_verified)
                    <x-filament::badge color="success">{{ __('backend.verified') }}</x-filament::badge>
                @endif
            </div>

            {{-- Completion breakdown --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold">{{ __('backend.completion_title') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('backend.completion_subtitle') }}</p>
                    </div>
                    <x-filament::badge color="info">{{ $overall }}%</x-filament::badge>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($completion as $item)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <b>{{ $item['label'] }}</b>
                                <span>{{ $item['percentage'] }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-full rounded-full" style="width: {{ $item['percentage'] }}%; background: {{ $item['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- AI suggestion banner --}}
            @if ($weakest && $weakest['percentage'] < 100)
                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-primary-100 bg-primary-50/60 p-4 dark:border-primary-400/20 dark:bg-primary-400/5">
                    <x-filament::icon icon="heroicon-o-sparkles" class="h-8 w-8 shrink-0 text-primary-600" />
                    <div class="min-w-0 flex-1">
                        <b class="block text-sm">{{ __('backend.ai_suggestion_title') }}</b>
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('backend.ai_suggestion_body', ['section' => $weakest['label'], 'percentage' => $weakest['percentage']]) }}
                        </span>
                    </div>
                </div>
            @endif

            {{-- Main info --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold">{{ __('backend.main_company_info') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('backend.main_company_info_subtitle') }}</p>
                    </div>
                    {{ $this->editMainInfoAction }}
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['heroicon-o-building-office-2', __('backend.name'), $company->name],
                        ['heroicon-o-hashtag', __('backend.business_number'), $company->business_number],
                        ['heroicon-o-calendar', __('backend.founded_year'), $company->founded_year],
                        ['heroicon-o-map-pin', __('backend.address'), $company->address],
                        ['heroicon-o-phone', __('backend.phone'), $company->phone],
                        ['heroicon-o-envelope', __('backend.email'), $company->email],
                        ['heroicon-o-link', __('backend.website'), $company->website],
                    ] as [$icon, $label, $value])
                        <div class="flex items-center gap-3 rounded-lg border border-gray-100 p-3 dark:border-white/5">
                            <x-filament::icon :icon="$icon" class="h-5 w-5 shrink-0 text-gray-400" />
                            <div class="min-w-0">
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                <span class="block truncate text-sm font-medium">{{ $value ?: __('backend.not_added_yet') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Services --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold">{{ __('backend.services_offered') }}</h2>
                    {{ $this->editServicesAction }}
                </div>
                <div class="flex flex-wrap gap-2">
                    @forelse ($company->services ?? [] as $service)
                        <x-filament::badge color="info">{{ $service }}</x-filament::badge>
                    @empty
                        <span class="text-sm text-gray-400">{{ __('backend.not_added_yet') }}</span>
                    @endforelse
                </div>
            </div>

            {{-- Featured projects --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                <h2 class="mb-3 text-sm font-semibold">{{ __('backend.featured_projects') }}</h2>
                <div class="grid gap-2">
                    @forelse ($featuredProjects as $project)
                        <a href="{{ \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $project]) }}" class="flex items-center gap-3 rounded-lg border border-gray-100 p-3 hover:border-gray-300 dark:border-white/5">
                            <x-filament::icon icon="heroicon-o-map-pin" class="h-5 w-5 shrink-0 text-primary-600" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-sm font-medium">{{ $project->name }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ number_format($project->budget) }} {{ __('backend.omani_riyal_code') }}</span>
                            </div>
                        </a>
                    @empty
                        <span class="text-sm text-gray-400">{{ __('backend.no_completed_projects_yet') }}</span>
                    @endforelse
                </div>
            </div>

            {{-- Staff counts --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                <h2 class="mb-3 text-sm font-semibold">{{ __('backend.staff_count') }}</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-gray-50 p-4 text-center dark:bg-white/5">
                        <b class="block text-2xl text-primary-600">{{ $staffCounts['workers'] }}</b>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('backend.workers') }}</span>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 text-center dark:bg-white/5">
                        <b class="block text-2xl text-info-600">{{ $staffCounts['users'] }}</b>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('backend.users') }}</span>
                    </div>
                </div>
            </div>

            {{-- About --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold">{{ __('backend.about_company') }}</h2>
                    {{ $this->editAboutAction }}
                </div>
                <p class="text-sm leading-7 text-gray-600 dark:text-gray-400">
                    {{ $company->description ?: __('backend.not_added_yet') }}
                </p>
            </div>
        </div>
    @elseif ($activeTab === 'files')
        <div class="mq-company-file-grid">
            @forelse ($this->getCompanyFiles() as $file)
                @php
                    $categoryLabel = match ($file->category) {
                        'certificate' => __('backend.file_category_certificate'),
                        'work_photo' => __('backend.file_category_work_photo'),
                        default => __('backend.file_category_general'),
                    };

                    $tone = match ($file->category) {
                        'certificate' => ['bg-amber-50 dark:bg-amber-500/10', 'text-amber-600 dark:text-amber-400'],
                        'work_photo' => ['bg-violet-50 dark:bg-violet-500/10', 'text-violet-600 dark:text-violet-400'],
                        default => ['bg-blue-50 dark:bg-blue-500/10', 'text-blue-600 dark:text-blue-400'],
                    };

                    $fileUrl = $file->file ? \Illuminate\Support\Facades\Storage::disk('public')->url($file->file) : null;
                    $completion = $this->getFileCompletion($file);

                    $statusLabel = \App\Filament\Resources\GeneratedDocumentResource::statusOptions()[$file->status] ?? $file->status;
                    $statusTone = match (\App\Filament\Resources\GeneratedDocumentResource::statusColor($file->status)) {
                        'warning' => ['bg-amber-50 dark:bg-amber-500/10', 'text-amber-700 dark:text-amber-400', 'bg-amber-500'],
                        'info' => ['bg-blue-50 dark:bg-blue-500/10', 'text-blue-700 dark:text-blue-400', 'bg-blue-500'],
                        'success' => ['bg-emerald-50 dark:bg-emerald-500/10', 'text-emerald-700 dark:text-emerald-400', 'bg-emerald-500'],
                        default => ['bg-gray-100 dark:bg-white/5', 'text-gray-600 dark:text-gray-400', 'bg-gray-400'],
                    };
                @endphp
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
                    <div class="mb-3 flex items-center gap-3">
                        <x-filament::icon icon="heroicon-o-document-text" class="h-11 w-11 shrink-0 rounded-xl p-2.5 {{ $tone[0] }} {{ $tone[1] }}" />
                        <div class="min-w-0">
                            <span class="block truncate text-sm font-semibold">{{ $file->name }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $categoryLabel }} &middot; {{ $file->created_at?->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="mb-2 flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('backend.status') }}</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 font-semibold {{ $statusTone[0] }} {{ $statusTone[1] }}">
                            <i class="h-1.5 w-1.5 rounded-full {{ $statusTone[2] }}"></i>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="mb-3 flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('backend.completion_percentage') }}</span>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $completion }}%</span>
                    </div>

                    @if ($fileUrl)
                        <div class="flex gap-2">
                            <a href="{{ $fileUrl }}" download="{{ $file->downloadFilename() }}" class="fi-btn fi-btn-size-sm fi-btn-color-gray flex-1 justify-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-4 w-4" />
                                PDF
                            </a>
                            <a href="{{ $fileUrl }}" target="_blank" class="fi-btn fi-btn-size-sm fi-btn-color-gray flex-1 justify-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-eye" class="h-4 w-4" />
                                {{ __('backend.preview') }}
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <span class="text-sm text-gray-400">{{ __('backend.no_documents_yet') }}</span>
            @endforelse
        </div>
    @elseif ($activeTab === 'log')
        <div class="rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">
            @forelse ($this->getActivityLogs() as $log)
                <div class="flex items-center gap-3 border-b border-gray-100 p-4 last:border-0 dark:border-white/5">
                    <x-filament::icon :icon="$log->icon" :color="$log->tone" class="h-5 w-5 shrink-0" />
                    <span class="flex-1 text-sm">{!! $log->message !!}</span>
                    <span class="text-xs text-gray-400">{{ $log->created_at?->diffForHumans() }}</span>
                </div>
            @empty
                <p class="p-4 text-sm text-gray-400">{{ __('backend.no_activity_yet') }}</p>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
