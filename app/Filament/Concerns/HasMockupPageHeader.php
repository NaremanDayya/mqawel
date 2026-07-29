<?php

namespace App\Filament\Concerns;

/**
 * Matches the design mockup's page header: a descriptive subtitle under the
 * title, and a 3-level breadcrumb that includes the navigation group
 * (e.g. "الشركة > المستخدمين > القائمة") instead of Filament's default 2-level one.
 */
trait HasMockupPageHeader
{
    protected function pageSubtitle(): ?string
    {
        return null;
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return $this->pageSubtitle();
    }

    public function getBreadcrumbs(): array
    {
        $group = static::getResource()::getNavigationGroup();

        return [
            ...(filled($group) ? [$group] : []),
            ...parent::getBreadcrumbs(),
        ];
    }
}
