<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
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
                    ->visible(fn (Get $get) => $get('enabled')),

                Toggle::make('notify_on_create')
                    ->label(__('backend.notify_on_create'))
                    ->helperText(__('backend.notify_on_create_hint'))
                    ->visible(fn (Get $get) => $get('enabled')),

                Toggle::make('notify_on_update')
                    ->label(__('backend.notify_on_update'))
                    ->helperText(__('backend.notify_on_update_hint'))
                    ->visible(fn (Get $get) => $get('enabled')),

                Toggle::make('notify_on_delete')
                    ->label(__('backend.notify_on_delete'))
                    ->helperText(__('backend.notify_on_delete_hint'))
                    ->visible(fn (Get $get) => $get('enabled')),

                Toggle::make('whatsapp')
                    ->label(__('backend.whatsapp_alert'))
                    ->helperText(__('backend.whatsapp_alert_hint'))
                    ->visible(fn (Get $get) => $get('enabled')),

                Select::make('lead_time_days')
                    ->label(__('backend.lead_time'))
                    ->visible(fn (Get $get) => $get('enabled'))
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
     * @return array{enabled: bool, expiry_alert: bool, whatsapp: bool, lead_time_days: int, notify_on_create: bool, notify_on_update: bool, notify_on_delete: bool}
     */
    public static function sectionNotificationSettings(string $section): array
    {
        $company = Auth::user()->company;

        $defaults = [
            'enabled' => true,
            'expiry_alert' => true,
            'whatsapp' => false,
            'lead_time_days' => $company?->about_to_expire_days ?: 1,
            'notify_on_create' => true,
            'notify_on_update' => true,
            'notify_on_delete' => true,
        ];

        return array_merge($defaults, $company?->notification_settings[$section] ?? []);
    }

    /**
     * Sends a database notification to every user in the company, but only
     * if this section's settings have notifications (and this specific
     * create/update/delete event) turned on.
     */
    protected static function notifyIfEnabled(string $section, string $event, string $messageAr, string $messageEn): void
    {
        $settings = static::sectionNotificationSettings($section);

        if (! ($settings['enabled'] ?? true) || ! ($settings["notify_on_{$event}"] ?? true)) {
            return;
        }

        foreach (Auth::user()->company->users ?? [] as $user) {
            Notification::make()
                ->title($user->locale === 'en' ? $messageEn : $messageAr)
                ->sendToDatabase($user);
        }
    }
}
