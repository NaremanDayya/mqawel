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
        // Resource pages (Users, Workers, Documents lists, …) expose the group
        // via their resource; plain pages (Company Profile, …) expose it on
        // themselves. Support both.
        $group = method_exists(static::class, 'getResource')
            ? static::getResource()::getNavigationGroup()
            : static::getNavigationGroup();

        $breadcrumbs = parent::getBreadcrumbs();

        if (empty($breadcrumbs)) {
            // Plain (non-resource) pages don't build their own breadcrumb
            // trail the way resource pages do — add the page's own title as
            // the final segment ourselves.
            $breadcrumbs = [static::getNavigationLabel()];
        }

        return [
            ...(filled($group) ? [$group] : []),
            ...$breadcrumbs,
        ];
    }
}
