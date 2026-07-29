<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * A reusable "إعدادات الإشعارات" header action, per data section (documents,
 * workers, projects, …), matching the mockup's notifBtn(section) pattern.
 * Settings are stored on the company as JSON, keyed by section.
 */
trait HasSectionNotificationSettings
{
    protected function notificationSettingsAction(string $section, string $sectionLabel): Action
    {
        return Action::make('notificationSettings')
            ->label(__('backend.notification_settings'))
            ->icon('heroicon-o-bell-alert')
            ->color('gray')
            ->modalHeading(__('backend.notification_settings_for', ['section' => $sectionLabel]))
            ->modalDescription(__('backend.notification_settings_description'))
            ->modalSubmitActionLabel(__('backend.save_settings'))
            ->fillForm(fn () => static::sectionNotificationSettings($section))
            ->form([
                Toggle::make('enabled')
                    ->label(__('backend.notifications_enabled'))
                    ->helperText(__('backend.notifications_enabled_hint'))
                    ->live(),

                Toggle::make('expiry_alert')
                    ->label(__('backend.expiry_alert'))
                    ->helperText(__('backend.expiry_alert_hint'))
                    ->visible(fn (\Filament\Forms\Get $get) => $get('enabled')),

                Toggle::make('whatsapp')
                    ->label(__('backend.whatsapp_alert'))
                    ->helperText(__('backend.whatsapp_alert_hint'))
                    ->visible(fn (\Filament\Forms\Get $get) => $get('enabled')),

                Select::make('lead_time_days')
                    ->label(__('backend.lead_time'))
                    ->visible(fn (\Filament\Forms\Get $get) => $get('enabled'))
                    ->options([
                        1 => __('backend.lead_time_1_day'),
                        3 => __('backend.lead_time_3_days'),
                        7 => __('backend.lead_time_7_days'),
                        14 => __('backend.lead_time_14_days'),
                        30 => __('backend.lead_time_30_days'),
                    ])
                    ->native(false),
            ])
            ->action(function (array $data) use ($section) {
                $company = Auth::user()->company;
                $settings = $company->notification_settings ?? [];
                $settings[$section] = $data;
                $company->update(['notification_settings' => $settings]);

                Notification::make()->title(__('backend.settings_saved'))->success()->send();
            });
    }

    /**
     * @return array{enabled: bool, expiry_alert: bool, whatsapp: bool, lead_time_days: int}
     */
    public static function sectionNotificationSettings(string $section): array
    {
        $company = Auth::user()->company;

        $defaults = [
            'enabled' => true,
            'expiry_alert' => true,
            'whatsapp' => false,
            'lead_time_days' => $company?->about_to_expire_days ?: 1,
        ];

        return array_merge($defaults, $company?->notification_settings[$section] ?? []);
    }
}
