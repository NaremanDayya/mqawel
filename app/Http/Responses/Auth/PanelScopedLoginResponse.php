<?php

namespace App\Http\Responses\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class PanelScopedLoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $intended = session('url.intended');
        $panelPath = Filament::getCurrentPanel()?->getPath();

        // The "intended" URL is stored session-wide (not scoped per Filament
        // panel), so logging into one panel after being bounced from another
        // (e.g. visiting /company while logged out, then logging into
        // /master) would otherwise redirect into the wrong panel. Discard it
        // when it doesn't belong to the panel actually being logged into.
        if ($intended && $panelPath && !str_contains($intended, "/{$panelPath}")) {
            session()->forget('url.intended');
        }

        return redirect()->intended(Filament::getUrl());
    }
}
