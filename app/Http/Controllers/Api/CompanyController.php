<?php

namespace App\Http\Controllers\Api;

use App\Filament\Concerns\AppliesCompanyLetterhead;
use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CompanyResource;
use App\Models\CompanyActivityLog;
use App\Services\CompanyProfileInsights;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class CompanyController extends Controller
{
    use AppliesCompanyLetterhead;
    use HasSectionNotificationSettings;

    private const NOTIFICATION_SECTIONS = ['workers', 'users', 'projects', 'documents', 'expired_files', 'contractors', 'items'];

    private function defaultDocumentCategories(): array
    {
        return [
            ['key' => 'contracts', 'label' => __('backend.documents_category_contracts'), 'icon' => 'heroicon-o-briefcase'],
            ['key' => 'quotes', 'label' => __('backend.documents_category_quotes'), 'icon' => 'heroicon-o-currency-dollar'],
            ['key' => 'letters', 'label' => __('backend.documents_category_letters'), 'icon' => 'heroicon-o-envelope'],
            ['key' => 'correspondence', 'label' => __('backend.documents_category_correspondence'), 'icon' => 'heroicon-o-chat-bubble-left-right'],
        ];
    }

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

    public function dashboardWidgets(Request $request)
    {
        return response()->json(['data' => $request->user()->company->dashboard_widgets ?? []]);
    }

    public function updateDashboardWidgets(Request $request)
    {
        $data = $request->validate([
            'widgets' => ['required', 'array'],
        ]);

        $company = $request->user()->company;
        $company->update(['dashboard_widgets' => $data['widgets']]);

        return response()->json(['data' => $company->dashboard_widgets]);
    }

    public function documentCategories(Request $request)
    {
        $stored = $request->user()->company->document_categories;

        return response()->json(['data' => filled($stored) ? $stored : $this->defaultDocumentCategories()]);
    }

    public function updateDocumentCategories(Request $request)
    {
        $data = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.key' => ['required', 'string', 'max:100'],
            'categories.*.label' => ['required', 'string', 'max:255'],
            'categories.*.icon' => ['nullable', 'string', 'max:100'],
        ]);

        $company = $request->user()->company;
        $company->update(['document_categories' => $data['categories']]);

        return response()->json(['data' => $company->document_categories]);
    }

    public function activityLog(Request $request)
    {
        $logs = CompanyActivityLog::where('company_id', $request->user()->company_id)
            ->latest('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($logs->toArray());
    }

    public function exportPdf(Request $request, CompanyProfileInsights $insights)
    {
        $company = $request->user()->company;

        $html = Blade::render('exports.company_profile', [
            'company' => $company,
            'completion' => $insights->completionBreakdown($company),
            'overall' => $insights->overallCompletion($company),
            'featuredProjects' => $insights->featuredProjects($company),
            'staffCounts' => $insights->staffCounts($company),
        ]);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/mpdf/tmp'),
        ]);

        if (session('current_lang') === 'ar') {
            $mpdf->SetDirectionality('rtl');
        }

        static::applyLetterhead($mpdf, $company->letterhead);

        $mpdf->WriteHTML($html);

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', Destination::STRING_RETURN);
        }, __('backend.company_profile').'-'.date('Y-m-d H-i').'.pdf', ['Content-Type' => 'application/pdf']);
    }

    private function validateSection(string $section): void
    {
        abort_unless(in_array($section, self::NOTIFICATION_SECTIONS, true), 404, __('backend.not_found'));
    }
}
