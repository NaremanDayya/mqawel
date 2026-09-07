<?php

namespace App\Http\Controllers\Api;

use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    use HasSectionNotificationSettings;

    private const NOTIFICATION_SECTIONS = ['workers', 'users', 'projects', 'documents', 'expired_files', 'contractors', 'items'];

    public function show(Request $request)
    {
        return new CompanyResource($request->user()->company);
    }

    public function update(Request $request)
    {
        $company = $request->user()->company;

        $data = $request->validate([
            'logo' => ['nullable', 'image', 'max:10240'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'business_number' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'address' => ['nullable', 'string', 'max:255'],
            'activity' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['picture'] = $request->file('logo')->store('form-attachments', 'public');
        }
        unset($data['logo']);

        $company->update($data);

        return new CompanyResource($company->refresh());
    }

    public function notificationSettings(Request $request, string $section)
    {
        $this->validateSection($section);

        return response()->json(['data' => static::sectionNotificationSettings($section)]);
    }

    public function updateNotificationSettings(Request $request, string $section)
    {
        $this->validateSection($section);

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'expiry_alert' => ['nullable', 'boolean'],
            'notify_on_create' => ['nullable', 'boolean'],
            'notify_on_update' => ['nullable', 'boolean'],
            'notify_on_delete' => ['nullable', 'boolean'],
            'whatsapp' => ['nullable', 'boolean'],
            'lead_time_days' => ['nullable', 'integer', Rule::in([1, 3, 7, 14, 30])],
        ]);

        $company = $request->user()->company;
        $settings = $company->notification_settings ?? [];
        $settings[$section] = array_merge(static::sectionNotificationSettings($section), $data);
        $company->update(['notification_settings' => $settings]);

        return response()->json(['data' => $settings[$section]]);
    }

    private function validateSection(string $section): void
    {
        abort_unless(in_array($section, self::NOTIFICATION_SECTIONS, true), 404, __('backend.not_found'));
    }
}
